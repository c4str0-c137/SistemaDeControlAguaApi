FROM php:8.4-apache

# Habilitamos mod_rewrite para que funcionen las rutas de Laravel
RUN a2enmod rewrite

# Instalamos dependencias del sistema y extensiones de PHP requeridas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql pdo_mysql zip

# Instalamos Composer (gestor de paquetes de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuramos Apache para que apunte a la carpeta 'public' de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Establecemos el directorio de trabajo
WORKDIR /var/www/html

# Copiamos primero composer.json para aprovechar la caché de Docker
COPY composer.json composer.lock ./

# Instalamos las dependencias de producción de Laravel
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiamos el resto del código de la aplicación
COPY . .

# Generamos los archivos de carga optimizados
RUN composer dump-autoload --optimize

# Le damos permisos a Apache sobre las carpetas que Laravel necesita escribir
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponemos el puerto 80 (el que usará Dokploy)
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
