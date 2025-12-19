{{--
Knowledge Base
Halaman untuk melihat data yang sudah diproses oleh RAG API
--}}
@extends('layouts.admin')

@section('title', 'Knowledge Base')
@section('header', 'Knowledge Base')

@section('content')
    {{-- Error Alert --}}
    @if($error)
        <div class="alert-custom alert-error">⚠️ {{ $error }}</div>
    @endif

    {{-- Statistik --}}
    @if($stats)
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-label">Total Dokumen</div>
                <div class="stat-value">{{ $stats['total_documents'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧩</div>
                <div class="stat-label">Total Chunks</div>
                <div class="stat-value">{{ $stats['total_chunks'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-label">Chunks Terindeks</div>
                <div class="stat-value">{{ $stats['indexed_chunks'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💾</div>
                <div class="stat-label">Ukuran Storage</div>
                <div class="stat-value">{{ number_format($stats['storage_size_mb'] ?? 0, 1) }} MB</div>
            </div>
        </div>

        {{-- Status --}}
        <div class="card-section" style="margin-bottom: 20px;">
            <h2 class="section-title">📊 Status Sistem</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">
                        @php
                            $vectorStatus = $stats['vector_store_status'] ?? 'unknown';
                        @endphp
                        @if(in_array($vectorStatus, ['healthy', 'unknown', 'indexing']))
                            ✅
                        @elseif($vectorStatus === 'empty')
                            📦
                        @else
                            ❌
                        @endif
                    </span>
                    <span>Vector Store @if($vectorStatus === 'empty')(Kosong)@endif</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">
                        @php
                            $bm25Status = $stats['bm25_index_status'] ?? 'unknown';
                        @endphp
                        @if(in_array($bm25Status, ['healthy', 'unknown', 'indexing']))
                            ✅
                        @elseif($bm25Status === 'empty')
                            📦
                        @else
                            ❌
                        @endif
                    </span>
                    <span>BM25 Index @if($bm25Status === 'empty')(Kosong)@endif</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Dokumen Terproses --}}
    <div class="card-section">
        <div
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
            <h2 class="section-title" style="margin-bottom: 0;">📁 Dokumen Terproses</h2>
            <div style="display: flex; gap: 10px;">
                <button type="button" id="reindexBtn" onclick="startReindex()"
                    style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px;">
                    🔄 Reindex
                </button>
                <form action="{{ route('admin.knowledge-base.clear') }}" method="POST" style="margin:0;"
                    onsubmit="return confirmDelete(this, 'PERINGATAN: Ini akan menghapus SELURUH Knowledge Base (chunks, vector store, BM25 index). Lanjutkan?');">
                    @csrf
                    <button type="submit"
                        style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px;">
                        🗑️ Clear KB
                    </button>
                </form>
            </div>
        </div>

        @if(count($documents) > 0)
            <div style="overflow-x:auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Chunks</th>
                            <th>Status</th>
                            <th>Diproses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr>
                                <td><strong>{{ $doc['filename'] ?? $doc['document_id'] ?? '-' }}</strong></td>
                                <td>{{ $doc['chunk_count'] ?? $doc['chunks'] ?? '-' }}</td>
                                <td>
                                    {{-- Status sudah dihitung di controller berdasarkan Laravel DB --}}
                                    @php
                                        $displayStatus = $doc['status'] ?? 'pending';
                                    @endphp
                                    @if($displayStatus === 'indexed')
                                        <span style="color: #10b981;">✅ Terindeks</span>
                                    @elseif($displayStatus === 'processing')
                                        <span style="color: #3b82f6;">⏳ Memproses</span>
                                    @elseif($displayStatus === 'needs_reindex')
                                        <span style="color: #f59e0b;">⚠️ Perlu Reindex</span>
                                    @elseif($displayStatus === 'uploaded')
                                        <span style="color: #3b82f6;">📤 Terupload</span>
                                    @else
                                        <span style="color: #64748b;">⏸️ Menunggu</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $dateStr = $doc['processed_at'] ?? $doc['created_at'] ?? null;
                                        if ($dateStr) {
                                            try {
                                                $date = \Carbon\Carbon::parse($dateStr);
                                                echo $date->format('Y-m-d H:i');
                                            } catch (\Exception $e) {
                                                echo '-';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div style="font-size:60px;">📭</div>
                @if($error)
                    RAG API tidak tersedia. Pastikan API sudah berjalan.
                @else
                    Belum ada dokumen yang diproses.
                @endif
            </div>
        @endif
    </div>

    <script>
        async function startReindex() {
            const btn = document.getElementById('reindexBtn');
            const originalText = btn.innerHTML;

            // Create or get toast notification (fixed position)
            let toast = document.getElementById('reindexToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'reindexToast';
                toast.style.cssText = `
                                                        position: fixed;
                                                        bottom: 20px;
                                                        right: 20px;
                                                        width: 320px;
                                                        padding: 20px;
                                                        background: white;
                                                        border-radius: 16px;
                                                        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                                                        z-index: 9999;
                                                        border-left: 4px solid #0ea5e9;
                                                    `;
                document.body.appendChild(toast);
            }

            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '⏳ Memproses...';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';

            toast.innerHTML = `
                                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                                        <span style="font-size: 24px;">🔄</span>
                                                        <span style="font-weight: 600; color: #1e293b;">Reindexing</span>
                                                    </div>
                                                    <div style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden; margin-bottom: 8px;">
                                                        <div id="reindexProgress" style="background: linear-gradient(90deg, #0ea5e9, #0284c7); height: 100%; width: 5%; transition: width 0.5s;"></div>
                                                    </div>
                                                    <div id="reindexTime" style="color: #64748b; font-size: 12px;">Waktu: 0 detik</div>
                                                `;

            try {
                // Send reindex request
                const response = await fetch('{{ route("admin.knowledge-base.reindex") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const responseData = await response.json();

                // Start polling for status
                let pollCount = 0;
                const maxPolls = 60; // Max 5 menit (60 x 5s)

                const pollStatus = setInterval(async () => {
                    pollCount++;
                    const seconds = pollCount * 5;
                    const progress = Math.min(95, (pollCount / maxPolls) * 100);

                    const progressBar = document.getElementById('reindexProgress');
                    const timeDiv = document.getElementById('reindexTime');
                    if (progressBar) progressBar.style.width = progress + '%';
                    if (timeDiv) timeDiv.textContent = `Waktu: ${seconds} detik`;

                    if (pollCount >= maxPolls) {
                        clearInterval(pollStatus);
                        showToastSuccess('Reindex selesai!');
                        return;
                    }

                    try {
                        // Check KB stats
                        const statsResponse = await fetch('/admin/knowledge-base/status', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const stats = await statsResponse.json();

                        // If chunks indexed matches total_chunks, done
                        if (stats.indexed_chunks > 0 && stats.indexed_chunks >= stats.total_chunks) {
                            clearInterval(pollStatus);
                            showToastSuccess('Reindex selesai!');
                        }
                    } catch (e) {
                        // Continue polling
                    }
                }, 5000);

            } catch (error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                showToastError(error.message);
            }
        }

        function showToastSuccess(message) {
            const toast = document.getElementById('reindexToast');
            if (toast) {
                toast.style.borderLeftColor = '#10b981';
                toast.innerHTML = `
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <span style="font-size: 24px;">✅</span>
                                                            <span style="font-weight: 600; color: #065f46;">${message}</span>
                                                        </div>
                                                    `;
                setTimeout(() => window.location.reload(), 1500);
            }
        }

        function showToastError(message) {
            const toast = document.getElementById('reindexToast');
            if (toast) {
                toast.style.borderLeftColor = '#ef4444';
                toast.innerHTML = `
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <span style="font-size: 24px;">❌</span>
                                                            <span style="font-weight: 600; color: #991b1b;">Error: ${message}</span>
                                                        </div>
                                                    `;
                setTimeout(() => toast.remove(), 5000);
            }
        }

        // Auto-polling on page load - auto-refresh when active jobs finish
        let wasProcessing = {{ $hasActiveJobs ? 'true' : 'false' }};

        async function checkActiveJobsAndPoll() {
            if (!wasProcessing) return; // Only poll if there were active jobs when page loaded

            try {
                const response = await fetch('/admin/knowledge-base/jobs/active', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.has_active_jobs) {
                    // Still processing - poll again in 3 seconds
                    setTimeout(() => checkActiveJobsAndPoll(), 3000);
                } else {
                    // Jobs finished, refresh the page to show updated status
                    window.location.reload();
                }
            } catch (e) {
                console.log('Active jobs check error:', e);
                // Retry after 5 seconds on error
                setTimeout(() => checkActiveJobsAndPoll(), 5000);
            }
        }

        // Start checking on page load if there are active jobs
        document.addEventListener('DOMContentLoaded', checkActiveJobsAndPoll);
    </script>
@endsection
