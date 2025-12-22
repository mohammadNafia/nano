<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - OCR Service</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #12121a;
            --bg-tertiary: #1a1a25;
            --bg-code: #0d0d14;
            --text-primary: #e4e4e7;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --accent-cyan: #22d3ee;
            --accent-emerald: #34d399;
            --accent-amber: #fbbf24;
            --accent-rose: #f43f5e;
            --accent-violet: #a78bfa;
            --accent-blue: #60a5fa;
            --border-color: #27272a;
            --method-get: #34d399;
            --method-post: #60a5fa;
            --method-delete: #f43f5e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.7;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header */
        header {
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34, 211, 238, 0.1);
            border: 1px solid rgba(34, 211, 238, 0.2);
            color: var(--accent-cyan);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent-cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            max-width: 600px;
        }

        /* Navigation */
        nav {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .nav-content {
            display: flex;
            gap: 8px;
            padding: 16px 0;
            overflow-x: auto;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: var(--text-primary);
            background: var(--bg-tertiary);
        }

        .nav-link.active {
            color: var(--accent-cyan);
            background: rgba(34, 211, 238, 0.1);
        }

        /* Main Content */
        main {
            padding: 60px 0;
        }

        section {
            margin-bottom: 80px;
        }

        .section-header {
            margin-bottom: 32px;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h2 .icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-tertiary);
            border-radius: 8px;
            font-size: 18px;
        }

        .section-desc {
            color: var(--text-secondary);
            font-size: 15px;
        }

        /* Endpoint Cards */
        .endpoint {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            margin-bottom: 24px;
            overflow: hidden;
            transition: border-color 0.2s;
        }

        .endpoint:hover {
            border-color: var(--accent-cyan);
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            cursor: pointer;
            user-select: none;
        }

        .method {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .method-get {
            background: rgba(52, 211, 153, 0.15);
            color: var(--method-get);
        }

        .method-post {
            background: rgba(96, 165, 250, 0.15);
            color: var(--method-post);
        }

        .method-delete {
            background: rgba(244, 63, 94, 0.15);
            color: var(--method-delete);
        }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            color: var(--text-primary);
            flex: 1;
        }

        .auth-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .auth-public {
            background: rgba(52, 211, 153, 0.15);
            color: var(--accent-emerald);
        }

        .auth-protected {
            background: rgba(251, 191, 36, 0.15);
            color: var(--accent-amber);
        }

        .endpoint-toggle {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: transform 0.2s;
        }

        .endpoint.open .endpoint-toggle {
            transform: rotate(180deg);
        }

        .endpoint-body {
            display: none;
            padding: 0 24px 24px;
            border-top: 1px solid var(--border-color);
        }

        .endpoint.open .endpoint-body {
            display: block;
        }

        .endpoint-desc {
            color: var(--text-secondary);
            font-size: 14px;
            padding: 20px 0;
        }

        /* Parameters & Response */
        .params-section {
            margin-top: 20px;
        }

        .params-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .params-table {
            width: 100%;
            border-collapse: collapse;
        }

        .params-table th,
        .params-table td {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .params-table th {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg-tertiary);
        }

        .params-table td:first-child {
            font-family: 'JetBrains Mono', monospace;
            color: var(--accent-cyan);
        }

        .param-type {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--accent-violet);
        }

        .param-required {
            color: var(--accent-rose);
            font-size: 11px;
            font-weight: 600;
        }

        .param-optional {
            color: var(--text-muted);
            font-size: 11px;
        }

        /* Code Blocks */
        .code-block {
            background: var(--bg-code);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-top: 16px;
            overflow: hidden;
        }

        .code-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
        }

        .code-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .copy-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            color: var(--text-primary);
            background: var(--bg-secondary);
        }

        pre {
            padding: 16px;
            overflow-x: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.6;
        }

        code {
            font-family: 'JetBrains Mono', monospace;
        }

        .json-key {
            color: var(--accent-cyan);
        }

        .json-string {
            color: var(--accent-emerald);
        }

        .json-number {
            color: var(--accent-amber);
        }

        .json-boolean {
            color: var(--accent-violet);
        }

        .json-null {
            color: var(--accent-rose);
        }

        /* Base URL Box */
        .base-url-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .base-url-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .base-url-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            color: var(--accent-cyan);
            background: var(--bg-code);
            padding: 8px 16px;
            border-radius: 8px;
            flex: 1;
        }

        /* Status Codes */
        .status-codes {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .status-code {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .status-number {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .status-2xx {
            background: rgba(52, 211, 153, 0.15);
            color: var(--accent-emerald);
        }

        .status-4xx {
            background: rgba(251, 191, 36, 0.15);
            color: var(--accent-amber);
        }

        .status-5xx {
            background: rgba(244, 63, 94, 0.15);
            color: var(--accent-rose);
        }

        /* Footer */
        footer {
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
            padding: 40px 0;
            text-align: center;
        }

        footer p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            h1 {
                font-size: 32px;
            }

            .endpoint-header {
                flex-wrap: wrap;
                gap: 12px;
            }

            .endpoint-path {
                order: 3;
                width: 100%;
            }

            .params-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                REST API v1.0
            </div>
            <h1>OCR Service API</h1>
            <p class="subtitle">Extract text from images using AI-powered optical character recognition. Fast, accurate, and easy to integrate.</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <div class="nav-content">
                <a href="#overview" class="nav-link active">Overview</a>
                <a href="#authentication" class="nav-link">Authentication</a>
                <a href="#ocr" class="nav-link">OCR Endpoints</a>
                <a href="#errors" class="nav-link">Error Handling</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <!-- Base URL -->
            <div class="base-url-box">
                <span class="base-url-label">Base URL</span>
                <span class="base-url-value">{{ url('/api') }}</span>
            </div>

            <!-- Overview Section -->
            <section id="overview">
                <div class="section-header">
                    <h2><span class="icon">📖</span> Overview</h2>
                    <p class="section-desc">The OCR Service API provides endpoints for extracting text from images using advanced AI models.</p>
                </div>

                <div class="endpoint open">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/ocr/status</span>
                        <span class="auth-badge auth-public">Public</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Check the OCR service health and availability status.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                    Copy
                                </button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"service_available"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"model_available"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"model_name"</span>: <span class="json-string">"llava"</span>,
    <span class="json-key">"base_url"</span>: <span class="json-string">"http://localhost:11434"</span>
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/ocr/rate-limit</span>
                        <span class="auth-badge auth-public">Public</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Check rate limit status for your current IP address.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"allowed"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"remaining_attempts"</span>: <span class="json-number">10</span>,
    <span class="json-key">"blocked_until"</span>: <span class="json-null">null</span>
  }
}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Authentication Section -->
            <section id="authentication">
                <div class="section-header">
                    <h2><span class="icon">🔐</span> Authentication</h2>
                    <p class="section-desc">JWT-based authentication for protected endpoints. Include the token in the Authorization header.</p>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/auth/register</span>
                        <span class="auth-badge auth-public">Public</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Register a new user account and receive a JWT token.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Request Body</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>name</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>User's full name (max 255 characters)</td>
                                    </tr>
                                    <tr>
                                        <td>email</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>Valid email address (must be unique)</td>
                                    </tr>
                                    <tr>
                                        <td>password</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>Password (min 6 characters)</td>
                                    </tr>
                                    <tr>
                                        <td>password_confirmation</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>Password confirmation (must match password)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response (201 Created)</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"User registered successfully"</span>,
  <span class="json-key">"user"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">1</span>,
    <span class="json-key">"name"</span>: <span class="json-string">"John Doe"</span>,
    <span class="json-key">"email"</span>: <span class="json-string">"john@example.com"</span>
  },
  <span class="json-key">"token"</span>: <span class="json-string">"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."</span>,
  <span class="json-key">"token_type"</span>: <span class="json-string">"bearer"</span>,
  <span class="json-key">"expires_in"</span>: <span class="json-number">3600</span>
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/auth/login</span>
                        <span class="auth-badge auth-public">Public</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Authenticate with email and password to receive a JWT token.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Request Body</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>email</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>Registered email address</td>
                                    </tr>
                                    <tr>
                                        <td>password</td>
                                        <td><span class="param-type">string</span> <span class="param-required">required</span></td>
                                        <td>Account password</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Login successful"</span>,
  <span class="json-key">"user"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">1</span>,
    <span class="json-key">"name"</span>: <span class="json-string">"John Doe"</span>,
    <span class="json-key">"email"</span>: <span class="json-string">"john@example.com"</span>
  },
  <span class="json-key">"token"</span>: <span class="json-string">"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."</span>,
  <span class="json-key">"token_type"</span>: <span class="json-string">"bearer"</span>,
  <span class="json-key">"expires_in"</span>: <span class="json-number">3600</span>
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/auth/me</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Get the authenticated user's profile information.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Headers</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Header</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Authorization</td>
                                        <td>Bearer {your_token}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"user"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">1</span>,
    <span class="json-key">"name"</span>: <span class="json-string">"John Doe"</span>,
    <span class="json-key">"email"</span>: <span class="json-string">"john@example.com"</span>,
    <span class="json-key">"created_at"</span>: <span class="json-string">"2025-01-15T10:30:00.000000Z"</span>
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/auth/refresh</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Refresh the JWT token before it expires.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"token"</span>: <span class="json-string">"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."</span>,
  <span class="json-key">"token_type"</span>: <span class="json-string">"bearer"</span>,
  <span class="json-key">"expires_in"</span>: <span class="json-number">3600</span>
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/auth/logout</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Invalidate the current JWT token and log out.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Successfully logged out"</span>
}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- OCR Endpoints Section -->
            <section id="ocr">
                <div class="section-header">
                    <h2><span class="icon">🔍</span> OCR Endpoints</h2>
                    <p class="section-desc">Upload images and extract text using AI-powered OCR. Supports JPEG, PNG, GIF, and WebP formats.</p>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/ocr/demo/upload</span>
                        <span class="auth-badge auth-public">Public (Rate Limited)</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Upload an image for OCR text extraction. This demo endpoint is rate-limited by IP address.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Request Body (multipart/form-data)</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>image</td>
                                        <td><span class="param-type">file</span> <span class="param-required">required</span></td>
                                        <td>Image file (jpeg, jpg, png, gif, webp). Max 10MB.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">cURL Example</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>curl -X POST {{ url('/api/ocr/demo/upload') }} \
  -F "image=@/path/to/image.png"</code></pre>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Text extracted successfully"</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"file_id"</span>: <span class="json-number">42</span>,
    <span class="json-key">"original_filename"</span>: <span class="json-string">"document.png"</span>,
    <span class="json-key">"extracted_text"</span>: <span class="json-string">"Hello World\nThis is extracted text..."</span>,
    <span class="json-key">"from_cache"</span>: <span class="json-boolean">false</span>,
    <span class="json-key">"processed_at"</span>: <span class="json-string">"2025-01-15T10:30:00.000000Z"</span>,
    <span class="json-key">"processing_time_ms"</span>: <span class="json-number">2450</span>
  },
  <span class="json-key">"rate_limit"</span>: {
    <span class="json-key">"remaining_attempts"</span>: <span class="json-number">9</span>
  }
}</code></pre>
                        </div>

                        <div class="status-codes">
                            <span class="status-code"><span class="status-number status-2xx">200</span> Success</span>
                            <span class="status-code"><span class="status-number status-4xx">422</span> Validation Error</span>
                            <span class="status-code"><span class="status-number status-4xx">429</span> Rate Limited</span>
                            <span class="status-code"><span class="status-number status-5xx">500</span> Processing Error</span>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/ocr/upload</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Upload an image for OCR text extraction. Requires authentication. Results are associated with your user account.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Headers</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Header</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Authorization</td>
                                        <td>Bearer {your_token}</td>
                                    </tr>
                                    <tr>
                                        <td>Content-Type</td>
                                        <td>multipart/form-data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="params-section">
                            <h4 class="params-title">Request Body</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>image</td>
                                        <td><span class="param-type">file</span> <span class="param-required">required</span></td>
                                        <td>Image file (jpeg, jpg, png, gif, webp). Max 10MB.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">JavaScript Example</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>const formData = new FormData();
formData.append('image', fileInput.files[0]);

const response = await fetch('{{ url('/api/ocr/upload') }}', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
});

const result = await response.json();</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/ocr/files/{id}</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Retrieve OCR result by file ID.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">URL Parameters</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>id</td>
                                        <td><span class="param-type">integer</span> <span class="param-required">required</span></td>
                                        <td>The file ID returned from upload</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">42</span>,
    <span class="json-key">"original_filename"</span>: <span class="json-string">"document.png"</span>,
    <span class="json-key">"extracted_text"</span>: <span class="json-string">"Hello World\nThis is extracted text..."</span>,
    <span class="json-key">"ocr_processed"</span>: <span class="json-boolean">true</span>,
    <span class="json-key">"processed_at"</span>: <span class="json-string">"2025-01-15T10:30:00.000000Z"</span>,
    <span class="json-key">"created_at"</span>: <span class="json-string">"2025-01-15T10:29:55.000000Z"</span>
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header" onclick="toggleEndpoint(this)">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/ocr/history</span>
                        <span class="auth-badge auth-protected">Protected</span>
                        <span class="endpoint-toggle">▼</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="endpoint-desc">Get your OCR upload history.</p>
                        
                        <div class="params-section">
                            <h4 class="params-title">Query Parameters</h4>
                            <table class="params-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>limit</td>
                                        <td><span class="param-type">integer</span> <span class="param-optional">optional</span></td>
                                        <td>Number of records to return (default: 10, max: 50)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Response</span>
                                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            </div>
                            <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">true</span>,
  <span class="json-key">"data"</span>: [
    {
      <span class="json-key">"id"</span>: <span class="json-number">42</span>,
      <span class="json-key">"filename"</span>: <span class="json-string">"document.png"</span>,
      <span class="json-key">"status"</span>: <span class="json-string">"completed"</span>,
      <span class="json-key">"from_cache"</span>: <span class="json-boolean">false</span>,
      <span class="json-key">"processing_time_ms"</span>: <span class="json-number">2450</span>,
      <span class="json-key">"extracted_text"</span>: <span class="json-string">"Hello World..."</span>,
      <span class="json-key">"created_at"</span>: <span class="json-string">"2025-01-15T10:30:00.000000Z"</span>
    }
  ]
}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Error Handling Section -->
            <section id="errors">
                <div class="section-header">
                    <h2><span class="icon">⚠️</span> Error Handling</h2>
                    <p class="section-desc">All errors return a consistent JSON structure with appropriate HTTP status codes.</p>
                </div>

                <div class="code-block">
                    <div class="code-header">
                        <span class="code-label">Error Response Format</span>
                    </div>
                    <pre><code>{
  <span class="json-key">"success"</span>: <span class="json-boolean">false</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Error description"</span>,
  <span class="json-key">"errors"</span>: {
    <span class="json-key">"field_name"</span>: [<span class="json-string">"Validation error message"</span>]
  }
}</code></pre>
                </div>

                <div class="params-section" style="margin-top: 32px;">
                    <h4 class="params-title">HTTP Status Codes</h4>
                    <table class="params-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="status-number status-2xx">200</span></td>
                                <td>OK</td>
                                <td>Request successful</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-2xx">201</span></td>
                                <td>Created</td>
                                <td>Resource created successfully</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-4xx">401</span></td>
                                <td>Unauthorized</td>
                                <td>Invalid or missing authentication token</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-4xx">404</span></td>
                                <td>Not Found</td>
                                <td>Resource not found</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-4xx">422</span></td>
                                <td>Unprocessable Entity</td>
                                <td>Validation errors in request</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-4xx">429</span></td>
                                <td>Too Many Requests</td>
                                <td>Rate limit exceeded</td>
                            </tr>
                            <tr>
                                <td><span class="status-number status-5xx">500</span></td>
                                <td>Internal Server Error</td>
                                <td>Server-side error during processing</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>OCR Service API Documentation • Built with Laravel</p>
        </div>
    </footer>

    <script>
        function toggleEndpoint(header) {
            const endpoint = header.parentElement;
            endpoint.classList.toggle('open');
        }

        function copyCode(button) {
            const codeBlock = button.closest('.code-block');
            const code = codeBlock.querySelector('code').innerText;
            
            navigator.clipboard.writeText(code).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '✓ Copied!';
                button.style.color = 'var(--accent-emerald)';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.color = '';
                }, 2000);
            });
        }

        // Smooth scroll for nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Update active state
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });

        // Update nav on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });
            
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

