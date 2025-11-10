import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";
import { componentTagger } from "lovable-tagger";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
  // Usar caminho base diferente para desenvolvimento e produção
  base: mode === 'production' ? '/ob2/' : '/',
  server: {
    host: "::",
    port: 8080,
    proxy: {
      '/appfacil': {
        target: 'https://gestaodeobrafacil.com',
        changeOrigin: true,
        secure: true,
        rewrite: (path) => path.replace(/^\/appfacil/, '/appfacil')
      }
    },
    // Adicionado para resolver o problema de roteamento SPA
    historyApiFallback: {
      // Redireciona todas as rotas para index.html
      rewrites: [
        { from: /^.*$/, to: '/index.html' }
      ]
    }
  },
  preview: {
    host: true,
    port: 8080,
    strictPort: true,
    // Configuração para o ambiente de preview (build)
    historyApiFallback: {
      rewrites: [
        { from: /^.*$/, to: '/index.html' }
      ]
    }
  },
  plugins: [react(), mode === "development" && componentTagger()].filter(Boolean),
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    rollupOptions: {
      output: {
        manualChunks: undefined,
      }
    }
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
}));