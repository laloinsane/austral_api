<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        //->withHeader('Access-Control-Allow-Origin', 'http://ecpm.cl')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

/**
 * all campus
 * http://localhost/tesis/austral_api/public/index.php/v1/campus
 */
$app->get('/v1/campus', function(Request $request, Response $response){
    $sql_campus = "SELECT * FROM CAMPUS ORDER BY ID_CAMPUS ASC";
    $sql_total_campus = "SELECT count(*) AS TOTAL_CAMPUS FROM CAMPUS";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_campus = $db->query($sql_campus);
        $datos_campus = $stmt_campus->fetchAll(PDO::FETCH_OBJ);
        $longitud_campus = count($datos_campus);

        $stmt_total_campus = $db->query($sql_total_campus);
        $datos_total_campus = $stmt_total_campus->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        $campus = array();

        for($i=0; $i<$longitud_campus; $i++) {
            $object = (object) array("id_campus" => $datos_campus[$i]->ID_CAMPUS, "nombre_campus" => $datos_campus[$i]->NOMBRE_CAMPUS, "direccion_campus" => $datos_campus[$i]->DIRECCION_CAMPUS, "latitud_campus" => $datos_campus[$i]->LATITUD_CAMPUS, "longitud_campus" => $datos_campus[$i]->LONGITUD_CAMPUS);
            array_push($campus, $object);
        }

        $json_campus = json_encode($campus, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        $json = '{
            "total_campus": '.$datos_total_campus[0]->TOTAL_CAMPUS.',
            "campus":'.$json_campus.'
        }';
        
        echo $json;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * all unidades id campus
 * http://localhost/tesis/austral_api/public/index.php/v1/campus/{id}
 */
$app->get('/v1/campus/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql_unidades = "SELECT * FROM UNIDAD WHERE ID_CAMPUS = '$id'";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_unidades = $db->query($sql_unidades);
        $datos_unidades = $stmt_unidades->fetchAll(PDO::FETCH_OBJ);
        $longitud_unidades = count($datos_unidades);

        $db = null;

        $unidades = array();

        for($i=0; $i<$longitud_unidades; $i++) {
            $object = (object) array("id_unidad" => $datos_unidades[$i]->ID_UNIDAD, "id_campus" => $datos_unidades[$i]->ID_CAMPUS, "nombre_unidad" => $datos_unidades[$i]->NOMBRE_UNIDAD, "descripcion_unidad" => $datos_unidades[$i]->DESCRIPCION_UNIDAD, "latitud_unidad" => $datos_unidades[$i]->LATITUD_UNIDAD, "longitud_unidad" => $datos_unidades[$i]->LONGITUD_UNIDAD);
            array_push($unidades, $object);
        }

        $json = json_encode($unidades, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});


/**
 * unidad by id campus
 * http://localhost/tesis/austral_api/public/index.php/v1/unidad/{id}
 */
$app->get('/v1/unidad/{id}&{id_campus}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql_unidad = "SELECT * FROM UNIDAD WHERE ID_UNIDAD = '$id'";
    $id_campus = $request->getAttribute('id_campus');
    $sql_nodos = "SELECT * FROM NODO WHERE ID_CAMPUS = '$id_campus'";
    try{
        $db = new db();
        $db = $db->connect();

        $stmt_unidad = $db->query($sql_unidad);
        $datos_unidad = $stmt_unidad->fetchAll(PDO::FETCH_OBJ);
        $longitud_unidad = count($datos_unidad);


        $stmt_nodos = $db->query($sql_nodos);
        $datos_nodos = $stmt_nodos->fetchAll(PDO::FETCH_OBJ);
        $longitud_nodos = count($datos_nodos);



        $db = null;

        $nodos = array();

        for($i=0; $i<$longitud_nodos; $i++) {
            $object = (object) array("id_nodo" => $datos_nodos[$i]->ID_NODO, "id_campus" => $datos_nodos[$i]->ID_CAMPUS, "latitud_nodo" => $datos_nodos[$i]->LATITUD_NODO, "longitud_nodo" => $datos_nodos[$i]->LONGITUD_NODO, 'conexiones' => conexionNodoNodo($datos_nodos[$i]->ID_NODO));
            array_push($nodos, $object);
        }

        $objectx = (object) array();

        for($i=0; $i<$longitud_unidad; $i++) {
            $objectx = (object) array("id_unidad" => $datos_unidad[0]->ID_UNIDAD, "id_campus" => $datos_unidad[0]->ID_CAMPUS, "nombre_unidad" => $datos_unidad[0]->NOMBRE_UNIDAD, "descripcion_unidad" => $datos_unidad[0]->DESCRIPCION_UNIDAD, "latitud_unidad" => $datos_unidad[0]->LATITUD_UNIDAD, "longitud_unidad" => $datos_unidad[0]->LONGITUD_UNIDAD, 'conexiones' => conexionUnidadNodo($datos_unidad[$i]->ID_UNIDAD), 'nodos' => $nodos);
        }

        $json = json_encode($objectx, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        echo $json;
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * all unidades id campus
 * http://localhost/tesis/austral_api/public/index.php/v1/campus/{id}
 */
$app->get('/v1/campustest/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql_unidades = "SELECT * FROM UNIDAD WHERE ID_CAMPUS = '$id'";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_unidades = $db->query($sql_unidades);
        $datos_unidades = $stmt_unidades->fetchAll(PDO::FETCH_OBJ);
        $longitud_unidades = count($datos_unidades);

        $db = null;

        $unidades = array();

        for($i=0; $i<$longitud_unidades; $i++) {
            $object = (object) array("id_unidad" => $datos_unidades[$i]->ID_UNIDAD, "id_campus" => $datos_unidades[$i]->ID_CAMPUS, "nombre_unidad" => $datos_unidades[$i]->NOMBRE_UNIDAD, "descripcion_unidad" => $datos_unidades[$i]->DESCRIPCION_UNIDAD, "latitud_unidad" => $datos_unidades[$i]->LATITUD_UNIDAD, "longitud_unidad" => $datos_unidades[$i]->LONGITUD_UNIDAD, 'conexiones' => conexionUnidadNodo($datos_unidades[$i]->ID_UNIDAD));
            array_push($unidades, $object);
        }

        $json = json_encode($unidades, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * all nodos id campus
 * http://localhost/tesis/austral_api/public/index.php/v1/nodos/{id}
 */
$app->get('/v1/nodos/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql_nodos = "SELECT * FROM NODO WHERE ID_CAMPUS = '$id'";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_nodos = $db->query($sql_nodos);
        $datos_nodos = $stmt_nodos->fetchAll(PDO::FETCH_OBJ);
        $longitud_nodos = count($datos_nodos);

        $db = null;

        $nodos = array();

        for($i=0; $i<$longitud_nodos; $i++) {
            $object = (object) array("id_nodo" => $datos_nodos[$i]->ID_NODO, "id_campus" => $datos_nodos[$i]->ID_CAMPUS, "latitud_nodo" => $datos_nodos[$i]->LATITUD_NODO, "longitud_nodo" => $datos_nodos[$i]->LONGITUD_NODO, 'conexiones' => conexionNodoNodo($datos_nodos[$i]->ID_NODO));
            array_push($nodos, $object);
        }

        /*for($i=0; $i<$longitud_nodos; $i++) {
            $object = (object) array("id_nodo" => $datos_nodos[$i]->ID_NODO, "id_campus" => $datos_nodos[$i]->ID_CAMPUS, "latitud_nodo" => $datos_nodos[$i]->LATITUD_NODO, "longitud_nodo" => $datos_nodos[$i]->LONGITUD_NODO);
            array_push($nodos, $object);
        }*/

        $json = json_encode($nodos, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

function conexionNodoNodo ($id_nodo){
    //Conexiones camino
    $sql_arista_nodo = "SELECT * FROM ARISTA_NODO WHERE ID_NODO = ".$id_nodo;
    //Conexiones unidad
    $sql_arista_unidad = "SELECT * FROM ARISTA_UNIDAD WHERE ID_NODO = ".$id_nodo;

    try{
        $db = new db();
        $db = $db->connect();

        //Conexiones camino
        $stmt_arista_nodo = $db->query($sql_arista_nodo);
        $datos_arista_nodo = $stmt_arista_nodo->fetchAll(PDO::FETCH_OBJ);
        //Conexiones entidad
        $stmt_arista_unidad = $db->query($sql_arista_unidad);
        $datos_arista_unidad = $stmt_arista_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();
        //Conexiones camino
        $longitud_arista_nodo = count($datos_arista_nodo);
        //Conexiones camino
        $longitud_arista_unidad = count($datos_arista_unidad);

        //Conexiones camino
        for($i=0; $i<$longitud_arista_nodo; $i++){
            $object = (object) array('destino' => $datos_arista_nodo[$i]->NOD_ID_NODO, 'distancia' => $datos_arista_nodo[$i]->DISTANCIA_NODO);
            array_push($conexiones, $object);
        }

        //Conexiones entidad
        /*for($i=0; $i<$longitud_arista_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_arista_unidad[$i]->ID_NODO, 'destino' => $datos_arista_unidad[$i]->ID_UNIDAD);
            array_push($conexiones, $object);
        }*/
    
        return $conexiones;

    } catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}


function conexionUnidadNodo ($id_unidad){
    //Conexiones unidad
    $sql_arista_unidad = "SELECT * FROM ARISTA_UNIDAD WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Conexiones unidad
        $stmt_arista_unidad = $db->query($sql_arista_unidad);
        $datos_arista_unidad = $stmt_arista_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Conexiones unidad
        $longitud_arista_unidad = count($datos_arista_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_arista_unidad; $i++){
            $object = (object) array('destino' => $datos_arista_unidad[$i]->ID_NODO, 'distancia' => $datos_arista_unidad[$i]->DISTANCIA_UNIDAD);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}


/**
 * all nodos id campus
 * http://localhost/tesis/austral_api/public/index.php/v1/campus/{id}
 */
$app->get('/v1/nodostest/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sql_nodos = "SELECT * FROM NODO WHERE ID_CAMPUS = '$id'";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_nodos = $db->query($sql_nodos);
        $datos_nodos = $stmt_nodos->fetchAll(PDO::FETCH_OBJ);
        $longitud_nodos = count($datos_nodos);

        $db = null;

        $nodos = array();

        for($i=0; $i<$longitud_nodos; $i++) {
            $object = (object) array("id_nodo" => $datos_nodos[$i]->ID_NODO, "id_campus" => $datos_nodos[$i]->ID_CAMPUS, "latitud_nodo" => $datos_nodos[$i]->LATITUD_NODO, "longitud_nodo" => $datos_nodos[$i]->LONGITUD_NODO, 'conexiones' => conexionNodoNodo($datos_nodos[$i]->ID_NODO));
            array_push($nodos, $object);
        }

        /*for($i=0; $i<$longitud_nodos; $i++) {
            $object = (object) array("id_nodo" => $datos_nodos[$i]->ID_NODO, "id_campus" => $datos_nodos[$i]->ID_CAMPUS, "latitud_nodo" => $datos_nodos[$i]->LATITUD_NODO, "longitud_nodo" => $datos_nodos[$i]->LONGITUD_NODO);
            array_push($nodos, $object);
        }*/

        $json = json_encode($nodos, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * all unidades
 * http://localhost/tesis/austral_api/public/index.php/v1/unidades
 */
$app->get('/v1/unidades', function(Request $request, Response $response){
    $sql1 = "SELECT * FROM UNIDAD ORDER BY ID_UNIDAD ASC";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt1 = $db->query($sql1);
        $datos1 = $stmt1->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        $json1 = json_encode($datos1, JSON_UNESCAPED_UNICODE);

		echo $json1;

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * geo.json correspondiente a la base de datos 10
 * http://localhost/tesis/austral_api/public/index.php/v1/geo10.json
 */
$app->get('/v1/geo10.json', function(Request $request, Response $response){
    //Campus points
    $sql_campus = "SELECT * FROM CAMPUS ORDER BY ID_CAMPUS ASC";
    //Unidad points
    $sql_unidad = "SELECT * FROM UNIDAD ORDER BY ID_UNIDAD ASC";
    //Nodo points
    $sql_nodo = "SELECT * FROM NODO ORDER BY ID_NODO ASC";
    //Arista nodo linestrings
    $sql_arista_nodo = "SELECT A.ID_NODO, A.LATITUD_NODO, A.LONGITUD_NODO, B.ID_NODO AS ID_NODO_B, B.LATITUD_NODO AS LATITUD_NODO_B, B.LONGITUD_NODO AS LONGITUD_NODO_B FROM NODO AS A INNER JOIN ARISTA_NODO ON A.ID_NODO = ARISTA_NODO.ID_NODO INNER JOIN NODO AS B ON ARISTA_NODO.NOD_ID_NODO = B.ID_NODO";
    //Arista unidad linestrings
    $sql_arista_unidad = "SELECT A.ID_UNIDAD, A.LATITUD_UNIDAD, A.LONGITUD_UNIDAD, B.ID_NODO, B.LATITUD_NODO, B.LONGITUD_NODO FROM UNIDAD AS A INNER JOIN ARISTA_UNIDAD ON A.ID_UNIDAD = ARISTA_UNIDAD.ID_UNIDAD INNER JOIN NODO AS B ON ARISTA_UNIDAD.ID_NODO = B.ID_NODO";

    try{
        $db = new db();
        $db = $db->connect();
        
        //Campus points
        $stmt_campus = $db->query($sql_campus);
        $datos_campus = $stmt_campus->fetchAll(PDO::FETCH_OBJ);
        //Unidad points
        $stmt_unidad = $db->query($sql_unidad);
        $datos_unidad = $stmt_unidad->fetchAll(PDO::FETCH_OBJ);
        //Nodo points
        $stmt_nodo = $db->query($sql_nodo);
        $datos_nodo = $stmt_nodo->fetchAll(PDO::FETCH_OBJ);
        //Arista nodo linestrings
        $stmt_arista_nodo = $db->query($sql_arista_nodo);
        $datos_arista_nodo = $stmt_arista_nodo->fetchAll(PDO::FETCH_OBJ);
        //Arista unidad linestrings
        $stmt_arista_unidad = $db->query($sql_arista_unidad);
        $datos_arista_unidad = $stmt_arista_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $features = array();
        //Campus points
        $longitud_campus = count($datos_campus);
        //Unidad points
        $longitud_unidad = count($datos_unidad);
        //Nodo points
        $longitud_nodo = count($datos_nodo);
        //Arista nodo linestrings
        $longitud_arista_nodo = count($datos_arista_nodo);
        //Arista unidad linestrings
        $longitud_arista_unidad = count($datos_arista_unidad);

        //Campus points
        for($i=0; $i<$longitud_campus; $i++) {
            $properties = (object) array('class' => 'Campus', 'id' => $datos_campus[$i]->ID_CAMPUS, 'nombre' => $datos_campus[$i]->NOMBRE_CAMPUS, 'direccion' => $datos_campus[$i]->DIRECCION_CAMPUS);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_campus[$i]->LONGITUD_CAMPUS, $datos_campus[$i]->LATITUD_CAMPUS));
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            array_push($features, $object);
        }

        //Unidad points
        for($i=0; $i<$longitud_unidad; $i++) {
            $properties = (object) array('class' => 'Unidad','id' => $datos_unidad[$i]->ID_UNIDAD, 'nombre' => $datos_unidad[$i]->NOMBRE_UNIDAD, 'descripcion' => $datos_unidad[$i]->DESCRIPCION_UNIDAD);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_unidad[$i]->LONGITUD_UNIDAD, $datos_unidad[$i]->LATITUD_UNIDAD));
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => aristaUnidadNodo($datos_unidad[$i]->ID_UNIDAD), 'personas' => personasUnidad($datos_unidad[$i]->ID_UNIDAD));
            array_push($features, $object);
        }

        //Nodo points
        for($i=0; $i<$longitud_nodo; $i++) {
            $properties = (object) array('class' => 'Nodo', 'id' => $datos_nodo[$i]->ID_NODO);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_nodo[$i]->LONGITUD_NODO, $datos_nodo[$i]->LATITUD_NODO));
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => aristaNodoNodo($datos_nodo[$i]->ID_NODO));
            array_push($features, $object);
        }

        //Conexion nodo linestrings
        for($i=0; $i<$longitud_arista_nodo; $i++) {
            $geometry = (object) array('type' => 'LineString', 'coordinates' => array([$datos_arista_nodo[$i]->LONGITUD_NODO, $datos_arista_nodo[$i]->LATITUD_NODO], [$datos_arista_nodo[$i]->LONGITUD_NODO_B, $datos_arista_nodo[$i]->LATITUD_NODO_B]));
            $object = (object) array('type' => 'Feature', 'properties' => '', 'geometry' => $geometry);
            array_push($features, $object);
        }

        //Conexion unidad linestrings
        for($i=0; $i<$longitud_arista_unidad; $i++) {
            $geometry = (object) array('type' => 'LineString', 'coordinates' => array([$datos_arista_unidad[$i]->LONGITUD_UNIDAD, $datos_arista_unidad[$i]->LATITUD_UNIDAD], [$datos_arista_unidad[$i]->LONGITUD_NODO, $datos_arista_unidad[$i]->LATITUD_NODO]));
            $object = (object) array('type' => 'Feature', 'properties' => '', 'geometry' => $geometry);
            array_push($features, $object);
        }

        $json_features = json_encode($features, JSON_UNESCAPED_UNICODE);

        $json_geo = '{
            "type": "FeatureCollection",
            "features":'.$json_features.
        '}';
        
        echo $json_geo;
    } 
    catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/*function conexionCaminoCamino ($id_camino){
    //Conexiones camino
    $sql_conexion_camino = "SELECT * FROM CONEXION_CAMINO WHERE ID_CAMINO = ".$id_camino;
    //Conexiones entidad
    $sql_conexion_unidad = "SELECT * FROM CONEXION_UNIDAD WHERE ID_CAMINO = ".$id_camino;

    try{
        $db = new db();
        $db = $db->connect();

        //Conexiones camino
        $stmt_conexion_camino = $db->query($sql_conexion_camino);
        $datos_conexion_camino = $stmt_conexion_camino->fetchAll(PDO::FETCH_OBJ);
        //Conexiones entidad
        $stmt_conexion_unidad = $db->query($sql_conexion_unidad);
        $datos_conexion_unidad = $stmt_conexion_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();
        //Conexiones camino
        $longitud_conexion_camino = count($datos_conexion_camino);
        //Conexiones camino
        $longitud_conexion_unidad = count($datos_conexion_unidad);

        //Conexiones camino
        for($i=0; $i<$longitud_conexion_camino; $i++){
            $object = (object) array('class' => 'Camino', 'origen' => $datos_conexion_camino[$i]->ID_CAMINO, 'destino' => $datos_conexion_camino[$i]->CAM_ID_CAMINO);
            array_push($conexiones, $object);
        }

        //Conexiones entidad
        for($i=0; $i<$longitud_conexion_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_conexion_unidad[$i]->ID_CAMINO, 'destino' => $datos_conexion_unidad[$i]->ID_UNIDAD);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/

/*function conexionUnidadCamino ($id_unidad){
    //Conexiones unidad
    $sql_conexion_unidad = "SELECT * FROM CONEXION_UNIDAD WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Conexiones unidad
        $stmt_conexion_unidad = $db->query($sql_conexion_unidad);
        $datos_conexion_unidad = $stmt_conexion_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Conexiones unidad
        $longitud_conexion_unidad = count($datos_conexion_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_conexion_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_conexion_unidad[$i]->ID_UNIDAD, 'destino' => $datos_conexion_unidad[$i]->ID_CAMINO);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/

function aristaNodoNodo ($id_nodo){
    //Conexiones camino
    $sql_arista_nodo = "SELECT * FROM ARISTA_NODO WHERE ID_NODO = ".$id_nodo;
    //Conexiones unidad
    $sql_arista_unidad = "SELECT * FROM ARISTA_UNIDAD WHERE ID_NODO = ".$id_nodo;

    try{
        $db = new db();
        $db = $db->connect();

        //Conexiones camino
        $stmt_arista_nodo = $db->query($sql_arista_nodo);
        $datos_arista_nodo = $stmt_arista_nodo->fetchAll(PDO::FETCH_OBJ);
        //Conexiones entidad
        $stmt_arista_unidad = $db->query($sql_arista_unidad);
        $datos_arista_unidad = $stmt_arista_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();
        //Conexiones camino
        $longitud_arista_nodo = count($datos_arista_nodo);
        //Conexiones camino
        $longitud_arista_unidad = count($datos_arista_unidad);

        //Conexiones camino
        for($i=0; $i<$longitud_arista_nodo; $i++){
            $object = (object) array('class' => 'Nodo', 'origen' => $datos_arista_nodo[$i]->ID_NODO, 'destino' => $datos_arista_nodo[$i]->NOD_ID_NODO);
            array_push($conexiones, $object);
        }

        //Conexiones entidad
        for($i=0; $i<$longitud_arista_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_arista_unidad[$i]->ID_NODO, 'destino' => $datos_arista_unidad[$i]->ID_UNIDAD);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}

function aristaUnidadNodo ($id_unidad){
    //Conexiones unidad
    $sql_arista_unidad = "SELECT * FROM ARISTA_UNIDAD WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Conexiones unidad
        $stmt_arista_unidad = $db->query($sql_arista_unidad);
        $datos_arista_unidad = $stmt_arista_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Conexiones unidad
        $longitud_arista_unidad = count($datos_arista_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_arista_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_arista_unidad[$i]->ID_UNIDAD, 'destino' => $datos_arista_unidad[$i]->ID_NODO);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}

function personasUnidad ($id_unidad){
    //Personas unidad
    $sql_personas_unidad = "SELECT * FROM PERSONA WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Personas unidad
        $stmt_personas_unidad = $db->query($sql_personas_unidad);
        $datos_personas_unidad = $stmt_personas_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Personas unidad
        $longitud_personas_unidad = count($datos_personas_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_personas_unidad; $i++){
            $object = (object) array('class' => 'Persona', 'id' => $datos_personas_unidad[$i]->ID_PERSONA, 'nombre' => $datos_personas_unidad[$i]->PRIMER_NOMBRE_PERSONA." ".$datos_personas_unidad[$i]->SEGUNDO_NOMBRE_PERSONA." ".$datos_personas_unidad[$i]->PRIMER_APELLIDO_PERSONA." ".$datos_personas_unidad[$i]->SEGUNDO_APELLIDO_PERSONA, 'cargo' => $datos_personas_unidad[$i]->CARGO_PERSONA, 'correo' => $datos_personas_unidad[$i]->CORREO_PERSONA, 'fono' => $datos_personas_unidad[$i]->FONO_PERSONA);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}

/**
 * geo.json correspondiente a la base de datos 9
 * http://localhost/tesis/austral_api/public/index.php/v1/geo9.json
 */
/*$app->get('/v1/geo9.json', function(Request $request, Response $response){
    //Campus points
    $sql_campus = "SELECT * FROM CAMPUS ORDER BY ID_CAMPUS ASC";
    //Unidad points
    $sql_unidad = "SELECT * FROM UNIDAD ORDER BY ID_UNIDAD ASC";
    //Camino points
    $sql_camino = "SELECT * FROM CAMINO ORDER BY ID_CAMINO ASC";
    //Conexion camino linestrings
    $sql_conexion_camino = "SELECT A.ID_CAMINO, A.LATITUD_CAMINO, A.LONGITUD_CAMINO, B.ID_CAMINO AS ID_CAMINO_B, B.LATITUD_CAMINO AS LATITUD_CAMINO_B, B.LONGITUD_CAMINO AS LONGITUD_CAMINO_B FROM CAMINO AS A INNER JOIN CONEXION_CAMINO ON A.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS B ON CONEXION_CAMINO.CAM_ID_CAMINO = B.ID_CAMINO";
    //Conexion entidad linestrings
    $sql_conexion_unidad = "SELECT A.ID_UNIDAD, A.LATITUD_UNIDAD, A.LONGITUD_UNIDAD, B.ID_CAMINO, B.LATITUD_CAMINO, B.LONGITUD_CAMINO FROM UNIDAD AS A INNER JOIN CONEXION_UNIDAD ON A.ID_UNIDAD = CONEXION_UNIDAD.ID_UNIDAD INNER JOIN CAMINO AS B ON CONEXION_UNIDAD.ID_CAMINO = B.ID_CAMINO";
    //Personas por unidades
    //$sql_persona_unidad = "SELECT * FROM PERSONA INNER JOIN UNIDAD ON PERSONA.ID_UNIDAD = UNIDAD.ID_UNIDAD";

    try{
        $db = new db();
        $db = $db->connect();
        
        //Campus points
        $stmt_campus = $db->query($sql_campus);
        $datos_campus = $stmt_campus->fetchAll(PDO::FETCH_OBJ);
        //Unidad points
        $stmt_unidad = $db->query($sql_unidad);
        $datos_unidad = $stmt_unidad->fetchAll(PDO::FETCH_OBJ);
        //Camino points
        $stmt_camino = $db->query($sql_camino);
        $datos_camino = $stmt_camino->fetchAll(PDO::FETCH_OBJ);
        //Conexion camino linestrings
        $stmt_conexion_camino = $db->query($sql_conexion_camino);
        $datos_conexion_camino = $stmt_conexion_camino->fetchAll(PDO::FETCH_OBJ);
        //Conexion unidad linestrings
        $stmt_conexion_unidad = $db->query($sql_conexion_unidad);
        $datos_conexion_unidad = $stmt_conexion_unidad->fetchAll(PDO::FETCH_OBJ);
        //Personas por unidades
        //$stmt_persona_unidad = $db->query($sql_persona_unidad);
        //$datos_persona_unidad = $stmt_persona_unidad->fetchAll(PDO::FETCH_OBJ);}

        $db = null;
        $features = array();
        //Campus points
        $longitud_campus = count($datos_campus);
        //Unidad points
        $longitud_unidad = count($datos_unidad);
        //Camino points
        $longitud_camino = count($datos_camino);
        //Conexion camino linestrings
        $longitud_conexion_camino = count($datos_conexion_camino);
        //Conexion unidad linestrings
        $longitud_conexion_unidad = count($datos_conexion_unidad);
        //Personas por unidades
        //$longitud_persona_unidad = count($datos_persona_unidad);

        //Campus points
        for($i=0; $i<$longitud_campus; $i++) {
            $properties = (object) array('class' => 'Campus', 'id' => $datos_campus[$i]->ID_CAMPUS, 'nombre' => $datos_campus[$i]->NOMBRE_CAMPUS, 'direccion' => $datos_campus[$i]->DIRECCION_CAMPUS);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_campus[$i]->LONGITUD_CAMPUS, $datos_campus[$i]->LATITUD_CAMPUS));

            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            
            array_push($features, $object);
        }

        //Unidad points
        for($i=0; $i<$longitud_unidad; $i++) {
            $properties = (object) array('class' => 'Unidad','id' => $datos_unidad[$i]->ID_UNIDAD, 'nombre' => $datos_unidad[$i]->NOMBRE_UNIDAD, 'descripcion' => $datos_unidad[$i]->DESCRIPCION_UNIDAD);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_unidad[$i]->LONGITUD_UNIDAD, $datos_unidad[$i]->LATITUD_UNIDAD));

            //$object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            //conexiones
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionUnidadCamino($datos_unidad[$i]->ID_UNIDAD), 'personas' => personasUnidad($datos_unidad[$i]->ID_UNIDAD));
            //$object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionUnidadCamino($datos_unidad[$i]->ID_UNIDAD));
            
            array_push($features, $object);
        }

        //Camino points
        for($i=0; $i<$longitud_camino; $i++) {
            $properties = (object) array('class' => 'Camino', 'id' => $datos_camino[$i]->ID_CAMINO);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_camino[$i]->LONGITUD_CAMINO, $datos_camino[$i]->LATITUD_CAMINO));

            //$object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            //conexiones
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionCaminoCamino($datos_camino[$i]->ID_CAMINO));
            
            array_push($features, $object);
        }

        //Conexion camino linestrings
        for($i=0; $i<$longitud_conexion_camino; $i++) {
            $geometry = (object) array('type' => 'LineString', 'coordinates' => array([$datos_conexion_camino[$i]->LONGITUD_CAMINO, $datos_conexion_camino[$i]->LATITUD_CAMINO], [$datos_conexion_camino[$i]->LONGITUD_CAMINO_B, $datos_conexion_camino[$i]->LATITUD_CAMINO_B]));

            $object = (object) array('type' => 'Feature', 'properties' => '', 'geometry' => $geometry);
            
            array_push($features, $object);
        }

        //Conexion unidad linestrings
        for($i=0; $i<$longitud_conexion_unidad; $i++) {
            $geometry = (object) array('type' => 'LineString', 'coordinates' => array([$datos_conexion_unidad[$i]->LONGITUD_UNIDAD, $datos_conexion_unidad[$i]->LATITUD_UNIDAD], [$datos_conexion_unidad[$i]->LONGITUD_CAMINO, $datos_conexion_unidad[$i]->LATITUD_CAMINO]));

            $object = (object) array('type' => 'Feature', 'properties' => '', 'geometry' => $geometry);
            
            array_push($features, $object);
        }

        $json_features = json_encode($features, JSON_UNESCAPED_UNICODE);

        $json_geo = '{
            "type": "FeatureCollection",
            "features":'.$json_features.
        '}';
        
        echo $json_geo;
    } 
    catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

function conexionCaminoCamino ($id_camino){
    //Conexiones camino
    $sql_conexion_camino = "SELECT * FROM CONEXION_CAMINO WHERE ID_CAMINO = ".$id_camino;
    //Conexiones entidad
    $sql_conexion_unidad = "SELECT * FROM CONEXION_UNIDAD WHERE ID_CAMINO = ".$id_camino;

    try{
        $db = new db();
        $db = $db->connect();

        //Conexiones camino
        $stmt_conexion_camino = $db->query($sql_conexion_camino);
        $datos_conexion_camino = $stmt_conexion_camino->fetchAll(PDO::FETCH_OBJ);
        //Conexiones entidad
        $stmt_conexion_unidad = $db->query($sql_conexion_unidad);
        $datos_conexion_unidad = $stmt_conexion_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();
        //Conexiones camino
        $longitud_conexion_camino = count($datos_conexion_camino);
        //Conexiones camino
        $longitud_conexion_unidad = count($datos_conexion_unidad);

        //Conexiones camino
        for($i=0; $i<$longitud_conexion_camino; $i++){
            $object = (object) array('class' => 'Camino', 'origen' => $datos_conexion_camino[$i]->ID_CAMINO, 'destino' => $datos_conexion_camino[$i]->CAM_ID_CAMINO);
            array_push($conexiones, $object);
        }

        //Conexiones entidad
        for($i=0; $i<$longitud_conexion_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_conexion_unidad[$i]->ID_CAMINO, 'destino' => $datos_conexion_unidad[$i]->ID_UNIDAD);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}

function conexionUnidadCamino ($id_unidad){
    //Conexiones unidad
    $sql_conexion_unidad = "SELECT * FROM CONEXION_UNIDAD WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Conexiones unidad
        $stmt_conexion_unidad = $db->query($sql_conexion_unidad);
        $datos_conexion_unidad = $stmt_conexion_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Conexiones unidad
        $longitud_conexion_unidad = count($datos_conexion_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_conexion_unidad; $i++){
            $object = (object) array('class' => 'Unidad', 'origen' => $datos_conexion_unidad[$i]->ID_UNIDAD, 'destino' => $datos_conexion_unidad[$i]->ID_CAMINO);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}


function personasUnidad ($id_unidad){
    //Personas unidad
    $sql_personas_unidad = "SELECT * FROM PERSONA WHERE ID_UNIDAD = ".$id_unidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Personas unidad
        $stmt_personas_unidad = $db->query($sql_personas_unidad);
        $datos_personas_unidad = $stmt_personas_unidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        $conexiones = array();

        //Personas unidad
        $longitud_personas_unidad = count($datos_personas_unidad);

        //Conexiones unidad
        for($i=0; $i<$longitud_personas_unidad; $i++){
            $object = (object) array('class' => 'Persona', 'id' => $datos_personas_unidad[$i]->ID_PERSONA, 'nombre' => $datos_personas_unidad[$i]->PRIMER_NOMBRE_PERSONA." ".$datos_personas_unidad[$i]->SEGUNDO_NOMBRE_PERSONA." ".$datos_personas_unidad[$i]->PRIMER_APELLIDO_PERSONA." ".$datos_personas_unidad[$i]->SEGUNDO_APELLIDO_PERSONA, 'cargo' => $datos_personas_unidad[$i]->CARGO_PERSONA, 'correo' => $datos_personas_unidad[$i]->CORREO_PERSONA, 'fono' => $datos_personas_unidad[$i]->FONO_PERSONA);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/