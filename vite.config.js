import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    /** Use IPv4 so `public/hot` matches browsers on http://127.0.0.1:8000 (avoids blank page when [::1]:5173 fails). */
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: { host: '127.0.0.1' },
    },
    plugins: [
        react(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/react.css',
                'resources/js/app.js',
                'resources/js/react/main.tsx',
            ],
            refresh: true,
        }),
    ],
});
