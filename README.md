# RAG Web

Laravel frontend untuk sistem RAG Akademik Universitas Mercu Buana.

## 📋 Deskripsi

Repository ini menyediakan antarmuka web untuk:
- **Chat dengan Bot**: Tanya-jawab berbasis RAG
- **Riwayat Chat**: Lihat percakapan sebelumnya dengan referensi yang tersimpan
- **Download Sumber**: Unduh dokumen referensi langsung dari chat
- **Admin Dashboard**: Kelola dokumen dan knowledge base
- **Autentikasi**: Login dengan role-based access

## 📁 Struktur Direktori

```
rag-web/
├── app/
│   ├── Http/Controllers/
│   │   ├── ChatController.php
│   │   ├── AdminUploadController.php
│   │   └── AdminKnowledgeBaseController.php
│   ├── Models/
│   └── Services/
│       └── RagApiService.php
├── resources/views/
│   ├── chat/
│   ├── admin/
│   └── auth/
├── routes/
├── database/migrations/
├── .env.example
└── README.md
```

## ⚙️ Instalasi

```bash
# Clone repository
git clone https://github.com/JovanAditya/rag-web.git
cd rag-web

# Install dependencies
composer install
npm install

# Konfigurasi
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate

# Build assets
npm run build
```

## 🔧 Konfigurasi

Edit file `.env`:

```env
APP_NAME="RAG Akademik"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=rag_akademik

RAG_API_URL=http://localhost:5001/api
```

## 🚀 Menjalankan Aplikasi

```bash
# Development
php artisan serve
npm run dev

# Akses di http://localhost:8000
```

## 👥 User Roles

| Role | Akses |
|------|-------|
| **Mahasiswa** | Chat, Riwayat, Download Dokumen, Profil |
| **Admin** | Semua + Kelola Dokumen, KB, Users |

## 🔗 Integrasi RAG API

Konfigurasi di `config/services.php`:

```php
'rag' => [
    'url' => env('RAG_API_URL', 'http://localhost:5001/api'),
    'timeout' => env('RAG_API_TIMEOUT', 60),
],
```

## 📦 Dependencies

| Package | Deskripsi |
|---------|-----------|
| Laravel 10+ | PHP Framework |
| Tailwind CSS | Styling |
| Axios | HTTP Client |
| MySQL 8.0 | Database |

## 🔗 Repository Terkait

| Repository | Deskripsi |
|------------|-----------|
| [rag-model](https://github.com/JovanAditya/rag-model) | Core RAG Model |
| [rag-api](https://github.com/JovanAditya/rag-api) | REST API |
| [rag-deploy](https://github.com/JovanAditya/rag-deploy) | Docker Orchestration |

## 📄 Lisensi

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

*Bagian dari proyek skripsi Sistem RAG Akademik - Universitas Mercu Buana*
