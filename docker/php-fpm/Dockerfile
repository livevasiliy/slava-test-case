FROM php:8.3-fpm-alpine

# Установка необходимых пакетов
RUN apk add --no-cache \
    supervisor \
    bash \
    git \
    composer \
    icu-dev \
    postgresql-dev \
    redis \
    nodejs \
    npm

# Установка PHP расширений через install-php-extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions opcache pdo_pgsql pgsql psr sockets zip gd redis pcntl

# Установка Composer глобально
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка глобальных Node.js пакетов (если они нужны)
RUN npm i -g laravel-echo-server

# Копирование конфигурации Supervisor
COPY docker/supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Настройка рабочей директории
WORKDIR /var/www/html

# Разрешение Git работать с директорией
RUN git config --global --add safe.directory /var/www/html

# Копирование приложения
COPY . .

# Установка зависимостей Composer и NPM
RUN composer install --no-dev --optimize-autoloader && \
    npm install && \
    npm run build

# Порт для PHP-FPM
EXPOSE 9000

# Запуск Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
