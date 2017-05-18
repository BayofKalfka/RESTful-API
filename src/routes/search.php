<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Search for Relevant Movies Given A Person Name
$app->get('/api/search/{name}', function(Request $request, Response $response){
    $name = $request->getAttribute('name');
    list($first, $last) = split(" ", $name);

    $sql_movieInvolved = "(SELECT M.id,M.title,M.year 
                            FROM  Movie M, MovieActor MA, Actor A 
                        WHERE  M.id = MA.mid 
                            AND MA.aid = A.id 
                            AND A.last = :last 
                            AND A.first = :first
                            ORDER BY M.year)
                        UNION
                        (SELECT M.id,M.title,M.year 
                            FROM  Movie M, MovieDirector MD,Director D
                        WHERE  M.id = MD.mid 
                            AND MD.did = D.id 
                            AND D.last = :last 
                            AND D.first = :first
                            ORDER BY M.year)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt_movieInvolved = $db->prepare($sql_movieInvolved);

        $stmt_movieInvolved->bindParam(':last', $last);
        $stmt_movieInvolved->bindParam(':first', $first);

        $stmt_movieInvolved->execute();
        $movieInvolved = $stmt_movieInvolved->fetchAll(PDO::FETCH_OBJ); 

        $db = null;
        echo json_encode($movieInvolved);

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

