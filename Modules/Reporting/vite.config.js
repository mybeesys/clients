import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-reporting',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-reporting',
            input: [
                __dirname + '/resources/assets/sass/app.scss',
                __dirname + '/resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});

//export const paths = [
//    'Modules/Reporting/resources/assets/sass/app.scss',
//    'Modules/Reporting/resources/assets/js/app.js',
//];