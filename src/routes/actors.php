<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Actors  - GET -DONE
$app->get('/api/actors', function(Request $request, Response $response){
    $sql = "SELECT * FROM Actor";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $actors = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($actors);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Get Single Actor By id - GET -DONE
$app->get('/api/actor/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_actorInfo = "SELECT * FROM Actor WHERE id = $id";
    $sql_movieInvolved = "SELECT MA.role,M.title,M.year,M.id 
                            FROM MovieActor MA,Movie M 
                    WHERE MA.aid = $id AND M.id = MA.mid ORDER BY M.year";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt_actorInfo  = $db->query($sql_actorInfo);
        $actorInfo = $stmt_actorInfo->fetch(PDO::FETCH_OBJ);

        $stmt_movieInvolved = $db->query($sql_movieInvolved);
        $movieInvolved = $stmt_movieInvolved->fetchAll(PDO::FETCH_OBJ); //  One person may play two roles in the same novie

        $db = null;
        echo json_encode($actorInfo);
        echo json_encode($movieInvolved);

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});


// Add Actor -DONE
$app->post('/api/actor/add', function(Request $request, Response $response){
    $last = $request->getParam('last');
    $first = $request->getParam('first');
    $sex = $request->getParam('sex');
    $dob = $request->getParam('dob');
    $dod = $request->getParam('dod');

    $mid_role = $request->getParam('mid_role');

    $sql_actorInfo = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES
    (:id, :last, :first, :sex, :dob, :dod)";
    $sql_movieInvolved = "INSERT INTO MovieActor (mid, aid, role) VALUES (:mid, :aid, :role)";


    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        // Get current max id from db and update max id
        $lookup_id_query = "SELECT * FROM MaxPersonID";
        $lookup_id_result = $db->query($lookup_id_query);
        $id = current($lookup_id_result->fetch(PDO::FETCH_ASSOC))+1;
        $update_id_query = "UPDATE MaxPersonID SET id=$id";
        $update_result =  $db->query($update_id_query);

        // Add actor information
        $stmt_actorInfo = $db->prepare($sql_actorInfo);

        $stmt_actorInfo->bindParam(':id',                 $id);
        $stmt_actorInfo->bindParam(':last',             $last);
        $stmt_actorInfo->bindParam(':first',           $first);
        $stmt_actorInfo->bindParam(':sex',               $sex);
        $stmt_actorInfo->bindParam(':dob',               $dob);
        $stmt_actorInfo->bindParam(':dod',               $dod);

        $stmt_actorInfo->execute();

        // Add movies involded
        foreach ($mid_role as $key => $value) {
            $stmt_movieInvolved = $db->prepare($sql_movieInvolved);

            $stmt_movieInvolved->bindParam(':mid',    $value['mid']);
            $stmt_movieInvolved->bindParam(':aid',              $id);
            $stmt_movieInvolved->bindParam(':role',  $value['role']);

            $stmt_movieInvolved->execute();
        }

        echo '{"notice": {"text": "Actor Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Add Movie information to a existing Actor
$app->post('/api/actor/{aid}/movie/add', function(Request $request, Response $response){
    $aid = $request->getAttribute('aid');
    $mid = $request->getParam('mid');
    $role = $request->getParam('role');

    $sql = "INSERT INTO MovieActor (mid, aid, role) VALUES
    (:mid, :aid, :role)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':aid',     $aid);
        $stmt->bindParam(':mid',     $mid);
        $stmt->bindParam(':role',   $role);


        $stmt->execute();

        echo '{"notice": {"text": "Actor-Movie Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Update Actor - PUT -DONE

$app->put('/api/actor/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $last = $request->getParam('last');
    $first = $request->getParam('first');
    $sex = $request->getParam('sex');
    $dob = $request->getParam('dob'); 
    $dod = $request->getParam('dod');

    $sql = "UPDATE Actor SET
                last          = :last,
                first         = :first,
                sex           = :sex,
                dob           = :dob,
                dod           = :dod
            WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':last', $last);
        $stmt->bindParam(':first', $first);
        $stmt->bindParam(':sex', $sex);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':dod', $dod);

        $stmt->execute();

        echo '{"notice": {"text": "Actor Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Delete Actor - DONE
$app->delete('/api/actor/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_movieInvolved = "DELETE FROM MovieActor WHERE aid = $id";
    $sql_actorInfo = "DELETE FROM Actor WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        // Delete child table first to avoid foreign key constrains
        $stmt_movieInvolved = $db->prepare($sql_movieInvolved);
        $stmt_movieInvolved->execute();
        // Delete parent table
        $stmt_actorInfo = $db->prepare($sql_actorInfo);
        $stmt_actorInfo->execute();

        $db = null;
        echo '{"notice": {"text": "Actor Deleted"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});




