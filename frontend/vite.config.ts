import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public',
    emptyOutDir: true,
    assetsDir: 'assets',
  },
  // Dev: served by Vite dev server on port 5173
  // Production: served at domain root on itanagarchoice.com
  base: '/',
  server: {
    port: 5173,
    proxy: {
      // Dev server proxies /api/* to CI4 backend
      '/api': {
        target: 'http://localhost/itanagar/backend/public',
        changeOrigin: true,
      },
      // Proxy imglogo/* to CI4 backend public folder
      '/imglogo': {
        target: 'http://localhost/itanagar/backend/public',
        changeOrigin: true,
      },
    },
  },
})
