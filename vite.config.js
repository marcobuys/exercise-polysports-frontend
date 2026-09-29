import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import tailwindcss from '@tailwindcss/vite';

// Fixes hot reload for components other than App.vue.
//
// Tailwind watches every .vue file it scans for classes, so on a change Vite
// passes two modules for the same file: the real component and a Tailwind
// "file-only" entry. @vitejs/plugin-vue2 (2.3.4, no longer updated) simply takes
// the first one — and when that's Tailwind's entry, the component is never
// hot-updated (you'd need a manual refresh). The Vue 3 plugin already avoids
// this by picking the real module first; we do the same here, and put
// Tailwind's entry back afterwards so new utility classes still update the CSS.
function vue2HmrFix() {
    return [
        {
            name: 'vue2-hmr-fix:real-module-first',
            enforce: 'pre',
            handleHotUpdate({ modules }) {
                // Real modules have an id; Tailwind's file-only entries don't.
                return [...modules].sort((a, b) => Number(!a.id) - Number(!b.id));
            },
        },
        {
            name: 'vue2-hmr-fix:keep-tailwind-update',
            enforce: 'post',
            handleHotUpdate({ file, modules, server }) {
                if (!file.endsWith('.vue')) return;
                const fileOnly = [...(server.moduleGraph.getModulesByFile(file) ?? [])].filter((m) => !m.id);
                return [...modules, ...fileOnly.filter((m) => !modules.includes(m))];
            },
        },
    ];
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        vue2HmrFix(),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
