<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Content-Type', 'application/json; charset=UTF-8')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET');
});

/**
 * get all campus
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

        /*$json_campus = json_encode($campus, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        $json = '{
            "total_campus": '.$datos_total_campus[0]->TOTAL_CAMPUS.',
            "campus":'.$json_campus.'
        }';
        
        echo $json;*/

        $final_object = (object) array("total_campus" => $datos_total_campus[0]->TOTAL_CAMPUS, "campus" => $campus);
        $json = json_encode($final_object, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        
        echo $json."\n";

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * get all unidades con sus conexiones a nodos by id campus
 */
$app->get('/v1/campus/{id_campus}', function(Request $request, Response $response){
    $id_campus = $request->getAttribute('id_campus');
    $sql_unidades = "SELECT * FROM UNIDAD WHERE ID_CAMPUS = '$id_campus'";

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

        //$json = json_encode($unidades, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        //echo $json;

        //$final_object = (object) array("total_campus" => $datos_total_campus[0]->TOTAL_CAMPUS, "campus" => $campus);
        $json = json_encode($unidades, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        
        echo $json."\n";

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * get single unidad con all nodos y sus conexiones by id campus
 */
$app->get('/v1/campus/{id_campus}/unidad/{id_unidad}', function(Request $request, Response $response){
    $id_unidad = $request->getAttribute('id_unidad');
    $sql_unidad = "SELECT * FROM UNIDAD WHERE ID_UNIDAD = '$id_unidad'";
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

        $final_object = (object) array();

        for($i=0; $i<$longitud_unidad; $i++) {
            $final_object = (object) array("id_unidad" => $datos_unidad[0]->ID_UNIDAD, "id_campus" => $datos_unidad[0]->ID_CAMPUS, "nombre_unidad" => $datos_unidad[0]->NOMBRE_UNIDAD, "descripcion_unidad" => $datos_unidad[0]->DESCRIPCION_UNIDAD, "latitud_unidad" => $datos_unidad[0]->LATITUD_UNIDAD, "longitud_unidad" => $datos_unidad[0]->LONGITUD_UNIDAD, 'conexiones' => conexionUnidadNodo($datos_unidad[$i]->ID_UNIDAD), 'nodos' => $nodos);
        }

        //$json = json_encode($object_final, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        //echo $json;

        //$final_object = (object) array("total_campus" => $datos_total_campus[0]->TOTAL_CAMPUS, "campus" => $campus);
        $json = json_encode($final_object, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        
        echo $json."\n";

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * función que entrega las conexiones de un nodo
 */
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
    
        return $conexiones;

    } catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}

/**
 * función que entrega las conexiones de una unidad
 */
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
 * get all personas por campus
 */
$app->get('/v1/campus/{id_campus}/persona/', function(Request $request, Response $response){
    $param_campus = $request->getAttribute('id_campus');
    $sql_persona = "SELECT C.ID_CAMPUS, B.ID_UNIDAD, B.NOMBRE_UNIDAD, B.LATITUD_UNIDAD, B.LONGITUD_UNIDAD, A.ID_PERSONA, A.PRIMER_NOMBRE_PERSONA, A.SEGUNDO_NOMBRE_PERSONA, A.PRIMER_APELLIDO_PERSONA, A.SEGUNDO_APELLIDO_PERSONA, A.CARGO_PERSONA, A.CORREO_PERSONA, A.FONO_PERSONA FROM PERSONA AS A INNER JOIN UNIDAD AS B ON A.ID_UNIDAD = B.ID_UNIDAD INNER JOIN CAMPUS AS C ON C.ID_CAMPUS = B.ID_CAMPUS WHERE C.ID_CAMPUS = '$param_campus' ORDER BY A.PRIMER_NOMBRE_PERSONA ASC";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_persona = $db->query($sql_persona);
        $datos_persona = $stmt_persona->fetchAll(PDO::FETCH_OBJ);
        $longitud_persona = count($datos_persona);

        $db = null;

        $persona = array();

        for($i=0; $i<$longitud_persona; $i++) {
            $object = (object) array("id_persona" => $datos_persona[$i]->ID_PERSONA, "id_unidad" => $datos_persona[$i]->ID_UNIDAD, "nombre_unidad" => $datos_persona[$i]->NOMBRE_UNIDAD, "latitud_unidad" => $datos_persona[$i]->LATITUD_UNIDAD, "longitud_unidad" => $datos_persona[$i]->LONGITUD_UNIDAD, "nombre_persona" => $datos_persona[$i]->PRIMER_NOMBRE_PERSONA." ".$datos_persona[$i]->PRIMER_APELLIDO_PERSONA, "primer_nombre_persona" => $datos_persona[$i]->PRIMER_NOMBRE_PERSONA, "segundo_nombre_persona" => $datos_persona[$i]->SEGUNDO_NOMBRE_PERSONA, "primer_apellido_persona" => $datos_persona[$i]->PRIMER_APELLIDO_PERSONA, "segundo_apellido_persona" => $datos_persona[$i]->SEGUNDO_APELLIDO_PERSONA, "cargo_persona" => $datos_persona[$i]->CARGO_PERSONA, "correo_persona" => $datos_persona[$i]->CORREO_PERSONA, "fono_persona" => $datos_persona[$i]->FONO_PERSONA);
            array_push($persona, $object);
        }

        /*$json_persona = json_encode($persona, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json_persona;*/

        //$final_object = (object) array("total_campus" => $datos_total_campus[0]->TOTAL_CAMPUS, "campus" => $campus);
        $json = json_encode($persona, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        
        echo $json."\n";

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * get all personas por campus filtradas por nombre y/o apellido
 */
$app->get('/v1/campus/{id_campus}/persona/{nombre_persona}', function(Request $request, Response $response){
    $param_campus = $request->getAttribute('id_campus');
    $param_nombre = $request->getAttribute('nombre_persona');
    $sql_persona = "SELECT C.ID_CAMPUS, B.ID_UNIDAD, B.NOMBRE_UNIDAD, B.LATITUD_UNIDAD, B.LONGITUD_UNIDAD, A.ID_PERSONA, A.PRIMER_NOMBRE_PERSONA, A.SEGUNDO_NOMBRE_PERSONA, A.PRIMER_APELLIDO_PERSONA, A.SEGUNDO_APELLIDO_PERSONA, A.CARGO_PERSONA, A.CORREO_PERSONA, A.FONO_PERSONA FROM PERSONA AS A INNER JOIN UNIDAD AS B ON A.ID_UNIDAD = B.ID_UNIDAD INNER JOIN CAMPUS AS C ON C.ID_CAMPUS = B.ID_CAMPUS WHERE C.ID_CAMPUS = '$param_campus' and CONCAT(A.PRIMER_NOMBRE_PERSONA, ' ', A.PRIMER_APELLIDO_PERSONA) LIKE '%$param_nombre%' ORDER BY A.PRIMER_NOMBRE_PERSONA ASC";

    try{
        $db = new db();
        $db = $db->connect();

        $stmt_persona = $db->query($sql_persona);
        $datos_persona = $stmt_persona->fetchAll(PDO::FETCH_OBJ);
        $longitud_persona = count($datos_persona);

        $db = null;

        $persona = array();

        for($i=0; $i<$longitud_persona; $i++) {
            $object = (object) array("id_persona" => $datos_persona[$i]->ID_PERSONA, "id_unidad" => $datos_persona[$i]->ID_UNIDAD, "nombre_unidad" => $datos_persona[$i]->NOMBRE_UNIDAD, "latitud_unidad" => $datos_persona[$i]->LATITUD_UNIDAD, "longitud_unidad" => $datos_persona[$i]->LONGITUD_UNIDAD, "nombre_persona" => $datos_persona[$i]->PRIMER_NOMBRE_PERSONA." ".$datos_persona[$i]->PRIMER_APELLIDO_PERSONA, "primer_nombre_persona" => $datos_persona[$i]->PRIMER_NOMBRE_PERSONA, "segundo_nombre_persona" => $datos_persona[$i]->SEGUNDO_NOMBRE_PERSONA, "primer_apellido_persona" => $datos_persona[$i]->PRIMER_APELLIDO_PERSONA, "segundo_apellido_persona" => $datos_persona[$i]->SEGUNDO_APELLIDO_PERSONA, "cargo_persona" => $datos_persona[$i]->CARGO_PERSONA, "correo_persona" => $datos_persona[$i]->CORREO_PERSONA, "fono_persona" => $datos_persona[$i]->FONO_PERSONA);
            array_push($persona, $object);
        }

        /*$json_persona = json_encode($persona, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        
        echo $json_persona;*/

        //$final_object = (object) array("total_campus" => $datos_total_campus[0]->TOTAL_CAMPUS, "campus" => $campus);
        $json = json_encode($persona, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        
        echo $json."\n";

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

/**
 * get geo.json de toda la base de datos
 */
$app->get('/v1/data.geojson', function(Request $request, Response $response){
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

        /*$json_features = json_encode($features, JSON_UNESCAPED_UNICODE);

        $json_geo = '{
            "type": "FeatureCollection",
            "features":'.$json_features.
        '}';
        
        echo $json_geo;*/

        $final_object = (object) array("type" => "FeatureCollection", "features" => $features);
        $json = json_encode($final_object, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        echo $json."\n";
    } 
    catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

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