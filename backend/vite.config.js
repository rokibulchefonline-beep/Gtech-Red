import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Only the admin panel theme is built here; the public site uses public/css/site.css and public/js/site.js.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
    ],
});
