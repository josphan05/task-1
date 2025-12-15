import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/coreui.css',
                'resources/css/select2-custom.css',
                'resources/js/coreui.js',
                'resources/js/select2-init.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@coreui/coreui': path.resolve(__dirname, 'node_modules/@coreui/coreui'),
        }
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
