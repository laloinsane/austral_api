<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

/** 
 * geo.json correspondiente a la base de datos 9
 * http://localhost/tesis/austral_api/public/index.php/api/geo9.json
 */
$app->get('/api/geo9.json', function(Request $request, Response $response){
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
}


/** 
 * geo.json correspondiente a la base de datos 7
 * http://localhost/tesis/austral_api/public/index.php/api/geo7.json
 */
/*$app->get('/api/geo7.json', function(Request $request, Response $response){
    //Establecimientos points
    $sql_establecimientos = "SELECT A.ID_ESTABLECIMIENTO, A.NOMBRE_ESTABLECIMIENTO, A.DIRECCION_ESTABLECIMIENTO, A.LATITUD_ESTABLECIMIENTO, A.LONGITUD_ESTABLECIMIENTO, B.NOMBRE_TIPO_ESTABLECIMIENTO FROM ESTABLECIMIENTO AS A INNER JOIN TIPO_ESTABLECIMIENTO AS B ON A.ID_TIPO_ESTABLECIMIENTO=B.ID_TIPO_ESTABLECIMIENTO ORDER BY ID_ESTABLECIMIENTO ASC";

    //Entidad points
    $sql_entidad = "SELECT A.ID_ENTIDAD, A.ID_ESTABLECIMIENTO, A.ID_INFRAESTRUCTURA, B.NOMBRE_INFRAESTRUCTURA, B.COLOR_INFRAESTRUCTURA, A.NOMBRE_ENTIDAD, A.DESCRIPCION_ENTIDAD, A.LATITUD_ENTIDAD, A.LONGITUD_ENTIDAD FROM ENTIDAD AS A INNER JOIN INFRAESTRUCTURA AS B ON A.ID_INFRAESTRUCTURA = B.ID_INFRAESTRUCTURA ORDER BY ID_ENTIDAD ASC";

    //Camino points
    $sql_camino = "SELECT * FROM CAMINO ORDER BY ID_CAMINO ASC";

    //Conexion camino linestrings
    $sql_conexion_camino = "SELECT A.ID_CAMINO, A.LATITUD_CAMINO, A.LONGITUD_CAMINO, B.ID_CAMINO AS ID_CAMINO_B, B.LATITUD_CAMINO AS LATITUD_CAMINO_B, B.LONGITUD_CAMINO AS LONGITUD_CAMINO_B FROM CAMINO AS A INNER JOIN CONEXION_CAMINO ON A.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS B ON CONEXION_CAMINO.CAM_ID_CAMINO = B.ID_CAMINO";

    //Conexion entidad linestrings
    $sql_conexion_entidad = "SELECT A.ID_ENTIDAD, A.LATITUD_ENTIDAD, A.LONGITUD_ENTIDAD, B.ID_CAMINO, B.LATITUD_CAMINO, B.LONGITUD_CAMINO FROM ENTIDAD AS A INNER JOIN CONEXION_ENTIDAD ON A.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO AS B ON CONEXION_ENTIDAD.ID_CAMINO = B.ID_CAMINO";

    try{
        $db = new db();
        $db = $db->connect();
        
        //Establecimientos points
        $stmt_establecimientos = $db->query($sql_establecimientos);
        $datos_establecimientos = $stmt_establecimientos->fetchAll(PDO::FETCH_OBJ);

        //Entidad points
        $stmt_entidad = $db->query($sql_entidad);
        $datos_entidad = $stmt_entidad->fetchAll(PDO::FETCH_OBJ);

        //Camino points
        $stmt_camino = $db->query($sql_camino);
        $datos_camino = $stmt_camino->fetchAll(PDO::FETCH_OBJ);

        //Conexion camino linestrings
        $stmt_conexion_camino = $db->query($sql_conexion_camino);
        $datos_conexion_camino = $stmt_conexion_camino->fetchAll(PDO::FETCH_OBJ);

        //Conexion entidad linestrings
        $stmt_conexion_entidad = $db->query($sql_conexion_entidad);
        $datos_conexion_entidad = $stmt_conexion_entidad->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        $features = array();

        //Establecimientos points
        $longitud_establecimientos = count($datos_establecimientos);
        
        //Entidad points
        $longitud_entidad = count($datos_entidad);

        //Camino points
        $longitud_camino = count($datos_camino);

        //Conexion camino linestrings
        $longitud_conexion_camino = count($datos_conexion_camino);

        //Conexion entidad linestrings
        $longitud_conexion_entidad = count($datos_conexion_entidad);

        //Establecimientos points
        for($i=0; $i<$longitud_establecimientos; $i++) {
            $properties = (object) array('class' => 'Establecimiento', 'id' => $datos_establecimientos[$i]->ID_ESTABLECIMIENTO,  'tipo' => $datos_establecimientos[$i]->NOMBRE_TIPO_ESTABLECIMIENTO, 'nombre' => $datos_establecimientos[$i]->NOMBRE_ESTABLECIMIENTO, 'direccion' => $datos_establecimientos[$i]->DIRECCION_ESTABLECIMIENTO);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_establecimientos[$i]->LONGITUD_ESTABLECIMIENTO, $datos_establecimientos[$i]->LATITUD_ESTABLECIMIENTO));

            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            
            array_push($features, $object);
        }

        //Entidad points
        for($i=0; $i<$longitud_entidad; $i++) {
            $properties = (object) array('class' => 'Entidad','id' => $datos_entidad[$i]->ID_ENTIDAD, 'nombre' => $datos_entidad[$i]->NOMBRE_ENTIDAD, 'descripcion' => $datos_entidad[$i]->DESCRIPCION_ENTIDAD, 'nombre_infraestructura' => $datos_entidad[$i]->NOMBRE_INFRAESTRUCTURA, 'color_infraestructura' => $datos_entidad[$i]->COLOR_INFRAESTRUCTURA);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_entidad[$i]->LONGITUD_ENTIDAD, $datos_entidad[$i]->LATITUD_ENTIDAD));

            //$object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            //conexiones
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionEntidadCamino($datos_entidad[$i]->ID_ENTIDAD));
            
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

        //Conexion entidad linestrings
        for($i=0; $i<$longitud_conexion_entidad; $i++) {
            $geometry = (object) array('type' => 'LineString', 'coordinates' => array([$datos_conexion_entidad[$i]->LONGITUD_ENTIDAD, $datos_conexion_entidad[$i]->LATITUD_ENTIDAD], [$datos_conexion_entidad[$i]->LONGITUD_CAMINO, $datos_conexion_entidad[$i]->LATITUD_CAMINO]));

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
});*/



// Api utilizada por la app
// Get All Entidades
// http://localhost/tesis/austral_api/public/index.php/api/entidades
$app->get('/api/entidades', function(Request $request, Response $response){
    $sql1 = "SELECT * FROM ENTIDAD ORDER BY ID_ENTIDAD ASC";

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

// geojson
// http://localhost/tesis/austral_api/public/index.php/api/geo.json
/*$app->get('/api/geo.json', function(Request $request, Response $response){

    //Sede points
    $sql_sede = "SELECT * FROM SEDE ORDER BY ID_SEDE ASC";

    //Entidad points
    $sql_entidad = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.ID_SEDE, ENTIDAD.ID_INFRAESTRUCTURA, INFRAESTRUCTURA.NOMBRE_INFRAESTRUNCTURA, INFRAESTRUCTURA.COLOR_INFRAESTRUCTURA, ENTIDAD.NOMBRE_ENTIDAD, ENTIDAD.DESCRIPCION_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD FROM ENTIDAD INNER JOIN INFRAESTRUCTURA ON ENTIDAD.ID_INFRAESTRUCTURA = INFRAESTRUCTURA.ID_INFRAESTRUCTURA ORDER BY ID_ENTIDAD ASC";
    //$sql_entidad = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.ID_SEDE, SEDE.NOMBRE_SEDE, SEDE.DIRECCION_SEDE, ENTIDAD.ID_INFRAESTRUCTURA, INFRAESTRUCTURA.NOMBRE_INFRAESTRUNCTURA, INFRAESTRUCTURA.COLOR_INFRAESTRUCTURA, ENTIDAD.NOMBRE_ENTIDAD, ENTIDAD.DESCRIPCION_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD FROM ENTIDAD INNER JOIN INFRAESTRUCTURA ON ENTIDAD.ID_INFRAESTRUCTURA = INFRAESTRUCTURA.ID_INFRAESTRUCTURA INNER JOIN SEDE ON ENTIDAD.ID_SEDE = SEDE.ID_SEDE ORDER BY ID_ENTIDAD ASC";

    $sql2 = "SELECT * FROM CAMINO ORDER BY ID_CAMINO ASC";
    
    $sql3 = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD, CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO FROM ENTIDAD INNER JOIN CONEXION_ENTIDAD ON ENTIDAD.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO ON CONEXION_ENTIDAD.ID_CAMINO = CAMINO.ID_CAMINO";

    $sql4 = "SELECT CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO, p.ID_CAMINO AS p_ID_CAMINO, p.LATITUD_CAMINO AS p_LATITUD_CAMINO, p.LONGITUD_CAMINO AS p_LONGITUD_CAMINO FROM CAMINO INNER JOIN CONEXION_CAMINO ON CAMINO.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS p ON CONEXION_CAMINO.CAM_ID_CAMINO = p.ID_CAMINO";

    try{
        $db = new db();
        $db = $db->connect();

        //Sede points
        $stmt_sede = $db->query($sql_sede);
        $datos_sede = $stmt_sede->fetchAll(PDO::FETCH_OBJ);
        //Entidad points
        $stmt_entidad = $db->query($sql_entidad);
        $datos_entidad = $stmt_entidad->fetchAll(PDO::FETCH_OBJ);

        $stmt2 = $db->query($sql2);
        $datos2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
        $stmt3 = $db->query($sql3);
        $datos3 = $stmt3->fetchAll(PDO::FETCH_OBJ);
        $stmt4 = $db->query($sql4);
        $datos4 = $stmt4->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        $features = array();

        //Sede points
        $longitud_sede = count($datos_sede);
        //Entidad points
        $longitud_entidad = count($datos_entidad);

        $longitud2 = count($datos2);
        $longitud3 = count($datos3);
        $longitud4 = count($datos4);

        //Sede points
        for($i=0; $i<$longitud_sede; $i++) {
            $properties = (object) array('class' => 'Sede', 'id' => $datos_sede[$i]->ID_SEDE, 'nombre' => $datos_sede[$i]->NOMBRE_SEDE, 'direccion' => $datos_sede[$i]->DIRECCION_SEDE);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_sede[$i]->LONGITUD_SEDE, $datos_sede[$i]->LATITUD_SEDE));

            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            
            array_push($features, $object);
        }
        //Entidad points
        for($i=0; $i<$longitud_entidad; $i++) {
            $properties = (object) array('class' => 'Entidad','id' => $datos_entidad[$i]->ID_ENTIDAD, 'nombre' => $datos_entidad[$i]->NOMBRE_ENTIDAD, 'descripcion' => $datos_entidad[$i]->DESCRIPCION_ENTIDAD, 'nombre_estructura' => $datos_entidad[$i]->NOMBRE_INFRAESTRUNCTURA, 'color_estructura' => $datos_entidad[$i]->COLOR_INFRAESTRUCTURA);
            $geometry = (object) array('type' => 'Point', 'coordinates' => array($datos_entidad[$i]->LONGITUD_ENTIDAD, $datos_entidad[$i]->LATITUD_ENTIDAD));

            //$object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry);
            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionEntidadCamino($datos1[$i]->ID_ENTIDAD));
            
            array_push($features, $object);
        }



        for($i=0; $i<$longitud2; $i++) {
            $properties = (object) array('class' => 'Camino', 'id' => $datos2[$i]->ID_CAMINO);
            $coordenadas = array();
            array_push($coordenadas, $datos2[$i]->LONGITUD_CAMINO, $datos2[$i]->LATITUD_CAMINO);
            $geometry = (object) array('type' => 'Point', 'coordinates' => $coordenadas);

            $object = (object) array('type' => 'Feature', 'properties' => $properties, 'geometry' => $geometry, 'conexiones' => conexionCaminoCamino($datos2[$i]->ID_CAMINO));
            array_push($features, $object);
        }

        for($i=0; $i<$longitud3; $i++)
        {
            $coordenadas = array();
            array_push($coordenadas, [$datos3[$i]->LONGITUD_ENTIDAD, $datos3[$i]->LATITUD_ENTIDAD], [$datos3[$i]->LONGITUD_CAMINO, $datos3[$i]->LATITUD_CAMINO]);
            //$coordenadas1 = array();
            //array_push($coordenadas1, $datos3[$i]->LONGITUD_CAMINO, $datos3[$i]->LATITUD_CAMINO);

            $a = (object) array($coordenadas, $coordenadas1);

            $myArray222 = (object) array('type' => 'LineString', 'coordinates' => $coordenadas);
            $myArray3 = (object) array("class" => 'Conexion camino entidad', 'to' => $datos3[$i]->ID_ENTIDAD);
            $myArray111 = (object) array('type' => 'Feature', 'properties' => $myArray3, 'geometry' => $myArray222);
            array_push($features, $myArray111);

            //array_push($features, $myArray1);
            //saco el valor de cada elemento
            //echo $datos1[$i]->NOMBRE_ENTIDAD;
            //echo "<br>";
        }
        for($i=0; $i<$longitud4; $i++)
        {
            $coordenadas = array();
            array_push($coordenadas, [$datos4[$i]->LONGITUD_CAMINO, $datos4[$i]->LATITUD_CAMINO], [$datos4[$i]->p_LONGITUD_CAMINO, $datos4[$i]->p_LATITUD_CAMINO]);
            //$coordenadas1 = array();
            //array_push($coordenadas1, $datos3[$i]->LONGITUD_CAMINO, $datos3[$i]->LATITUD_CAMINO);

            $a = (object) array($coordenadas, $coordenadas1);

            $myArray2222 = (object) array('type' => 'LineString', 'coordinates' => $coordenadas);
            //$myArray3 = (object) array("nombre" => $datos1[$i]->NOMBRE_ENTIDAD, "descripcion" => $datos1[$i]->DESCRIPCION_ENTIDAD);
            $myArray1111 = (object) array('type' => 'Feature', 'properties' => '', 'geometry' => $myArray2222);
            array_push($features, $myArray1111);

            //array_push($features, $myArray1);
            //saco el valor de cada elemento
            //echo $datos1[$i]->NOMBRE_ENTIDAD;
            //echo "<br>";
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
});*/

//$sql2 = "SELECT count(ID_ENTIDAD) AS TOTAL FROM ENTIDAD";
/*
function conexionEntidadCamino ($id_entidad){
    $sql = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD, CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO FROM ENTIDAD INNER JOIN CONEXION_ENTIDAD ON ENTIDAD.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO ON CONEXION_ENTIDAD.ID_CAMINO = CAMINO.ID_CAMINO WHERE ENTIDAD.ID_ENTIDAD = ".$id_entidad;

    try{
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query($sql);
        $datos = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $conexiones = array();
        $longitud = count($datos);

        for($i=0; $i<$longitud; $i++){
            $conexion = (object) array('class' => 'Entidad - Camino', 'id_camino' => $datos[$i]->ID_CAMINO);
            array_push($conexiones, $conexion);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/

/*function conexionCaminoCamino ($id_camino){
    $sql1 = "SELECT CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO, p.ID_CAMINO AS p_ID_CAMINO, p.LATITUD_CAMINO AS p_LATITUD_CAMINO, p.LONGITUD_CAMINO AS p_LONGITUD_CAMINO FROM CAMINO INNER JOIN CONEXION_CAMINO ON CAMINO.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS p ON CONEXION_CAMINO.CAM_ID_CAMINO = p.ID_CAMINO WHERE CAMINO.ID_CAMINO = ".$id_camino;
    $sql2 = "SELECT CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO, p.ID_CAMINO AS p_ID_CAMINO, p.LATITUD_CAMINO AS p_LATITUD_CAMINO, p.LONGITUD_CAMINO AS p_LONGITUD_CAMINO FROM CAMINO INNER JOIN CONEXION_CAMINO ON CAMINO.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS p ON CONEXION_CAMINO.CAM_ID_CAMINO = p.ID_CAMINO WHERE p.ID_CAMINO = ".$id_camino;
    $sql3 = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD, CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO FROM ENTIDAD INNER JOIN CONEXION_ENTIDAD ON ENTIDAD.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO ON CONEXION_ENTIDAD.ID_CAMINO = CAMINO.ID_CAMINO WHERE CAMINO.ID_CAMINO = ".$id_camino;

    try{
        $db = new db();
        $db = $db->connect();
        $stmt1 = $db->query($sql1);
        $datos1 = $stmt1->fetchAll(PDO::FETCH_OBJ);
        $stmt2 = $db->query($sql2);
        $datos2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
        $stmt3 = $db->query($sql3);
        $datos3 = $stmt3->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $conexiones = array();
        $longitud1 = count($datos1);
        $longitud2 = count($datos2);
        $longitud3 = count($datos3);

        for($i=0; $i<$longitud1; $i++){
            $conexion1 = (object) array('class' => 'Camino1 - Camino2', 'id_camino' => $datos1[$i]->p_ID_CAMINO, 'id_inicio' => $datos1[$i]->ID_CAMINO, 'id_fin' => $datos1[$i]->p_ID_CAMINO, 'id_origen' => $datos1[$i]->ID_CAMINO, 'id_destino' => $datos1[$i]->p_ID_CAMINO);
            array_push($conexiones, $conexion1);
        }

        for($i=0; $i<$longitud2; $i++){
            $conexion2 = (object) array('class' => 'Camino2 - Camino1', 'id_camino' => $datos2[$i]->ID_CAMINO, 'id_inicio' => $datos2[$i]->p_ID_CAMINO, 'id_fin' => $datos2[$i]->ID_CAMINO, 'id_origen' => $datos2[$i]->p_ID_CAMINO, 'id_destino' => $datos2[$i]->ID_CAMINO);
            array_push($conexiones, $conexion2);
        }

        for($i=0; $i<$longitud3; $i++){
            $conexion3 = (object) array('class' => 'Camino - Entidad', 'id_camino' => $datos3[$i]->ID_ENTIDAD);
            array_push($conexiones, $conexion3);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/

/*function conexionCaminoCamino ($id_camino){

    //Conexiones camino
    $sql_conexion_camino = "SELECT * FROM CONEXION_CAMINO WHERE ID_CAMINO = ".$id_camino;

    //Conexiones entidad
    $sql_conexion_entidad = "SELECT * FROM CONEXION_ENTIDAD WHERE ID_CAMINO = ".$id_camino;

    /*$sql1 = "SELECT CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO, p.ID_CAMINO AS p_ID_CAMINO, p.LATITUD_CAMINO AS p_LATITUD_CAMINO, p.LONGITUD_CAMINO AS p_LONGITUD_CAMINO FROM CAMINO INNER JOIN CONEXION_CAMINO ON CAMINO.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS p ON CONEXION_CAMINO.CAM_ID_CAMINO = p.ID_CAMINO WHERE CAMINO.ID_CAMINO = ".$id_camino;
    $sql2 = "SELECT CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO, p.ID_CAMINO AS p_ID_CAMINO, p.LATITUD_CAMINO AS p_LATITUD_CAMINO, p.LONGITUD_CAMINO AS p_LONGITUD_CAMINO FROM CAMINO INNER JOIN CONEXION_CAMINO ON CAMINO.ID_CAMINO = CONEXION_CAMINO.ID_CAMINO INNER JOIN CAMINO AS p ON CONEXION_CAMINO.CAM_ID_CAMINO = p.ID_CAMINO WHERE p.ID_CAMINO = ".$id_camino;
    $sql3 = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD, CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO FROM ENTIDAD INNER JOIN CONEXION_ENTIDAD ON ENTIDAD.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO ON CONEXION_ENTIDAD.ID_CAMINO = CAMINO.ID_CAMINO WHERE CAMINO.ID_CAMINO = ".$id_camino;
*/
   /* try{
        $db = new db();
        $db = $db->connect();

        //Conexiones camino
        $stmt_conexion_camino = $db->query($sql_conexion_camino);
        $datos_conexion_camino = $stmt_conexion_camino->fetchAll(PDO::FETCH_OBJ);

        //Conexiones entidad
        $stmt_conexion_entidad = $db->query($sql_conexion_entidad);
        $datos_conexion_entidad = $stmt_conexion_entidad->fetchAll(PDO::FETCH_OBJ);

        /*$stmt1 = $db->query($sql1);
        $datos1 = $stmt1->fetchAll(PDO::FETCH_OBJ);
        $stmt2 = $db->query($sql2);
        $datos2 = $stmt2->fetchAll(PDO::FETCH_OBJ);
        $stmt3 = $db->query($sql3);
        $datos3 = $stmt3->fetchAll(PDO::FETCH_OBJ);
*/
     /*   $db = null;
        $conexiones = array();
        
        //Conexiones camino
        $longitud_conexion_camino = count($datos_conexion_camino);

        //Conexiones camino
        $longitud_conexion_entidad = count($datos_conexion_entidad);

        /*$longitud1 = count($datos1);
        $longitud2 = count($datos2);
        $longitud3 = count($datos3);
*/
       /* //Conexiones camino
        for($i=0; $i<$longitud_conexion_camino; $i++){
            $object = (object) array('class' => 'Camino', 'origen' => $datos_conexion_camino[$i]->ID_CAMINO, 'destino' => $datos_conexion_camino[$i]->CAM_ID_CAMINO);
            array_push($conexiones, $object);
        }

        //Conexiones entidad
        for($i=0; $i<$longitud_conexion_entidad; $i++){
            $object = (object) array('class' => 'Entidad', 'origen' => $datos_conexion_entidad[$i]->ID_CAMINO, 'destino' => $datos_conexion_entidad[$i]->ID_ENTIDAD);
            array_push($conexiones, $object);
        }

        /*for($i=0; $i<$longitud1; $i++){
            $conexion1 = (object) array('class' => 'Camino1 - Camino2', 'id_camino' => $datos1[$i]->p_ID_CAMINO, 'id_inicio' => $datos1[$i]->ID_CAMINO, 'id_fin' => $datos1[$i]->p_ID_CAMINO, 'id_origen' => $datos1[$i]->ID_CAMINO, 'id_destino' => $datos1[$i]->p_ID_CAMINO);
            array_push($conexiones, $conexion1);
        }

        for($i=0; $i<$longitud2; $i++){
            $conexion2 = (object) array('class' => 'Camino2 - Camino1', 'id_camino' => $datos2[$i]->ID_CAMINO, 'id_inicio' => $datos2[$i]->p_ID_CAMINO, 'id_fin' => $datos2[$i]->ID_CAMINO, 'id_origen' => $datos2[$i]->p_ID_CAMINO, 'id_destino' => $datos2[$i]->ID_CAMINO);
            array_push($conexiones, $conexion2);
        }

        for($i=0; $i<$longitud3; $i++){
            $conexion3 = (object) array('class' => 'Camino - Entidad', 'id_camino' => $datos3[$i]->ID_ENTIDAD);
            array_push($conexiones, $conexion3);
        }*/
    
  /*      return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}
*/

/*function conexionEntidadCamino ($id_entidad){

    //Conexiones entidad
    $sql_conexion_entidad = "SELECT * FROM CONEXION_ENTIDAD WHERE ID_ENTIDAD = ".$id_entidad;

    //$sql = "SELECT ENTIDAD.ID_ENTIDAD, ENTIDAD.LATITUD_ENTIDAD, ENTIDAD.LONGITUD_ENTIDAD, CAMINO.ID_CAMINO, CAMINO.LATITUD_CAMINO, CAMINO.LONGITUD_CAMINO FROM ENTIDAD INNER JOIN CONEXION_ENTIDAD ON ENTIDAD.ID_ENTIDAD = CONEXION_ENTIDAD.ID_ENTIDAD INNER JOIN CAMINO ON CONEXION_ENTIDAD.ID_CAMINO = CAMINO.ID_CAMINO WHERE ENTIDAD.ID_ENTIDAD = ".$id_entidad;

    try{
        $db = new db();
        $db = $db->connect();
        
        //Conexiones entidad
        $stmt_conexion_entidad = $db->query($sql_conexion_entidad);
        $datos_conexion_entidad = $stmt_conexion_entidad->fetchAll(PDO::FETCH_OBJ);

        /*$stmt = $db->query($sql);
        $datos = $stmt->fetchAll(PDO::FETCH_OBJ);*/

        /*$db = null;
        $conexiones = array();

        //Conexiones entidad
        $longitud_conexion_entidad = count($datos_conexion_entidad);

        //$longitud = count($datos);

        /*for($i=0; $i<$longitud; $i++){
            $conexion = (object) array('class' => 'Entidad - Camino', 'id_camino' => $datos[$i]->ID_CAMINO);
            array_push($conexiones, $conexion);
        }*/

        //Conexiones entidad
        /*for($i=0; $i<$longitud_conexion_entidad; $i++){
            $object = (object) array('class' => 'Entidad', 'origen' => $datos_conexion_entidad[$i]->ID_ENTIDAD, 'destino' => $datos_conexion_entidad[$i]->ID_CAMINO);
            array_push($conexiones, $object);
        }
    
        return $conexiones;
    } 
    catch(PDOException $e){
        return '{"error": {"text": '.$e->getMessage().'}';
    }
}*/