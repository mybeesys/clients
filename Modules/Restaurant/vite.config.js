import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-restaurant',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-restaurant',
            input: [
                __dirname + '/resources/assets/sass/app.scss',
                __dirname + '/resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});

//export const paths = [
//    'Modules/Restaurant/resources/assets/sass/app.scss',
//    'Modules/Restaurant/resources/assets/js/app.js',
//];