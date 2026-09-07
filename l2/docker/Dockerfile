FROM php:8.3-apache

# Install mlocati php extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-client \
    unzip \
    libldap-common \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions for i-doit
RUN install-php-extensions \
    bcmath \
    curl \
    gd \
    intl \
    ldap \
    mbstring \
    mysqli \
    opcache \
    pdo_mysql \
    soap \
    sockets \
    xml \
    zip

# Enable Apache modules
RUN a2enmod rewrite expires headers

# Apache and PHP configs
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/conf.d/idoit.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
