import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public/app',
    emptyOutDir: true,
    assetsDir: 'assets',
  },
  // base = /itanagar/ → import.meta.env.BASE_URL = '/itanagar/'
  // BrowserRouter uses BASE_URL as its basename → clean URLs like /itanagar/games/3
  base: '/itanagar/',
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
