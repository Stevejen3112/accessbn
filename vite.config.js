import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';


function getTemplate() {
    try {
        const content = fs.readFileSync('.env', 'utf-8');
        const match = content.match(/TEMPLATE=['"]?([^'"\n]+)['"]?/);
        return match ? match[1] : 'bento';
    } catch (e) {
        return 'bento';
    }
}

const template = getTemplate();

export default defineConfig({
    plugins: [
        laravel({
            input: [
                `resources/views/templates/${template}/css/app.css`,
                `resources/views/templates/${template}/js/app.js`
            ],
            refresh: [
                `resources/views/templates/${template}/**`,
                'routes/**',
                'config/site.php',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: 'lozand.local',
        cors: true,
        https: {
            key: fs.readFileSync('C:/laragon/etc/ssl/laragon.key'),
            cert: fs.readFileSync('C:/laragon/etc/ssl/laragon.crt'),
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
