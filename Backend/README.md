# OCR Vision - AI-Powered Text Extraction

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Ollama-LLaVA-green?style=for-the-badge" alt="Ollama">
</p>

A Laravel-based OCR service that extracts text from images using AI-powered vision models (LLaVA/Ollama). Features JWT authentication, rate limiting, cloud storage support, and a beautiful demo interface.

## ✨ Features

- 🔍 **AI-Powered OCR** - Uses LLaVA vision model via Ollama for accurate text extraction
- 🔐 **JWT Authentication** - Secure API access with token-based auth
- ⚡ **Smart Caching** - File hash-based caching to avoid duplicate processing
- 🚦 **Rate Limiting** - Protect demo endpoints from abuse
- ☁️ **Cloud Storage** - Support for S3/Cloudflare R2 storage
- 🎨 **Beautiful UI** - Modern demo page with drag & drop upload
- 📖 **API Documentation** - Comprehensive interactive docs

## 🚀 Quick Setup

### Prerequisites

- PHP 8.2+
- Composer
- SQLite or MySQL/PostgreSQL
- [Ollama](https://ollama.ai/) with LLaVA model

### 1. Clone the Repository

```bash
git clone https://github.com/AhmedITD/OCR.git
cd OCR
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configure Environment

Edit `.env` file with your settings:

```env
# Database (SQLite is default)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Ollama Configuration
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llava
OLLAMA_TIMEOUT=300

# Storage (optional - for cloud storage)
FILESYSTEM_DISK=local
# For S3/R2:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=auto
# AWS_BUCKET=your_bucket
# AWS_ENDPOINT=https://your-endpoint.r2.cloudflarestorage.com
```

### 5. Database Setup

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 6. Install Ollama & LLaVA Model

```bash
# Install Ollama (Linux/macOS)
curl -fsSL https://ollama.ai/install.sh | sh

# Pull LLaVA model
ollama pull llava

# Start Ollama server
ollama serve
```

### 7. Start the Application

```bash
php artisan serve
```

Visit:
- **Demo Page**: http://localhost:8000/demo
- **API Docs**: http://localhost:8000/docs

## 📡 API Endpoints

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ocr/status` | Check service status |
| `GET` | `/api/ocr/rate-limit` | Check rate limit status |
| `POST` | `/api/ocr/demo/upload` | Upload image (rate limited) |
| `POST` | `/api/auth/register` | Register new user |
| `POST` | `/api/auth/login` | Login & get token |

### Protected Endpoints (Require JWT)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/auth/me` | Get current user |
| `POST` | `/api/auth/refresh` | Refresh token |
| `POST` | `/api/auth/logout` | Logout |
| `POST` | `/api/ocr/upload` | Upload image for OCR |
| `GET` | `/api/ocr/files/{id}` | Get OCR result |
| `GET` | `/api/ocr/history` | Get upload history |

### Example: Upload Image

```bash
# Demo endpoint (no auth required)
curl -X POST http://localhost:8000/api/ocr/demo/upload \
  -F "image=@/path/to/image.png"

# Authenticated endpoint
curl -X POST http://localhost:8000/api/ocr/upload \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "image=@/path/to/image.png"
```

### Example Response

```json
{
  "success": true,
  "message": "Text extracted successfully",
  "data": {
    "file_id": 42,
    "original_filename": "document.png",
    "extracted_text": "Hello World\nThis is extracted text...",
    "from_cache": false,
    "processed_at": "2025-01-15T10:30:00.000000Z",
    "processing_time_ms": 2450
  },
  "rate_limit": {
    "remaining_attempts": 4
  }
}
```

## 🛠️ Configuration Options

### Ollama Models

You can use different vision models:

```env
# Default (recommended)
OLLAMA_MODEL=llava

# Smaller/faster alternative
OLLAMA_MODEL=moondream

# Other options: bakllava, llava-llama3, etc.
```

### Storage Options

```env
# Local storage (default)
FILESYSTEM_DISK=local

# AWS S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket

# Cloudflare R2
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_r2_key
AWS_SECRET_ACCESS_KEY=your_r2_secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket
AWS_ENDPOINT=https://account-id.r2.cloudflarestorage.com
```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── AuthController.php    # Authentication
│   │   └── OcrController.php     # OCR endpoints
│   ├── Models/
│   │   ├── OcrFile.php          # OCR file records
│   │   ├── RateLimit.php        # Rate limiting
│   │   └── UploadLog.php        # Upload history
│   └── Services/
│       ├── OcrService.php       # OCR processing logic
│       └── OllamaService.php    # Ollama API client
├── resources/views/
│   ├── demo.blade.php           # Demo upload page
│   └── docs.blade.php           # API documentation
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
└── database/
    └── migrations/              # Database schema
```

## 🔒 Security

- JWT tokens expire after 60 minutes (configurable)
- Rate limiting: 5 uploads per hour per IP (demo)
- File validation: Only images (JPEG, PNG, GIF, WebP)
- Max file size: 10MB
- Sensitive data excluded from git (.env, logs, etc.)

## 📝 License

MIT License - feel free to use this project for any purpose.

## 🙏 Credits

- [Laravel](https://laravel.com/) - PHP Framework
- [Ollama](https://ollama.ai/) - Local AI model runner
- [LLaVA](https://llava-vl.github.io/) - Vision-Language Model
- [JWT-Auth](https://github.com/tymondesigns/jwt-auth) - JWT Authentication
