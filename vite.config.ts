import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';
const env = loadEnv('', '');

console.log(env.VITE_APP_URL);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    // server: {
    //     host: true,
    //     port: 5173,
    //     hmr: {
    //         host: 'localhost',
    //     },
    //     cors: {
    //         origin: '*',
    //         methods: 'GET,HEAD,PUT,POST,DELETE',
    //         preflightContinue: false,
    //         optionsSuccessStatus: 204,
    //     },
    // },
});
