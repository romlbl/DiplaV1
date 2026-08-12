FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    default-libmysqlclient-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN echo "DirectoryIndex main.php index.php index.html" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80