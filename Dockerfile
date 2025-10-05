# Imagen base de PHP
FROM php:8.2-cli

# Copia los archivos del proyecto
COPY . /app
WORKDIR /app

# Expone el puerto que Render necesita
EXPOSE 10000

# Comando para iniciar el servidor PHP
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
