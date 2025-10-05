# Imagen base con PHP 8.2
FROM php:8.2-cli

# Copiamos los archivos del proyecto al contenedor
COPY . /app
WORKDIR /app

# Exponemos el puerto que Render usa (10000)
EXPOSE 10000

# Comando para iniciar el servidor PHP
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
