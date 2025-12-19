<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk upload dokumen
 * Menangani upload, hapus, dan bulk delete dokumen
 * Terintegrasi dengan RAG API untuk preprocessing
 */
class AdminUploadController extends Controller
{
    /**
     * Menampilkan halaman upload dengan pencarian
     */
    public function index(Request $request)
    {
        $query = Document::query();

        // Filter pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where('file_name', 'like', '%' . $request->search . '%');
        }

        $documents = $query->latest()->paginate(12);
        $documents->appends($request->all());

        // Ambil status dokumen dan stats dari RAG API
        $indexedDocs = [];
        $kbStats = null;
        $hasActiveJobs = false;

        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');

            // Cek active jobs terlebih dahulu
            try {
                $jobsResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/jobs/active");
                if ($jobsResponse->successful()) {
                    $jobsData = $jobsResponse->json();
                    $hasActiveJobs = $jobsData['has_active_jobs'] ?? false;
                }
            } catch (\Exception $e) {
                // Ignore
            }

            // Ambil daftar dokumen
            $response = Http::timeout(30)->get("{$ragApiUrl}/documents");
            if ($response->successful()) {
                $ragDocs = $response->json()['documents'] ?? [];
                foreach ($ragDocs as $doc) {
                    $indexedDocs[$doc['filename']] = $doc;
                }
            }

            // Ambil statistik KB untuk cek kesehatan sistem
            $statsResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/stats");
            if ($statsResponse->successful()) {
                $kbStats = $statsResponse->json();
            }

            // Sync Laravel DB status dengan RAG API jika tidak ada active jobs
            if (!$hasActiveJobs && $kbStats) {
                $vectorOk = in_array($kbStats['vector_store_status'] ?? '', ['healthy', 'unknown', 'indexing']);
                $bm25Ok = in_array($kbStats['bm25_index_status'] ?? '', ['healthy', 'unknown', 'indexing']);
                $systemHealthy = $vectorOk && $bm25Ok;

                if ($systemHealthy) {
                    // Update dokumen yang masih 'processing' tapi sudah indexed di RAG
                    foreach ($documents as $laravelDoc) {
                        if ($laravelDoc->status === 'processing') {
                            $ragDoc = $indexedDocs[$laravelDoc->file_path] ?? null;
                            if ($ragDoc && ($ragDoc['chunk_count'] ?? 0) > 0) {
                                // Dokumen sudah indexed di RAG, update Laravel DB
                                $laravelDoc->update([
                                    'status' => 'done',
                                    'chunk_count' => $ragDoc['chunk_count'] ?? 0,
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Abaikan error
        }

        return view('admin.upload.index', compact('documents', 'indexedDocs', 'kbStats', 'hasActiveJobs'));
    }

    /**
     * Menyimpan dokumen yang diupload (bulk upload)
     */
    public function store(Request $request)
    {
        // Validasi files
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'mimes:pdf|max:51200',
        ], [
            'documents.required' => 'Pilih file untuk diupload.',
            'documents.*.mimes' => 'Hanya file PDF yang diizinkan.',
            'documents.*.max' => 'Ukuran file maksimal 50MB.',
        ]);

        $uploaded = 0;
        $failed = 0;

        foreach ($request->file('documents') as $file) {
            try {
                // Simpan ke temp storage
                $tempPath = $file->store('uploads/temp', 'local');

                // Buat nama file unik (tanpa prefix doc_ karena RAG API akan menambahkan sendiri)
                $extension = $file->getClientOriginalExtension();
                $uniqueFilename = uniqid() . '_' . time() . '.' . $extension;

                // Pindahkan ke folder documents
                $fileContent = Storage::disk('local')->get($tempPath);
                Storage::disk('documents')->put($uniqueFilename, $fileContent);

                // Hapus file temp
                Storage::disk('local')->delete($tempPath);

                // Simpan ke database
                Document::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $uniqueFilename,
                    'file_type' => $extension,
                    'file_size' => $file->getSize(),
                ]);

                $uploaded++;
            } catch (\Exception $e) {
                $failed++;
                Log::warning("Upload failed for {$file->getClientOriginalName()}: " . $e->getMessage());
            }
        }

        // Trigger RAG API untuk refresh metadata
        if ($uploaded > 0) {
            try {
                $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
                Http::timeout(10)->post("{$ragApiUrl}/documents/refresh");
            } catch (\Exception $e) {
                // Abaikan error - ini hanya untuk trigger refresh
            }
        }

        if ($uploaded > 0 && $failed == 0) {
            return back()->with('success', "{$uploaded} dokumen berhasil diupload! Klik Proses untuk memproses.");
        } elseif ($uploaded > 0 && $failed > 0) {
            return back()->with('success', "{$uploaded} berhasil, {$failed} gagal.");
        } else {
            return back()->with('error', 'Upload gagal.');
        }
    }

    /**
     */
    public function destroy($id)
    {
        try {
            $doc = Document::findOrFail($id);

            // Hapus chunks dari RAG API
            $this->deleteFromRagApi($doc->file_path);

            // Hapus file dari storage
            if (Storage::disk('documents')->exists($doc->file_path)) {
                Storage::disk('documents')->delete($doc->file_path);
            }

            $doc->delete();

            // Jika tidak ada dokumen tersisa, clear KB
            if (Document::count() === 0) {
                $this->clearKnowledgeBase();
            }

            return back()->with('success', 'Dokumen berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen. Coba lagi.');
        }
    }

    /**
     * Download dokumen
     */
    public function download($id)
    {
        $doc = Document::findOrFail($id);
        $path = Storage::disk('documents')->path($doc->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download($path, $doc->file_name);
    }

    /**
     * Cek status dokumen di RAG API (untuk polling)
     */
    public function checkStatus($id)
    {
        try {
            $doc = Document::findOrFail($id);
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');

            // Cek apakah ada active jobs (indexing masih berjalan)
            $hasActiveJobs = false;
            try {
                $jobsResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/jobs/active");
                if ($jobsResponse->successful()) {
                    $jobsData = $jobsResponse->json();
                    $hasActiveJobs = $jobsData['has_active_jobs'] ?? false;
                }
            } catch (\Exception $e) {
                // Abaikan error
            }

            // Jika masih ada active jobs, belum indexed
            if ($hasActiveJobs) {
                return response()->json([
                    'indexed' => false,
                    'status' => 'processing',
                    'processing' => true,
                    'chunk_count' => $doc->chunk_count ?? 0,
                ]);
            }

            // Cek KB stats untuk memastikan sistem healthy
            $systemHealthy = false;
            try {
                $kbResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/stats");
                if ($kbResponse->successful()) {
                    $kbStats = $kbResponse->json();
                    $vectorOk = in_array($kbStats['vector_store_status'] ?? '', ['healthy', 'unknown']);
                    $bm25Ok = in_array($kbStats['bm25_index_status'] ?? '', ['healthy', 'unknown']);
                    $systemHealthy = $vectorOk && $bm25Ok;
                }
            } catch (\Exception $e) {
                // Abaikan error
            }

            // Ambil dokumen dari RAG API
            $response = Http::timeout(30)->get("{$ragApiUrl}/documents");

            if ($response->successful()) {
                $ragDocs = $response->json()['documents'] ?? [];

                // Cari dokumen berdasarkan filename
                foreach ($ragDocs as $ragDoc) {
                    if ($ragDoc['filename'] === $doc->file_path) {
                        $hasChunks = ($ragDoc['chunk_count'] ?? 0) > 0;
                        // Dokumen terindeks hanya jika: ada chunks DAN sistem healthy DAN tidak ada active jobs
                        $isIndexed = $hasChunks && $systemHealthy && !$hasActiveJobs;

                        // Update database Laravel jika status berubah
                        if ($isIndexed && $doc->status !== 'done') {
                            $doc->update([
                                'status' => 'done',
                                'chunk_count' => $ragDoc['chunk_count'] ?? 0,
                            ]);
                        }

                        return response()->json([
                            'indexed' => $isIndexed,
                            'status' => $isIndexed ? 'indexed' : ($hasChunks ? 'processing' : 'uploaded'),
                            'processing' => false,
                            'chunk_count' => $ragDoc['chunk_count'] ?? 0,
                        ]);
                    }
                }
            }

            return response()->json(['indexed' => false, 'status' => 'unknown', 'processing' => false]);
        } catch (\Exception $e) {
            return response()->json(['indexed' => false, 'error' => $e->getMessage(), 'processing' => false]);
        }
    }

    /**
     * Menghapus beberapa dokumen sekaligus
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || count($ids) == 0) {
            return back()->with('error', 'Tidak ada dokumen yang dipilih.');
        }

        try {
            $documents = Document::whereIn('id', $ids)->get();

            foreach ($documents as $doc) {
                // Hapus chunks dari RAG API
                $this->deleteFromRagApi($doc->file_path);

                // Hapus file dari storage
                if (Storage::disk('documents')->exists($doc->file_path)) {
                    Storage::disk('documents')->delete($doc->file_path);
                }
                $doc->delete();
            }

            // Jika tidak ada dokumen tersisa, clear KB
            if (Document::count() === 0) {
                $this->clearKnowledgeBase();
            }

            return back()->with('success', 'Dokumen terpilih berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen terpilih. Coba lagi.');
        }
    }    /**
         * Proses satu dokumen ke RAG API (AJAX)
         */
    public function processSingle($id)
    {
        try {
            $doc = Document::findOrFail($id);
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');

            // Update status ke processing
            $doc->update(['status' => 'processing']);

            // Konversi filename ke document_id format RAG API
            $name = pathinfo($doc->file_path, PATHINFO_FILENAME);
            $docId = 'doc_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
            $docId = preg_replace('/_+/', '_', $docId);
            $docId = trim($docId, '_');

            // Proses dokumen spesifik (timeout lebih lama untuk indexing)
            $response = Http::timeout(180)->post("{$ragApiUrl}/chunking/process", [
                'document_ids' => [$docId],
                'auto_index' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Log response for debugging
                Log::info("RAG API response for {$docId}: " . json_encode($data));

                // Cek hasil dari response
                $result = $data['results'][$docId] ?? null;
                $chunks = $result['chunks'] ?? $result['chunk_count'] ?? $data['total_chunks'] ?? 0;

                // SELALU set status ke processing dan indexed=false
                // Frontend polling akan handle kapan refresh setelah semua jobs selesai
                // Ini menghindari race condition antara respons API dan start indexing job

                $doc->update([
                    'status' => 'processing',
                    'chunk_count' => $chunks,
                    'error_message' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Chunking selesai ({$chunks} chunks), menunggu indexing...",
                    'chunks' => $chunks,
                    'status' => 'processing',
                    'indexed' => false, // Selalu false, biar page polling yang handle
                ]);
            } else {
                $doc->update([
                    'status' => 'error',
                    'error_message' => 'RAG API error: ' . $response->status(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses dokumen',
                    'status' => 'error',
                ]);
            }
        } catch (\Exception $e) {
            if (isset($doc)) {
                $doc->update([
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::warning("Process failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dokumen. Pastikan RAG API berjalan.',
                'status' => 'error',
            ]);
        }
    }

    /**
     * Proses semua dokumen ke RAG API
     */
    public function processAll()
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');

            $response = Http::timeout(120)->post("{$ragApiUrl}/chunking/process-all", [
                'auto_index' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $count = $data['documents_queued'] ?? 'semua';
                Log::info("Process-all triggered");
                return back()->with('success', "Memproses {$count} dokumen!");
            } else {
                return back()->with('error', 'Gagal memproses dokumen.');
            }
        } catch (\Exception $e) {
            Log::warning("Process-all failed: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses dokumen. Pastikan RAG API berjalan.');
        }
    }

    /**
     * Hapus dokumen dan chunks dari RAG API
     */
    private function deleteFromRagApi($filename)
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');

            // Konversi filename ke document_id format yang digunakan RAG API
            // RAG API: doc_{name} dimana name adalah filename tanpa extension
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $docId = 'doc_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
            $docId = preg_replace('/_+/', '_', $docId);
            $docId = trim($docId, '_');

            Http::timeout(10)->delete("{$ragApiUrl}/documents/{$docId}");

            Log::info("Document deleted from RAG API: {$docId}");
        } catch (\Exception $e) {
            // Jangan gagalkan delete jika RAG API tidak merespons
            Log::warning("Failed to delete from RAG API {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi database dengan folder storage
     * Menghapus record yang file-nya sudah tidak ada
     * Dan membersihkan Knowledge Base di RAG API
     */
    public function sync()
    {
        $deleted = 0;
        $ragCleared = false;

        try {
            // Ambil semua dokumen dari database
            $documents = Document::all();

            foreach ($documents as $doc) {
                // Cek apakah file masih ada
                if (!Storage::disk('documents')->exists($doc->file_path)) {
                    $doc->delete();
                    $deleted++;
                    Log::info("Orphan record deleted: {$doc->file_path}");
                }
            }

            // Clear Knowledge Base di RAG API
            try {
                $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
                $response = Http::timeout(15)->delete("{$ragApiUrl}/kb/clear?confirm=true");
                if ($response->successful()) {
                    $ragCleared = true;
                    Log::info("RAG Knowledge Base cleared");
                }
            } catch (\Exception $e) {
                Log::warning("Failed to clear RAG KB: " . $e->getMessage());
            }

            $message = "Sinkronisasi selesai.";
            if ($deleted > 0) {
                $message .= " {$deleted} record orphan dihapus.";
            }
            if ($ragCleared) {
                $message .= " Knowledge Base dibersihkan.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Sinkronisasi gagal. Coba lagi nanti.');
        }
    }

    /**
     * Clear Knowledge Base di RAG API
     */
    private function clearKnowledgeBase()
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
            Http::timeout(15)->delete("{$ragApiUrl}/kb/clear?confirm=true");
            Log::info("Knowledge Base auto-cleared (no documents left)");
        } catch (\Exception $e) {
            Log::warning("Failed to auto-clear KB: " . $e->getMessage());
        }
    }
}
