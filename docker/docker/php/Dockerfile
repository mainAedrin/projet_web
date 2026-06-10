FROM php:8.2-apache

# ─── Extensions PHP nécessaires au projet ────────────────────────
RUN docker-php-ext-install pdo pdo_mysql mysqli

# ─── Activer mod_rewrite Apache (pour les URLs propres) ──────────
RUN a2enmod rewrite

# ─── Configuration Apache : autoriser .htaccess ──────────────────
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# ─── Dossier uploads : permissions d'écriture ────────────────────
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod 755 /var/www/html/uploads
