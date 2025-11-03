import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path, { resolve } from 'path';
import { defineConfig } from 'vite';
import { watchAndRun } from 'vite-plugin-watch-and-run';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx', 'resources/css/prezet.css'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        watchAndRun([
            {
                name: 'prezet:index',
                watch: path.resolve('prezet/**/*.(md|jpg|png|webp)'),
                run: 'php artisan prezet:index',
                delay: 1000,
                // watchKind: ['add', 'change', 'unlink'], // (default)
            },
        ]),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
