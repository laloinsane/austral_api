# Austra_api

### Descripción
API desarrollada para entregar información del `Austral_sistema`, en archivos json y geojson.

### Construcción / Modificación

``` bash
# instalación de composer en local
sudo su
curl -sS https://getcomposer.org/installer | php

# instalación de dependencias
php composer.phar install

# para utilizar logs, dar permisos de escritura a la carpeta logs
sudo chmod 777 logs/

- Implementar la base de datos y modificar los parámetros de conexión del archivo austral_api/src/db.php.
```
