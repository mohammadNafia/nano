# NANO

A modern, polished Svelte frontend for the NANO Platform - an AI-powered OCR service that extracts text from images.

## Features

- 🎨 **Modern UI** - Built with Svelte 5, Tailwind CSS, and custom components with beautiful animations
- 🌓 **Dark Mode** - Full light/dark theme support with smooth transitions
- 📱 **Responsive** - Works seamlessly on mobile, tablet, and desktop
- 🔐 **Authentication** - Secure user authentication with JWT tokens
- 📊 **Dashboard** - Comprehensive usage analytics, metrics, and upload history
- 💳 **Subscriptions** - Stripe integration for Pro plans (Monthly & Yearly)
- 🔑 **API Keys** - API access management for Pro users
- 📤 **OCR Upload** - Drag-and-drop file upload with real-time processing
- ✨ **Animations** - Smooth typing effects and UI transitions
- ⚡ **Fast** - Optimized performance with SvelteKit

## Tech Stack

- **Framework**: SvelteKit 2.x with Svelte 5 (using runes)
- **Styling**: Tailwind CSS 3.x with custom design system
- **UI Components**: Custom components with ShineBorder, ShimmerButton, and AnimatedLabel effects
- **HTTP Client**: Axios with interceptors for auth
- **Animations**: @motionone/svelte for smooth animations
- **Icons**: lucide-svelte
- **TypeScript**: Full type safety throughout
- **State Management**: Svelte stores (auth, usage, theme)

## Project Structure

```
src/
├── lib/
│   ├── api/
│   │   ├── client.ts          # API client with Axios interceptors
│   │   ├── index.ts           # API exports
│   │   └── mockClient.ts      # Mock API client (optional)
│   ├── components/
│   │   ├── dashboard/         # Dashboard components
│   │   │   ├── ApiKeyManager.svelte
│   │   │   ├── HistoryTable.svelte
│   │   │   └── MetricCard.svelte
│   │   ├── layout/            # Layout components
│   │   │   ├── Navbar.svelte
│   │   │   └── Footer.svelte
│   │   ├── ui/                # Reusable UI components
│   │   │   ├── Button.svelte
│   │   │   ├── Card.svelte
│   │   │   ├── Input.svelte
│   │   │   ├── Modal.svelte
│   │   │   └── ShineBorder.svelte
│   │   ├── UploadArea.svelte  # File upload component
│   │   └── OcrResultPanel.svelte
│   ├── stores/                # Svelte stores
│   │   ├── authStore.ts       # Authentication state
│   │   ├── usageStore.ts      # Usage metrics state
│   │   └── themeStore.ts      # Theme preferences
│   └── utils.ts               # Utility functions
├── routes/
│   ├── +layout.svelte         # Root layout
│   ├── +page.svelte           # Landing page
│   ├── auth/                  # Authentication
│   │   ├── +page.svelte
│   │   ├── AnimatedLabel.svelte
│   │   ├── ShimmerButton.svelte
│   │   └── ShineBorder.svelte
│   ├── upload/                # OCR upload page
│   │   └── +page.svelte
│   ├── dashboard/             # User dashboard
│   │   └── +page.svelte
│   ├── pricing/               # Pricing plans
│   │   └── +page.svelte
│   └── docs/                  # API documentation
│       └── +page.svelte
└── app.css                    # Global styles and Tailwind
```

## Getting Started


[Your License Here]
