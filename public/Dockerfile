FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip unzip nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP y JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080
RUN chmod +x docker/start.sh
CMD ["docker/start.sh"]
