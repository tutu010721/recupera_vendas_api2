# Estágio 1: Instalar as dependências com o Composer
FROM composer:2.7 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Estágio 2: Construir a imagem final da aplicação
FROM php:8.2-fpm-alpine

# Argumentos que podem ser passados durante o build
ARG APP_USER=www-data
ARG APP_GROUP=www-data

# Instala pacotes do sistema e extensões PHP necessárias para o Laravel
# === VERSÃO SIMPLIFICADA E CORRIGIDA DA LINHA ABAIXO ===
RUN apk update && apk add --no-cache \
    libxml2-dev \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install \
    bcmath \
    mbstring \
    pdo_pgsql \
    xml \
    zip

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos da aplicação e as dependências já instaladas
COPY --from=vendor /app/vendor /var/www/vendor
COPY . .

# Ajusta as permissões dos arquivos
RUN chown -R ${APP_USER}:${APP_GROUP} /var/www

# Expõe a porta que o 'php artisan serve' vai usar
EXPOSE 8000

# O comando para iniciar a aplicação
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
