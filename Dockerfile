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

# === VERSÃO DETETIVE: INSTALANDO UMA COISA DE CADA VEZ ===

# Passo A: Instala os pacotes do sistema (sabemos que esta parte funciona)
RUN apk update && apk add --no-cache build-base libxml2-dev postgresql-dev libzip-dev

# Passo B: Instala as extensões do PHP, UMA POR UMA, para encontrar a culpada
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install xml
RUN docker-php-ext-configure zip # Configuração especial para a extensão zip
RUN docker-php-ext-install zip

# =============================================================

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
