# Imagen base con PHP 8.2
FROM php:8.2-cli

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql pgsql

# Copiar los archivos del proyecto
COPY . /app
WORKDIR /app

# Exponer el puerto que Render usa
EXPOSE 10000

# Comando para iniciar el servidor PHP
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
