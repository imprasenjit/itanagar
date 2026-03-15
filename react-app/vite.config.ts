import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig(({ command }) => ({
  plugins: [react()],
  build: {
    outDir: '../public/app',
    emptyOutDir: true,
  },
  base: command === 'serve' ? '/' : '/itanagar/public/app/',
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost/itanagar/index.php',
        changeOrigin: true,
        rewrite: (path) => path,
      },
      // Proxy game image requests to Apache so dev server can serve them
      '/itanagar/public/imglogo': {
        target: 'http://localhost',
        changeOrigin: true,
      },
    },
  },
}))
