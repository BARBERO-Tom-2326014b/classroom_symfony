import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://0.0.0.0:8000',
        changeOrigin: true,
      },
      // proxy uniquement /login EXACT (pas /login/etudiant)
      '^/login$': {
        target: 'http://0.0.0.0:8000',
        changeOrigin: true,
      },
      '/logout': {
        target: 'http://0.0.0.0:8000',
        changeOrigin: true,
      },
    },
  },
})
