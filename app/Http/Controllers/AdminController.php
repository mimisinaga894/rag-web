<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk fitur admin
 * Menangani dashboard, manajemen user, dan pengaturan
 */
class AdminController extends Controller
{
    /**
     * Menampilkan dashboard admin
     */
    public function dashboard()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $documents = Document::latest()->get();
        $totalChats = Chat::count();

        return view('admin.AdminDashboard', compact(
            'totalUsers',
            'totalAdmins',
            'documents',
            'totalChats'
        ));
    }

    /**
     * Menampilkan daftar user
     */
    public function users()
    {
        $users = User::where('role', 'user')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah user
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan user baru
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'nim' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'nim' => $validated['nim'] ?? null,
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit user
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui data user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'nim' => 'nullable|string|max:20',
            'role' => 'required|in:admin,user',
        ]);

        $user->update($validated);

        // Update password jika diisi
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Menghapus user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Cegah hapus akun sendiri
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus!');
    }
    /**
     * Menampilkan halaman pengaturan admin
     */
    public function settings()
    {
        $user = auth()->user();
        return view('admin.settings', compact('user'));
    }

    /**
     * Memperbarui pengaturan admin
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        // Update password jika diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:6|confirmed',
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    /**
     * Menghapus dokumen
     */
    public function deleteDocument($id)
    {
        $document = Document::findOrFail($id);

        try {
            // Hapus file dari storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return redirect()->back()
                ->with('success', 'Dokumen berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman Knowledge Base (data yang sudah diproses)
     */
    public function knowledgeBase()
    {
        $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
        $stats = null;
        $documents = [];
        $error = null;
        $hasActiveJobs = false;

        try {
            // Cek apakah ada active jobs (indexing berjalan)
            try {
                $jobsResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/jobs/active");
                if ($jobsResponse->successful()) {
                    $jobsData = $jobsResponse->json();
                    $hasActiveJobs = $jobsData['has_active_jobs'] ?? false;
                }
            } catch (\Exception $e) {
                // Ignore
            }

            // Ambil statistik dari RAG API
            $statsResponse = Http::timeout(30)->get("{$ragApiUrl}/kb/stats");
            if ($statsResponse->successful()) {
                $stats = $statsResponse->json();
            }

            // Ambil daftar dokumen dari RAG API
            $ragDocs = [];
            $docsResponse = Http::timeout(30)->get("{$ragApiUrl}/documents");
            if ($docsResponse->successful()) {
                $ragDocsRaw = $docsResponse->json()['documents'] ?? [];
                foreach ($ragDocsRaw as $doc) {
                    $ragDocs[$doc['filename']] = $doc;
                }
            }

            // Ambil dokumen dari Laravel DB dan gabungkan dengan data RAG API
            $laravelDocs = Document::all();

            // Cek system health - 'indexing' status means indexing in progress, treat as healthy
            $vectorOk = in_array($stats['vector_store_status'] ?? 'unknown', ['healthy', 'unknown', 'indexing']);
            $bm25Ok = in_array($stats['bm25_index_status'] ?? 'unknown', ['healthy', 'unknown', 'indexing']);
            $systemHealthy = $vectorOk && $bm25Ok;

            foreach ($laravelDocs as $laravelDoc) {
                $ragDoc = $ragDocs[$laravelDoc->file_path] ?? null;
                $chunkCount = $ragDoc['chunk_count'] ?? $laravelDoc->chunk_count ?? 0;

                // Sync DB status jika tidak ada active jobs dan sistem healthy
                if (!$hasActiveJobs && $systemHealthy && $laravelDoc->status === 'processing' && $chunkCount > 0) {
                    $laravelDoc->update([
                        'status' => 'done',
                        'chunk_count' => $chunkCount,
                    ]);
                }

                // Tentukan status untuk ditampilkan berdasarkan Laravel DB
                $dbStatus = $laravelDoc->status;
                if ($dbStatus === 'processing') {
                    $displayStatus = 'processing';
                } elseif ($dbStatus === 'done' && $chunkCount > 0) {
                    // Jika DB bilang done dan ada chunks, tampilkan indexed
                    // Bahkan jika sistem sedang unhealthy (karena indexing lain berjalan)
                    // Hanya tampilkan needs_reindex jika TIDAK ada active jobs DAN sistem unhealthy
                    if ($systemHealthy || $hasActiveJobs) {
                        $displayStatus = 'indexed';
                    } else {
                        $displayStatus = 'needs_reindex';
                    }
                } elseif ($chunkCount > 0 && !$systemHealthy && !$hasActiveJobs) {
                    // Hanya show needs_reindex jika tidak ada active jobs
                    $displayStatus = 'needs_reindex';
                } elseif ($dbStatus === 'uploaded' || $dbStatus === 'pending') {
                    $displayStatus = 'uploaded';
                } else {
                    $displayStatus = 'pending';
                }

                $documents[] = [
                    'document_id' => $ragDoc['document_id'] ?? 'doc_' . $laravelDoc->id,
                    'filename' => $laravelDoc->file_path,
                    'chunk_count' => $chunkCount,
                    'status' => $displayStatus, // Gunakan display status, bukan raw status
                    'indexed' => $displayStatus === 'indexed',
                    'processed_at' => $laravelDoc->updated_at,
                    'created_at' => $laravelDoc->created_at,
                ];
            }

        } catch (\Exception $e) {
            $error = 'RAG API tidak tersedia. Pastikan server API sudah berjalan.';
            Log::warning("Knowledge Base API error: " . $e->getMessage());
        }

        return view('admin.knowledge-base.index', compact('stats', 'documents', 'error', 'hasActiveJobs'));
    }

    /**
     * Clear Knowledge Base (menghapus semua chunks & index + dokumen)
     */
    public function clearKnowledgeBase()
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
            $response = Http::timeout(30)->delete("{$ragApiUrl}/kb/clear?confirm=true");

            if ($response->successful()) {
                // Hapus juga semua dokumen dari Laravel
                $documents = Document::all();
                foreach ($documents as $doc) {
                    if (Storage::disk('documents')->exists($doc->file_path)) {
                        Storage::disk('documents')->delete($doc->file_path);
                    }
                    $doc->delete();
                }

                Log::info("Knowledge Base and all documents cleared by admin");
                return back()->with('success', 'Knowledge Base dan semua dokumen berhasil dibersihkan!');
            } else {
                return back()->with('error', 'Gagal membersihkan Knowledge Base.');
            }
        } catch (\Exception $e) {
            Log::warning("Clear KB failed: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat membersihkan KB. Coba lagi nanti.');
        }
    }

    /**
     * Reindex Knowledge Base (rebuild vector store & BM25)
     */
    public function reindexKnowledgeBase(Request $request)
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
            $response = Http::timeout(120)->post("{$ragApiUrl}/kb/reindex", [
                'force' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $chunks = $data['chunks_indexed'] ?? $data['total_chunks'] ?? 0;
                Log::info("Knowledge Base reindexed: {$chunks} chunks");

                // Kembalikan JSON untuk request AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Reindex berhasil! {$chunks} chunks diindeks.",
                        'chunks_indexed' => $chunks
                    ]);
                }

                return back()->with('success', "Reindex berhasil! {$chunks} chunks diindeks.");
            } else {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal reindex Knowledge Base.'
                    ], 500);
                }
                return back()->with('error', 'Gagal reindex Knowledge Base.');
            }
        } catch (\Exception $e) {
            Log::warning("Reindex KB failed: " . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat reindex: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat reindex. Coba lagi nanti.');
        }
    }

    /**
     * Get KB status for AJAX polling
     */
    public function kbStatus()
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
            $response = Http::timeout(5)->get("{$ragApiUrl}/kb/stats");

            if ($response->successful()) {
                $stats = $response->json();
                return response()->json([
                    'vector_healthy' => ($stats['vector_store_status'] ?? 'unknown') === 'healthy',
                    'bm25_healthy' => ($stats['bm25_index_status'] ?? 'unknown') === 'healthy',
                    'total_chunks' => $stats['total_chunks'] ?? 0,
                    'indexed_chunks' => $stats['indexed_chunks'] ?? 0,
                ]);
            }

            return response()->json(['vector_healthy' => false, 'bm25_healthy' => false]);
        } catch (\Exception $e) {
            return response()->json(['vector_healthy' => false, 'bm25_healthy' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Check if there are any active indexing jobs
     * Used for smart polling - only poll when jobs are running
     */
    public function activeJobs()
    {
        try {
            $ragApiUrl = env('RAG_API_URL', 'http://localhost:5001/api');
            $response = Http::timeout(5)->get("{$ragApiUrl}/kb/jobs/active");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['has_active_jobs' => false]);
        } catch (\Exception $e) {
            return response()->json(['has_active_jobs' => false, 'error' => $e->getMessage()]);
        }
    }
}
