<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk fitur chat
 * Menangani daftar chat, kirim pesan, pin, rename, dan hapus chat
 */
class ChatController extends Controller
{
    /**
     * Menampilkan halaman chat utama
     */
    public function index()
    {
        $chats = Chat::where('user_id', Auth::id())
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('chat', [
            'chats' => $chats,
            'messages' => []
        ]);
    }

    /**
     * Memuat chat tertentu berdasarkan ID
     */
    public function loadChat($id)
    {
        $chats = Chat::where('user_id', Auth::id())
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeChat = Chat::where('user_id', Auth::id())->find($id);

        $messages = Message::where('chat_id', $id)
            ->orderBy('created_at')
            ->get();

        return view('chat', [
            'chats' => $chats,
            'messages' => $messages,
            'active_chat' => $activeChat
        ]);
    }

    /**
     * Membuat chat baru
     */
    public function newChat(Request $request)
    {
        $chat = Chat::create([
            'user_id' => Auth::id(),
            'title' => 'Obrolan Baru'
        ]);

        return response()->json([
            'success' => true,
            'chat_id' => $chat->id
        ]);
    }

    /**
     * Mengirim pesan dan mendapatkan respons dari AI
     */
    public function send(Request $request)
    {
        // Validasi input
        $request->validate([
            'chat_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $chat = Chat::where('user_id', Auth::id())->findOrFail($request->chat_id);

        // Simpan pesan user
        $msg = Message::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'user_message' => $request->message,
            'bot_response' => null,
        ]);

        // Auto-update judul dari pesan pertama
        if ($chat->title === 'Obrolan Baru') {
            $title = $this->generateTitleFromMessage($request->message);
            $chat->update(['title' => $title]);
        }

        // Panggil Model API RAG untuk mendapatkan jawaban
        try {
            // Gunakan 127.0.0.1 bukan localhost 
            $ragApiUrl = env('RAG_API_URL', 'http://127.0.0.1:5001/api');

            // Payload sesuai format API teman
            $payload = [
                'question' => $request->message,
                'pipeline_type' => 'advanced',
                'max_results' => 5
            ];

            Log::info('📤 Mengirim ke RAG API', [
                'url' => "{$ragApiUrl}/query",
                'payload' => $payload
            ]);

            $response = Http::timeout(60)
                ->connectTimeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$ragApiUrl}/query", $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Ambil jawaban dari response
                $botText = $data['answer'] ?? $data['response'] ?? "Saya tidak dapat menemukan jawaban.";

                // Ekstrak sumber referensi untuk ditampilkan - dengan lookup ke Document Laravel
                $sources = [];
                $seenNames = []; // Untuk deduplikasi

                if (isset($data['sources']) && is_array($data['sources'])) {
                    foreach ($data['sources'] as $source) {
                        $metadata = $source['metadata'] ?? [];
                        $docName = null;

                        // Coba ambil original_filename dari metadata terlebih dahulu
                        $possibleKeys = ['original_filename', 'filename', 'doc_filename', 'source', 'title'];
                        foreach ($possibleKeys as $key) {
                            if (!empty($metadata[$key])) {
                                $docName = $metadata[$key];
                                // Ekstrak basename jika berupa path
                                if (str_contains($docName, '/') || str_contains($docName, '\\')) {
                                    $docName = basename($docName);
                                }
                                break;
                            }
                        }

                        // Jika nama file terproses, cari nama asli dari Document Laravel
                        $filePath = null;
                        $docId = null;

                        if ($docName && preg_match('/^[a-f0-9]+_\d+\.(pdf|docx?|txt)$/i', $docName)) {
                            $doc = Document::where('file_path', $docName)->first();
                            if ($doc) {
                                $filePath = $doc->file_path;
                                $docId = $doc->id;
                                if ($doc->file_name) {
                                    $docName = $doc->file_name;
                                }
                            }
                        } else {
                            // Coba cari berdasarkan nama file asli
                            $doc = Document::where('file_name', $docName)->first();
                            if ($doc) {
                                $filePath = $doc->file_path;
                                $docId = $doc->id;
                            }
                        }

                        // Lewati jika tidak ada nama atau sudah ditampilkan (deduplikasi)
                        if (!$docName || $docName === 'Dokumen' || in_array($docName, $seenNames)) {
                            continue;
                        }

                        $seenNames[] = $docName;
                        $sources[] = [
                            'name' => $docName,
                            'score' => round($source['score'] ?? 0, 2),
                            'file_path' => $filePath,
                            'doc_id' => $docId
                        ];
                    }
                }

                Log::info('✅ RAG API Success', [
                    'status' => $response->status(),
                    'answer_length' => strlen($botText),
                    'sources_count' => count($sources),
                    'sources' => $sources
                ]);
            } else {
                $errorData = $response->json();

                Log::error('❌ RAG API Error', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'sent_payload' => $payload
                ]);

                // Tampilkan error yang lebih informatif
                if ($response->status() === 422) {
                    $detail = $errorData['detail'] ?? 'Format tidak sesuai';
                    $botText = "Maaf, format pertanyaan tidak valid. Detail: " . json_encode($detail);
                } elseif ($response->status() === 500) {
                    $botText = "Maaf, terjadi kesalahan pada server AI. Silakan coba lagi.";
                } else {
                    $botText = "Maaf, terjadi kesalahan pada sistem RAG (Status: {$response->status()}).";
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('🔌 RAG API Connection Error', [
                'message' => $e->getMessage(),
                'url' => $ragApiUrl ?? 'unknown'
            ]);

            $botText = "⚠️ Tidak dapat terhubung ke server AI.\n\n";
            $botText .= "Pastikan:\n";
            $botText .= "1. Server Python berjalan: uvicorn api.main:app --reload --port 5001\n";
            $botText .= "2. Port 5001 tidak diblokir firewall\n";
            $botText .= "3. Akses http://127.0.0.1:5001 di browser untuk cek status";
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('📡 RAG API Request Error', [
                'message' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : null
            ]);

            $botText = "Maaf, permintaan ke server AI gagal: " . $e->getMessage();
        } catch (\Exception $e) {
            Log::error('❌ RAG API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $botText = "Maaf, layanan AI tidak tersedia: " . $e->getMessage();
        }

        // Simpan respons bot dan sources
        $msg->update([
            'bot_response' => $botText,
            'sources' => $sources ?? []
        ]);

        return response()->json([
            'reply' => $botText,
            'title' => $chat->fresh()->title,
            'sources' => $sources ?? []
        ]);
    }

    /**
     * Membuat judul dari pesan (maksimal 30 karakter)
     */
    private function generateTitleFromMessage(string $message): string
    {
        // Bersihkan whitespace berlebih
        $clean = preg_replace('/\s+/', ' ', trim($message));

        // Jika pendek, langsung kembalikan
        if (strlen($clean) <= 30) {
            return $clean;
        }

        // Potong dan tambahkan ellipsis
        $truncated = substr($clean, 0, 30);
        $lastSpace = strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated . '...';
    }

    /**
     * Pin atau unpin chat
     */
    public function pin(Request $request, $id)
    {
        $chat = Chat::where('user_id', Auth::id())->findOrFail($id);
        $chat->update(['is_pinned' => $request->input('pinned', false)]);

        return response()->json(['success' => true]);
    }

    /**
     * Mengubah nama chat
     */
    public function rename(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:100'
        ]);

        $chat = Chat::where('user_id', Auth::id())->findOrFail($id);
        $chat->update(['title' => $request->input('title')]);

        return response()->json(['success' => true]);
    }

    /**
     * Menghapus chat beserta pesannya
     */
    public function delete($id)
    {
        $chat = Chat::where('user_id', Auth::id())->findOrFail($id);

        Message::where('chat_id', $chat->id)->delete();
        $chat->delete();

        return response()->json(['success' => true]);
    }
}
