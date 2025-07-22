// vite.config.js

import vue from '@vitejs/plugin-vue';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '192.168.18.53',
    port: 5173,
    origin: 'http://192.168.18.53:5173',
    cors: true, // ✅ enable CORS
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      vue: 'vue/dist/vue.esm-bundler.js', // ✅ allow templates in SFC
    },
  },
});
