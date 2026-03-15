import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public/ui',
    emptyOutDir: true,
    assetsDir: 'assets',
  },
  // Dev: /itanagar/ (runs under XAMPP subfolder)
  // Production: /ui/ (CI4 public/ is document root; React assets live at public/ui/)
  base: process.env.NODE_ENV === 'production' ? '/ui/' : '/itanagar/',
  server: {
    port: 5173,
    proxy: {
      // Dev server proxies /api/* to CI4 running under Apache
      '/api': {
        target: 'http://localhost/itanagar',
        changeOrigin: true,
      },
      // Proxy game logo images to Apache
      '/itanagar/public/imglogo': {
        target: 'http://localhost',
        changeOrigin: true,
      },
    },
  },
})
