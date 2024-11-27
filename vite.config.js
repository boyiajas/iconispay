/* import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            'ziggy-js': path.resolve('vendor/tightenco/ziggy/dist/vue.es.js'),
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue()
    ],
});
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
    const isProduction = mode === 'production';

    return {
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'), // Alias for easier imports
                'ziggy-js': path.resolve('vendor/tightenco/ziggy/dist/vue.es.js'),
            },
        },
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
            vue(),
        ],
        build: {
            sourcemap: !isProduction, // Disable sourcemaps in production
            rollupOptions: {
                output: {
                    assetFileNames: 'assets/[name].[hash][extname]', // For consistent file naming
                    chunkFileNames: 'js/[name].[hash].js',
                    entryFileNames: 'js/[name].[hash].js',
                },
            },
        },
        server: {
            host: '127.0.0.1', // Explicitly set host if needed
            port: 3000, // Customize if necessary
        },
    };
});
