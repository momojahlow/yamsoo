import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';

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
        drop: ['console', 'debugger'], // Supprimer console.log en production
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    build: {
        // Optimisations de build
        minify: 'esbuild',
        cssMinify: true,
        rollupOptions: {
            output: {
                // Chunking manuel pour optimiser le cache
                manualChunks: {
                    vendor: ['react', 'react-dom'],
                    ui: ['lucide-react', '@headlessui/react'],
                    utils: ['clsx', 'tailwind-merge'],
                },
                // Noms de fichiers avec hash pour le cache
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },
        // Taille limite des chunks
        chunkSizeWarningLimit: 1000,
        // Compression
        reportCompressedSize: true,
    },
    // Optimisations de développement
    server: {
        hmr: {
            overlay: false, // Désactiver l'overlay d'erreur en dev
        },
    },
});
