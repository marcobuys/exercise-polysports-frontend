import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue2';

// Vitest runs the store/unit suite in a jsdom environment. It intentionally does
// NOT reuse vite.config.js (that one wires up the Laravel/Vite dev server).
export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.spec.js'],
    },
});
