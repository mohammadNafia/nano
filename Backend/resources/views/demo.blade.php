<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OCR Vision - Extract Text from Images</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #12121a;
            --bg-tertiary: #1a1a25;
            --bg-card: #16161f;
            --border-color: #2a2a3a;
            --border-accent: #3d3d5c;
            --text-primary: #f0f0f5;
            --text-secondary: #a0a0b5;
            --text-muted: #6a6a80;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --glow-primary: rgba(99, 102, 241, 0.15);
            --glow-secondary: rgba(139, 92, 246, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, var(--glow-primary) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, var(--glow-secondary) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(168, 85, 247, 0.05) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .grid-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(99, 102, 241, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Header */
        header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: var(--accent-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px var(--glow-primary);
        }

        .logo-icon svg {
            width: 28px;
            height: 28px;
            stroke: white;
            fill: none;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .tagline {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
            font-weight: 300;
        }

        /* Status bar */
        .status-bar {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            font-size: 0.85rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-dot.online { background: var(--success); }
        .status-dot.offline { background: var(--error); }
        .status-dot.warning { background: var(--warning); }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Main grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 900px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-primary), transparent);
            opacity: 0.5;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title svg {
            width: 20px;
            height: 20px;
            stroke: var(--accent-primary);
        }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed var(--border-accent);
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(99, 102, 241, 0.02);
        }

        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--accent-primary);
            background: rgba(99, 102, 241, 0.05);
            transform: translateY(-2px);
        }

        .upload-zone.processing {
            pointer-events: none;
            opacity: 0.7;
        }

        .upload-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            stroke: var(--text-muted);
            transition: stroke 0.3s ease;
        }

        .upload-zone:hover .upload-icon {
            stroke: var(--accent-primary);
        }

        .upload-text {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .file-input {
            display: none;
        }

        /* Preview section */
        .preview-section {
            margin-top: 1.5rem;
            display: none;
        }

        .preview-section.visible {
            display: block;
        }

        .preview-image {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: var(--bg-tertiary);
        }

        .file-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .file-name {
            color: var(--text-primary);
            font-weight: 500;
        }

        .file-size {
            color: var(--text-muted);
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            font-size: 0.95rem;
            font-weight: 500;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 15px var(--glow-primary);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--glow-primary);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        .extract-btn {
            width: 100%;
            margin-top: 1rem;
        }

        /* Result section */
        .result-section {
            display: none;
        }

        .result-section.visible {
            display: block;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .result-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .result-badge.cached {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .result-badge.fresh {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent-primary);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .result-text {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 300px;
            overflow-y: auto;
            color: var(--text-secondary);
        }

        .result-text::-webkit-scrollbar {
            width: 6px;
        }

        .result-text::-webkit-scrollbar-track {
            background: var(--bg-secondary);
            border-radius: 3px;
        }

        .result-text::-webkit-scrollbar-thumb {
            background: var(--border-accent);
            border-radius: 3px;
        }

        .copy-btn {
            padding: 0.5rem 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .copy-btn:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        /* Processing indicator */
        .processing-indicator {
            display: none;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 10px;
            margin-top: 1rem;
        }

        .processing-indicator.visible {
            display: flex;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-accent);
            border-top-color: var(--accent-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .processing-text {
            color: var(--accent-primary);
            font-size: 0.9rem;
        }

        /* History section */
        .history-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .history-item:hover {
            border-color: var(--border-accent);
        }

        .history-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .history-filename {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
        }

        .history-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .history-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .history-status.completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .history-status.cached {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-secondary);
        }

        .history-status.failed {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .empty-history {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        /* Rate limit bar */
        .rate-limit-section {
            margin-bottom: 2rem;
        }

        .rate-limit-bar {
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.75rem;
        }

        .rate-limit-fill {
            height: 100%;
            background: var(--accent-gradient);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .rate-limit-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* Error message */
        .error-message {
            display: none;
            padding: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            color: var(--error);
            margin-top: 1rem;
        }

        .error-message.visible {
            display: block;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border-color);
        }

        footer a {
            color: var(--accent-primary);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* API docs link */
        .api-docs-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            margin-top: 1.5rem;
        }

        .api-docs-link:hover {
            border-color: var(--accent-primary);
            color: var(--accent-primary);
        }

        .api-docs-link svg {
            width: 16px;
            height: 16px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeIn 0.4s ease forwards;
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="grid-pattern"></div>

    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <h1>OCR Vision</h1>
            </div>
            <p class="tagline">Extract text from images using AI-powered LLaVA vision model</p>
        </header>

        <div class="status-bar">
            <div class="status-item">
                <span class="status-dot {{ $serviceStatus['service_available'] ? 'online' : 'offline' }}"></span>
                <span>Ollama: {{ $serviceStatus['service_available'] ? 'Online' : 'Offline' }}</span>
            </div>
            <div class="status-item">
                <span class="status-dot {{ $serviceStatus['model_available'] ? 'online' : ($serviceStatus['service_available'] ? 'warning' : 'offline') }}"></span>
                <span>{{ $serviceStatus['model_name'] }}: {{ $serviceStatus['model_available'] ? 'Ready' : 'Not loaded' }}</span>
            </div>
            <div class="status-item">
                <span class="status-dot {{ $rateLimit['remaining'] > 0 ? 'online' : 'warning' }}"></span>
                <span>Uploads remaining: {{ $rateLimit['remaining'] }}</span>
            </div>
        </div>

        <div class="main-grid">
            <!-- Upload Card -->
            <div class="card animate-in">
                <h2 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Upload Image
                </h2>

                <div class="rate-limit-section">
                    <div class="rate-limit-info">
                        <span>Demo uploads</span>
                        <span id="rateLimitCount">{{ $rateLimit['remaining'] }} of 5 remaining</span>
                    </div>
                    <div class="rate-limit-bar">
                        <div class="rate-limit-fill" style="width: {{ ($rateLimit['remaining'] / 5) * 100 }}%" id="rateLimitBar"></div>
                    </div>
                </div>

                <div class="upload-zone" id="uploadZone">
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                        <path d="M12 12v9"/>
                        <path d="m16 16-4-4-4 4"/>
                    </svg>
                    <p class="upload-text">Drop your image here or click to browse</p>
                    <p class="upload-hint">Supports JPEG, PNG, GIF, WebP (max 10MB)</p>
                    <input type="file" class="file-input" id="fileInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                </div>

                <div class="preview-section" id="previewSection">
                    <img class="preview-image" id="previewImage" alt="Preview">
                    <div class="file-info">
                        <span class="file-name" id="fileName"></span>
                        <span class="file-size" id="fileSize"></span>
                    </div>
                </div>

                <div class="processing-indicator" id="processingIndicator">
                    <div class="spinner"></div>
                    <span class="processing-text">Extracting text with LLaVA...</span>
                </div>

                <div class="error-message" id="errorMessage"></div>

                <button class="btn btn-primary extract-btn" id="extractBtn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    Extract Text
                </button>
            </div>

            <!-- Results Card -->
            <div class="card animate-in" style="animation-delay: 0.1s">
                <h2 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    Extracted Text
                </h2>

                <div class="result-section" id="resultSection">
                    <div class="result-header">
                        <span class="result-badge" id="resultBadge">
                            <span id="badgeText">Processed</span>
                        </span>
                        <button class="btn copy-btn" id="copyBtn" onclick="copyResult()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <div class="result-text" id="resultText"></div>
                </div>

                <div id="emptyResult" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <p>Upload an image to extract text</p>
                </div>
            </div>
        </div>

        <!-- History Card -->
        <div class="card animate-in" style="animation-delay: 0.2s">
            <h2 class="card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Recent Uploads
            </h2>

            <div class="history-list" id="historyList">
                @if($history->isEmpty())
                    <div class="empty-history">
                        <p>No uploads yet. Upload an image to get started!</p>
                    </div>
                @else
                    @foreach($history as $item)
                        <div class="history-item">
                            <div class="history-info">
                                <span class="history-filename">{{ Str::limit($item->original_filename, 40) }}</span>
                                <span class="history-meta">
                                    {{ $item->created_at->diffForHumans() }}
                                    @if($item->processing_time_ms)
                                        • {{ $item->processing_time_ms }}ms
                                    @endif
                                </span>
                            </div>
                            <span class="history-status {{ $item->status }}">
                                @if($item->status === 'cached')
                                    From Cache
                                @elseif($item->status === 'completed')
                                    Completed
                                @elseif($item->status === 'failed')
                                    Failed
                                @else
                                    {{ ucfirst($item->status) }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>

            <a href="/docs" class="api-docs-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                View API Documentation
            </a>
        </div>

        <footer>
            <p>Powered by <a href="https://ollama.ai" target="_blank">Ollama</a> & <a href="https://llava-vl.github.io/" target="_blank">LLaVA</a> Vision Model</p>
            <p style="margin-top: 0.5rem;">Built with Laravel • Rate limited to 5 uploads per hour per IP</p>
        </footer>
    </div>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const previewSection = document.getElementById('previewSection');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const extractBtn = document.getElementById('extractBtn');
        const processingIndicator = document.getElementById('processingIndicator');
        const errorMessage = document.getElementById('errorMessage');
        const resultSection = document.getElementById('resultSection');
        const resultText = document.getElementById('resultText');
        const resultBadge = document.getElementById('resultBadge');
        const badgeText = document.getElementById('badgeText');
        const emptyResult = document.getElementById('emptyResult');
        const rateLimitCount = document.getElementById('rateLimitCount');
        const rateLimitBar = document.getElementById('rateLimitBar');

        let selectedFile = null;

        // Click to upload
        uploadZone.addEventListener('click', () => fileInput.click());

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        function handleFile(file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showError('Please upload a valid image file (JPEG, PNG, GIF, or WebP)');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showError('File size must be less than 10MB');
                return;
            }

            selectedFile = file;
            hideError();

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewSection.classList.add('visible');
            };
            reader.readAsDataURL(file);

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            extractBtn.disabled = false;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.add('visible');
        }

        function hideError() {
            errorMessage.classList.remove('visible');
        }

        // Extract button click
        extractBtn.addEventListener('click', async () => {
            if (!selectedFile) return;

            extractBtn.disabled = true;
            uploadZone.classList.add('processing');
            processingIndicator.classList.add('visible');
            hideError();

            const formData = new FormData();
            formData.append('image', selectedFile);

            try {
                const response = await fetch('/api/ocr/demo/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Show result
                    resultText.textContent = data.data.extracted_text || 'No text found in image.';
                    
                    // Update badge
                    if (data.data.from_cache) {
                        resultBadge.className = 'result-badge cached';
                        badgeText.textContent = '⚡ From Cache';
                    } else {
                        resultBadge.className = 'result-badge fresh';
                        badgeText.textContent = '✓ Just Processed';
                    }

                    resultSection.classList.add('visible');
                    emptyResult.style.display = 'none';

                    // Update rate limit
                    if (data.rate_limit) {
                        updateRateLimit(data.rate_limit.remaining_attempts);
                    }

                    // Add to history without reloading
                    addToHistory({
                        filename: data.data.original_filename,
                        status: data.data.from_cache ? 'cached' : 'completed',
                        processing_time_ms: data.data.processing_time_ms,
                    });
                } else {
                    showError(data.message || 'Failed to extract text from image');
                    
                    if (response.status === 429) {
                        updateRateLimit(0);
                    }
                }
            } catch (error) {
                showError('Network error. Please try again.');
                console.error('Error:', error);
            } finally {
                extractBtn.disabled = false;
                uploadZone.classList.remove('processing');
                processingIndicator.classList.remove('visible');
            }
        });

        function updateRateLimit(remaining) {
            rateLimitCount.textContent = `${remaining} of 5 remaining`;
            rateLimitBar.style.width = `${(remaining / 5) * 100}%`;
            
            if (remaining === 0) {
                extractBtn.disabled = true;
            }
        }

        function addToHistory(item) {
            const historyList = document.getElementById('historyList');
            
            // Remove empty history message if present
            const emptyHistory = historyList.querySelector('.empty-history');
            if (emptyHistory) {
                emptyHistory.remove();
            }

            // Create new history item
            const historyItem = document.createElement('div');
            historyItem.className = 'history-item animate-in';
            
            const statusClass = item.status === 'cached' ? 'cached' : 'completed';
            const statusText = item.status === 'cached' ? 'From Cache' : 'Completed';
            const timeText = item.processing_time_ms ? ` • ${item.processing_time_ms}ms` : '';
            
            historyItem.innerHTML = `
                <div class="history-info">
                    <span class="history-filename">${item.filename.length > 40 ? item.filename.substring(0, 40) + '...' : item.filename}</span>
                    <span class="history-meta">Just now${timeText}</span>
                </div>
                <span class="history-status ${statusClass}">${statusText}</span>
            `;

            // Insert at the top of the list
            historyList.insertBefore(historyItem, historyList.firstChild);

            // Remove oldest item if more than 10
            const items = historyList.querySelectorAll('.history-item');
            if (items.length > 10) {
                items[items.length - 1].remove();
            }
        }

        function copyResult() {
            const text = resultText.textContent;
            navigator.clipboard.writeText(text).then(() => {
                const copyBtn = document.getElementById('copyBtn');
                copyBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Copied!
                `;
                setTimeout(() => {
                    copyBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        Copy
                    `;
                }, 2000);
            });
        }
    </script>
</body>
</html>

