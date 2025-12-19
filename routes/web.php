<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| File ini mendefinisikan semua route untuk aplikasi web
|--------------------------------------------------------------------------
*/

// Redirect root berdasarkan status login
Route::get('/', function () {
    if (auth()->check()) {
        // User sudah login - redirect berdasarkan role
        return auth()->user()->role === 'admin'
            ? redirect('/admin/dashboard')
            : redirect('/chat');
    }
    // Guest - redirect ke login
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Route Tamu (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Route Terautentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pengaturan Akun (untuk semua user terautentikasi)
    Route::get('/account', [AuthController::class, 'getAccount'])->name('account.get');
    Route::post('/account', [AuthController::class, 'updateAccount'])->name('account.update');

    /*
    |--------------------------------------------------------------------------
    | Route User
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user')->group(function () {
        // Halaman chat
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{id}', [ChatController::class, 'loadChat'])->name('chat.load');
        Route::post('/chat/new', [ChatController::class, 'newChat'])->name('chat.new');
        Route::post('/chat/{chat_id}/send', [ChatController::class, 'send'])->name('chat.send');

        // Aksi menu chat
        Route::post('/chat/{id}/pin', [ChatController::class, 'pin'])->name('chat.pin');
        Route::post('/chat/{id}/rename', [ChatController::class, 'rename'])->name('chat.rename');
        Route::delete('/chat/{id}/delete', [ChatController::class, 'delete'])->name('chat.delete');
    });

    // Download dokumen (bisa diakses admin dan user)
    Route::get('/dokumen/{id}/download', [AdminUploadController::class, 'download'])->name('dokumen.download');

    /*
    |--------------------------------------------------------------------------
    | Route Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Manajemen User
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('index');
            Route::get('/create', [AdminController::class, 'createUser'])->name('create');
            Route::post('/', [AdminController::class, 'storeUser'])->name('store');
            Route::get('/{id}/edit', [AdminController::class, 'editUser'])->name('edit');
            Route::put('/{id}', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'deleteUser'])->name('destroy');
        });

        // Manajemen Upload
        Route::prefix('upload')->name('upload.')->group(function () {
            Route::get('/', [AdminUploadController::class, 'index'])->name('index');
            Route::post('/', [AdminUploadController::class, 'store'])->name('save');
            Route::delete('/bulk-delete', [AdminUploadController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('/sync', [AdminUploadController::class, 'sync'])->name('sync');
            Route::post('/process-all', [AdminUploadController::class, 'processAll'])->name('processAll');
            Route::post('/{id}/process', [AdminUploadController::class, 'processSingle'])->name('process');
            Route::get('/status/{id}', [AdminUploadController::class, 'checkStatus'])->name('status');
            Route::delete('/{id}', [AdminUploadController::class, 'destroy'])->name('delete');
        });

        // Knowledge Base (data yang sudah diproses)
        Route::get('/knowledge-base', [AdminController::class, 'knowledgeBase'])->name('kb.index');
        Route::post('/knowledge-base/clear', [AdminController::class, 'clearKnowledgeBase'])->name('knowledge-base.clear');
        Route::post('/knowledge-base/reindex', [AdminController::class, 'reindexKnowledgeBase'])->name('knowledge-base.reindex');
        Route::get('/knowledge-base/status', [AdminController::class, 'kbStatus'])->name('knowledge-base.status');
        Route::get('/knowledge-base/jobs/active', [AdminController::class, 'activeJobs'])->name('knowledge-base.jobs.active');

        // Profil Admin
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
});
