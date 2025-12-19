{{--
Upload Dokumen
Halaman untuk upload dan kelola dokumen
--}}
@extends('layouts.admin')

@section('title', 'Dokumen')
@section('header', 'Kelola Dokumen')

@section('content')
    {{-- Upload Section --}}
    <div class="card-section">
        <h2 class="section-title">📤 Upload Dokumen</h2>
        <form action="{{ route('admin.upload.save') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <label class="upload-box" for="fileInput" id="dropZone">
                <div class="upload-icon" id="uploadIcon" style="font-size:48px;">📎</div>
                <div id="uploadText" style="font-weight:600; margin:10px 0;">Klik atau seret file ke sini</div>
                <div id="uploadHint" style="color:#64748b; font-size:14px;">PDF saja (Maks: 50MB per file)</div>
                <div id="fileCount" style="color:#10b981; font-weight:600; margin-top:10px;"></div>
                <input type="file" name="documents[]" id="fileInput" style="display:none" accept=".pdf" multiple required>
            </label>
            <button type="submit" id="uploadBtn"
                style="display:none; margin-top:15px; width:100%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s;">
                📤 Upload Files
            </button>
        </form>
    </div>


    {{-- Documents List --}}
    <div class="card-section">
        <h2 class="section-title">📁 Dokumen Terupload</h2>

        @if($documents->count() > 0)
            {{-- Toolbar --}}
            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <form method="GET" action="{{ route('admin.upload.index') }}" style="max-width: 300px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari dokumen..."
                        class="form-control">
                </form>
                <div style="display:flex; align-items:center; gap:15px; flex-wrap: wrap;">
                    <span id="selectedCount" style="color:#64748b; font-size:14px;"></span>
                    <button id="bulkProcessBtn" class="btn-bulk-process" disabled
                        style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; cursor: pointer; opacity: 0.5;">
                        ⚡ Proses Terpilih
                    </button>
                    <button id="bulkDeleteBtn" class="btn-bulk-delete" disabled>Hapus Terpilih</button>
                </div>
            </div>

            {{-- Tabel --}}
            <form id="bulkDeleteForm" action="{{ route('admin.upload.bulkDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <div style="overflow-x:auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Nama File</th>
                                <th>Tipe</th>
                                <th>Ukuran</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr data-doc-id="{{ $doc->id }}">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $doc->id }}" class="doc-checkbox">
                                    </td>
                                    <td><strong>{{ $doc->file_name }}</strong></td>
                                    <td>{{ strtoupper($doc->file_type) }}</td>
                                    <td>{{ number_format($doc->file_size / 1024 / 1024, 2) }} MB</td>
                                    <td>{{ $doc->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @php
                                            $ragDoc = $indexedDocs[$doc->file_path] ?? null;
                                            $hasChunks = $ragDoc && (($ragDoc['chunk_count'] ?? 0) > 0);

                                            // Cek system health - unknown dianggap healthy (belum dicek bukan berarti rusak)
                                            $vectorStatus = $kbStats['vector_store_status'] ?? 'unknown';
                                            $bm25Status = $kbStats['bm25_index_status'] ?? 'unknown';
                                            $vectorHealthy = in_array($vectorStatus, ['healthy', 'unknown', 'indexing']);
                                            $bm25Healthy = in_array($bm25Status, ['healthy', 'unknown', 'indexing']);
                                            $systemHealthy = $vectorHealthy && $bm25Healthy;

                                            // Cek status dari database Laravel (sumber kebenaran utama)
                                            $dbStatus = $doc->status ?? 'pending';

                                            // PRIORITAS STATUS:
                                            // 1. DB Processing - jika DB bilang processing
                                            // 2. Indexed - jika punya chunks DAN sistem healthy DAN DB done
                                            // 3. Perlu Reindex - HANYA jika punya chunks tapi sistem unhealthy
                                            // 4. Pending - belum diproses

                                            // Hanya dokumen dengan status 'processing' yang tampil processing
                                            $isProcessing = $dbStatus === 'processing';
                                            $dbSaysIndexed = $dbStatus === 'done';

                                            if ($isProcessing) {
                                                $displayStatus = 'processing';
                                            } elseif ($dbSaysIndexed && $hasChunks && $systemHealthy) {
                                                $displayStatus = 'indexed';
                                            } elseif ($hasChunks && !$systemHealthy) {
                                                $displayStatus = 'needs_reindex';
                                            } elseif ($dbSaysIndexed && !$hasChunks) {
                                                // DB bilang done tapi belum ada chunks - masih processing
                                                $displayStatus = 'processing';
                                            } else {
                                                $displayStatus = 'pending';
                                            }
                                        @endphp
                                        <span
                                            class="status-badge {{ $displayStatus === 'indexed' ? 'status-done' : ($displayStatus === 'needs_reindex' ? 'status-warning' : ($displayStatus === 'processing' ? 'status-processing' : '')) }}"
                                            id="status-{{ $doc->id }}" data-db-status="{{ $dbStatus }}">
                                            @if($displayStatus === 'processing')
                                                ⏳ Memproses
                                            @elseif($displayStatus === 'indexed')
                                                ✅ Terindeks
                                            @elseif($displayStatus === 'needs_reindex')
                                                ⚠️ Perlu Reindex
                                            @else
                                                📤 Terupload
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:5px;">
                                            @if($displayStatus === 'indexed')
                                                <button type="button" class="btn-process" title="Sudah Terindeks" disabled
                                                    style="background: #10b981; color: #fff; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; opacity: 0.7;">
                                                    ✓
                                                </button>
                                            @elseif($displayStatus === 'processing')
                                                <button type="button" class="btn-process" title="Sedang Memproses" disabled
                                                    style="background: #3b82f6; color: #fff; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; opacity: 0.7;">
                                                    ⏳
                                                </button>
                                            @else
                                                <button type="button" class="btn-process" title="Proses" data-id="{{ $doc->id }}"
                                                    onclick="processDocument({{ $doc->id }})"
                                                    style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                                    ⚡
                                                </button>
                                            @endif
                                            <a href="{{ route('dokumen.download', $doc->id) }}" target="_blank" title="Download"
                                                style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                                                📥
                                            </a>
                                            <button type="button" class="btn-delete" title="Hapus"
                                                onclick="deleteDocument({{ $doc->id }}, '{{ addslashes($doc->file_name) }}')"
                                                style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

            {{-- Pagination --}}
            @if($documents->hasPages())
                <div style="margin-top: 20px;">
                    {{ $documents->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div style="font-size:60px;">📂</div>
                Belum ada dokumen yang diupload.
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.doc-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkProcessBtn = document.getElementById('bulkProcessBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const selectedCount = document.getElementById('selectedCount');

        // Update status tombol bulk (delete & process) dan jumlah terpilih
        function updateBulkButtons() {
            const checked = document.querySelectorAll('.doc-checkbox:checked');
            const count = checked.length;

            if (bulkDeleteBtn) {
                bulkDeleteBtn.disabled = count === 0;
            }

            if (bulkProcessBtn) {
                bulkProcessBtn.disabled = count === 0;
                bulkProcessBtn.style.opacity = count === 0 ? '0.5' : '1';
            }

            if (selectedCount) {
                selectedCount.textContent = count > 0 ? `${count} dokumen dipilih` : '';
            }
        }

        // Select all checkbox
        selectAll?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkButtons();
        });

        // Individual checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const allChecked = [...checkboxes].every(c => c.checked);
                const someChecked = [...checkboxes].some(c => c.checked);
                if (selectAll) {
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                }
                updateBulkButtons();
            });
        });

        // Bulk delete button
        bulkDeleteBtn?.addEventListener('click', function () {
            const checked = document.querySelectorAll('.doc-checkbox:checked');
            if (checked.length === 0) {
                return; // Button should be disabled, but just in case
            }
            showConfirmModal('Hapus Dokumen', 'Hapus ' + checked.length + ' dokumen terpilih?', function () {
                bulkDeleteForm.submit();
            });
        });

        // Bulk process button - proses dokumen terpilih SEQUENTIAL
        bulkProcessBtn?.addEventListener('click', async function () {
            const checked = document.querySelectorAll('.doc-checkbox:checked');
            if (checked.length === 0) return;

            bulkProcessBtn.disabled = true;

            let processed = 0;
            let skipped = 0;
            let failed = 0;
            const total = checked.length;

            for (const cb of checked) {
                const docId = cb.value;
                // Update progress
                bulkProcessBtn.innerHTML = `⏳ Memproses ${processed + skipped + failed + 1}/${total}...`;

                // Cek apakah dokumen sudah terindeks (button menampilkan ✓)
                const row = cb.closest('tr');
                const processBtn = row.querySelector('.btn-process');
                const isAlreadyIndexed = processBtn && processBtn.textContent.trim() === '✓';

                if (isAlreadyIndexed) {
                    skipped++;
                    continue; // Skip dokumen yang sudah terindeks
                }

                try {
                    // Await proses dokumen dengan timeout
                    const success = await processDocumentSync(docId);
                    if (success) {
                        processed++;
                    } else {
                        failed++;
                    }
                    // Delay antar dokumen untuk tidak overload API
                    await new Promise(resolve => setTimeout(resolve, 500));
                } catch (e) {
                    console.error(e);
                    failed++;
                }
            }

            bulkProcessBtn.innerHTML = '⚡ Proses Terpilih';
            bulkProcessBtn.disabled = false;
            updateBulkButtons();

            // Show result
            let msg = `Selesai! ${processed} berhasil`;
            if (skipped > 0) msg += `, ${skipped} sudah terindeks`;
            if (failed > 0) msg += `, ${failed} gagal`;
            console.log(msg);

            // Start polling for completion instead of immediate reload
            if (processed > 0) {
                isPollingForCompletion = true;
                pollAttempts = 0; // Reset counter
                pollForCompletion();
            }
        });

        // File input change - show count and upload button
        const fileInput = document.getElementById('fileInput');
        const fileCount = document.getElementById('fileCount');
        const uploadBtn = document.getElementById('uploadBtn');
        const dropZone = document.getElementById('dropZone');
        const uploadForm = document.getElementById('uploadForm');
        const uploadIcon = document.getElementById('uploadIcon');
        const uploadText = document.getElementById('uploadText');
        const uploadHint = document.getElementById('uploadHint');

        function updateFileDisplay(files) {
            const count = files.length;
            if (count > 0) {
                fileCount.textContent = `${count} file dipilih`;
                uploadBtn.style.display = 'block';
                uploadBtn.textContent = `📤 Upload ${count} File`;
            } else {
                fileCount.textContent = '';
                uploadBtn.style.display = 'none';
            }
        }

        // Handle form submit - show loading state
        uploadForm?.addEventListener('submit', function (e) {
            // DON'T disable file input - it prevents file from being sent!
            // Just disable visual interaction
            dropZone.style.pointerEvents = 'none';
            dropZone.style.opacity = '0.7';

            // Update upload box UI
            uploadIcon.innerHTML = '⏳';
            uploadIcon.style.animation = 'spin 1s linear infinite';
            uploadText.textContent = 'Mengupload...';
            uploadHint.textContent = 'Mohon tunggu, jangan tutup halaman ini';
            fileCount.innerHTML = '<span class="uploading-dots">Memproses file</span>';

            // Update button
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '⏳ Mengupload...';
            uploadBtn.style.background = '#94a3b8';
            uploadBtn.style.cursor = 'not-allowed';
        });

        fileInput?.addEventListener('change', function () {
            updateFileDisplay(this.files);
        });

        // Drag and Drop handlers
        dropZone?.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
        });

        dropZone?.addEventListener('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
        });

        dropZone?.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');

            const files = e.dataTransfer.files;

            // Filter hanya PDF
            const pdfFiles = Array.from(files).filter(f => f.type === 'application/pdf' || f.name.toLowerCase().endsWith('.pdf'));

            if (pdfFiles.length === 0) {
                alert('Hanya file PDF yang diizinkan!');
                return;
            }

            if (pdfFiles.length !== files.length) {
                alert(`${files.length - pdfFiles.length} file bukan PDF dan dilewati.`);
            }

            // Create new DataTransfer to set files
            const dt = new DataTransfer();
            pdfFiles.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;

            updateFileDisplay(fileInput.files);
        });

        // Synchronous version untuk bulk process - returns Promise
        async function processDocumentSync(id) {
            const statusEl = document.getElementById(`status-${id}`);
            const btn = document.querySelector(`button[data-id="${id}"]`);

            if (!statusEl || !btn) return false;

            // Update UI to processing
            statusEl.className = 'status-badge status-processing';
            statusEl.innerHTML = '⏳ Memproses';
            btn.disabled = true;
            btn.innerHTML = '⏳';

            try {
                const response = await fetch(`/admin/upload/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Keep status as indexing - page will refresh when all jobs complete
                    statusEl.className = 'status-badge status-processing';
                    statusEl.innerHTML = '⏳ Memproses';
                    return true;
                } else {
                    statusEl.className = 'status-badge status-error';
                    statusEl.innerHTML = '❌ Error';
                    btn.innerHTML = '⚡';
                    btn.disabled = false;
                    return false;
                }
            } catch (error) {
                statusEl.className = 'status-badge status-error';
                statusEl.innerHTML = '❌ Error';
                btn.innerHTML = '⚡';
                btn.disabled = false;
                return false;
            }
        }


        // Global flag to track if we should poll for completion
        let isPollingForCompletion = {{ $hasActiveJobs ? 'true' : 'false' }};
        let pollAttempts = 0;
        const maxWaitAttempts = 3; // Wait max 3 attempts (6 seconds) for active jobs to appear

        // Simple page-level polling - auto-refresh when all jobs complete
        async function pollForCompletion() {
            if (!isPollingForCompletion) return;

            try {
                const response = await fetch('/admin/knowledge-base/jobs/active', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                pollAttempts++;

                if (data.has_active_jobs) {
                    // Jobs are running - reset counter and keep polling
                    pollAttempts = 0;
                    setTimeout(() => pollForCompletion(), 3000);
                } else if (pollAttempts >= maxWaitAttempts) {
                    // No active jobs and we've waited enough
                    // Trigger a final reindex to ensure all chunks are indexed
                    await triggerReindexAndReload();
                } else {
                    // No active jobs yet, but wait a bit more in case they're about to start
                    setTimeout(() => pollForCompletion(), 2000);
                }
            } catch (e) {
                console.error('Polling error:', e);
                // Retry after 5 seconds on error
                setTimeout(() => pollForCompletion(), 5000);
            }
        }

        // Trigger reindex and then reload page
        async function triggerReindexAndReload() {
            try {
                // First check if reindex is needed (total chunks != indexed chunks)
                const statsResponse = await fetch('/admin/knowledge-base/status', {
                    headers: { 'Accept': 'application/json' }
                });

                if (statsResponse.ok) {
                    const stats = await statsResponse.json();
                    const totalChunks = stats.total_chunks || 0;
                    const indexedChunks = stats.indexed_chunks || 0;

                    console.log(`Checking: total=${totalChunks}, indexed=${indexedChunks}`);

                    if (totalChunks > 0 && totalChunks === indexedChunks) {
                        console.log('Chunks already indexed, skipping reindex');
                        window.location.reload();
                        return;
                    }
                }

                console.log('Triggering final reindex...');
                const response = await fetch('/admin/knowledge-base/reindex', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    console.log('Reindex triggered, waiting for completion...');
                    // Wait for reindex to complete before reloading
                    await waitForReindexComplete();
                }
            } catch (e) {
                console.error('Reindex error:', e);
            }
            // Reload page regardless
            window.location.reload();
        }

        // Wait for reindex job to complete
        async function waitForReindexComplete() {
            for (let i = 0; i < 60; i++) { // Max 60 attempts (3 min)
                await new Promise(resolve => setTimeout(resolve, 3000));
                try {
                    const response = await fetch('/admin/knowledge-base/jobs/active', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!data.has_active_jobs) {
                        console.log('Reindex complete!');
                        return;
                    }
                } catch (e) {
                    console.error('Wait error:', e);
                }
            }
        }

        // Start polling on page load if there are active jobs
        document.addEventListener('DOMContentLoaded', pollForCompletion);

        async function processDocument(id) {
            const statusEl = document.getElementById(`status-${id}`);
            const btn = document.querySelector(`button[data-id="${id}"]`);

            // Update UI to processing
            statusEl.className = 'status-badge status-processing';
            statusEl.innerHTML = '⏳ Memproses';
            btn.disabled = true;
            btn.innerHTML = '⏳';

            try {
                const response = await fetch(`/admin/upload/${id}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Always show processing and start polling
                    // Status will be updated when page refreshes after all jobs complete
                    statusEl.innerHTML = '⏳ Memproses';

                    // Start page-level polling if not already running
                    if (!isPollingForCompletion) {
                        isPollingForCompletion = true;
                        pollForCompletion();
                    }
                } else {
                    statusEl.className = 'status-badge status-error';
                    statusEl.innerHTML = '❌ Error';
                    btn.innerHTML = '⚡';
                    btn.disabled = false;
                    showConfirmModal('Proses Gagal', data.message, function () { });
                }
            } catch (error) {
                statusEl.className = 'status-badge status-error';
                statusEl.innerHTML = '❌ Error';
                btn.innerHTML = '⚡';
                btn.disabled = false;
                showConfirmModal('Proses Gagal', 'Gagal memproses: ' + error.message, function () { });
            }
        }

        // AJAX Delete Document
        function deleteDocument(id, filename) {
            showConfirmModal('Hapus Dokumen', `Hapus dokumen "${filename}"?`, async function () {
                try {
                    const response = await fetch(`/admin/upload/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    // Reload halaman setelah hapus
                    window.location.reload();
                } catch (error) {
                    showConfirmModal('Error', 'Gagal menghapus: ' + error.message, function () { });
                }
            });
        }
    </script>

    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
            animation: pulse 1.5s infinite;
        }

        .status-done {
            background: #d1fae5;
            color: #065f46;
        }

        .status-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .uploading-dots::after {
            content: '';
            animation: dots 1.5s infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '.';
            }

            40% {
                content: '..';
            }

            60%,
            100% {
                content: '...';
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        /* Drag and Drop visual feedback */
        .upload-box.drag-over {
            border-color: #0ea5e9;
            background: rgba(14, 165, 233, 0.15);
            transform: scale(1.02);
        }

        .upload-box.drag-over .upload-icon {
            transform: scale(1.2);
        }

        .upload-box {
            transition: all 0.3s ease;
        }

        .upload-icon {
            transition: transform 0.3s ease;
        }
    </style>
@endsection
