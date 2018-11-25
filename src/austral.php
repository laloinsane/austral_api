<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

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