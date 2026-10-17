# =========================================================================
# STAGE 1: BUILD STAGE (Node.js Build Environment)
# =========================================================================
FROM node:22-alpine AS build-stage

WORKDIR /app

# Salin package manifest dan install dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Salin seluruh source code frontend
COPY . .

# Build aplikasi Vue 3 untuk produksi
RUN npm run build

# =========================================================================
# STAGE 2: PRODUCTION STAGE (Lightweight Nginx Web Server)
# =========================================================================
FROM nginx:1.27-alpine AS production-stage

# Salin hasil build dari stage 1 (tanpa menyertakan node_modules)
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Salin konfigurasi kustom Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
