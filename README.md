# Austra_api

### Descripción

API desarrollada para entregar información correspondiente a `Austral_sistema`, en archivos json y geojson.

### Construcción / Modificación

``` bash
# instalación de composer en local
sudo su
curl -sS https://getcomposer.org/installer | php

# instalación de dependencias
php composer.phar install

# para utilizar logs, dar permisos de escritura a la carpeta logs
sudo chmod 777 logs/

# ejemplo de log
$this->logger->addInfo('Algo pasó');

- Implementar la base de datos y modificar los parámetros de conexión del archivo austral_api/src/db.php.
```

### Utilización

Utilización de la API a través del comando cURL.

``` bash
# Listado de todos los campus ordenados por el id del campus.
curl http://localhost/austral_api/public/index.php/v1/campus

# Listado de todas las unidades pertenecientes a un campus en específico, determinado por el id del campus y ordenados por el id de la unidad.
curl http://localhost/austral_api/public/index.php/v1/campus/{id_campus}

# Obtiene una unidad específica de un campus específico, determinada por el id de la unidad y por el id del campus, tambien se incluye el listado de sus conexiones, así como el listado de todos los nodos pertenecientes al campus específico.
curl http://localhost/austral_api/public/index.php/v1/campus/{id_campus}/unidad/{id_unidad}

# Listado de todas las personas pertenecientes a un campus en específico, determinado por el id del campus y ordenados por el id de la persona.
curl http://localhost/austral_api/public/index.php/v1/campus/{id_campus}/persona/

# Listado de todas las personas filtradas por el nombre de la persona, pertenecientes a un campus en específico, determinado por el id del campus y ordenados por el id de la persona.
curl http://localhost/austral_api/public/index.php/v1/campus/{id_campus}/persona/{nombre_persona}

# Obtiene un geojson con todos los datos del sistema.
curl http://localhost/austral_api/public/index.php/v1/data.geojson
```