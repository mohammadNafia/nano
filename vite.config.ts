import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [sveltekit()],
	server: {
		host: '0.0.0.0', // Listen on all network interfaces
		port: 5173,
		strictPort: false
	},
	build: {
		rollupOptions: {
			output: {
				manualChunks: (id) => {
					// Split large vendor libraries into separate chunks
					if (id.includes('node_modules')) {
						// Separate chunk for heavy libraries
						if (id.includes('jspdf') || id.includes('docx') || id.includes('file-saver')) {
							return 'export-libs';
						}
						// Separate chunk for axios (API client)
						if (id.includes('axios')) {
							return 'api-client';
						}
						// Separate chunk for lucide icons
						if (id.includes('lucide-svelte')) {
							return 'icons';
						}
						// Default vendor chunk
						return 'vendor';
					}
				}
			}
		},
		// Enable minification
		minify: 'terser',
		terserOptions: {
			compress: {
				drop_console: true, // Remove console.log in production
				drop_debugger: true
			}
		},
		// Optimize chunk size
		chunkSizeWarningLimit: 1000
	},
	optimizeDeps: {
		// Pre-bundle dependencies for faster dev server startup
		include: ['axios', 'lucide-svelte'],
		exclude: ['jspdf', 'docx', 'file-saver'] // Exclude heavy libs from pre-bundling
	}
});
