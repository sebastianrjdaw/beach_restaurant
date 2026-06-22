import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
        },
        watch: {
            usePolling: process.env.VITE_USE_POLLING === 'true',
            interval: 100,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
