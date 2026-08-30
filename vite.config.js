import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/themes/flamingo.css',
                'resources/css/themes/bubbles.css',
                'resources/js/app.js',
                'resources/js/themes/bubbles.js',
                'resources/js/admin-charts.js',
            ],
            refresh: true,
        }),
    ],
});
