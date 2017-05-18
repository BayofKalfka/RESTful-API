<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



// Get All Directors - GET -DONE
$app->get('/api/directors', function(Request $request, Response $response){
    $sql = "SELECT * FROM Director";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $directors = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($directors);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Get Single Director - GET -DONE
$app->get('/api/director/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_directorInfo = "SELECT * FROM Director WHERE id = $id";
    $sql_movieInvolved = "SELECT MD.role,M.title,M.year,M.id 
                            FROM MovieDirector MD,Movie M 
                    WHERE MD.did = $id AND M.id = MD.mid ORDER BY M.year";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt_directorInfo = $db->query($sql_directorInfo);
        $directorInfo = $stmt_directorInfo->fetch(PDO::FETCH_OBJ);
        
        $stmt_movieInvolved = $db->query($sql_movieInvolved);
        $movieInvolved = $stmt_movieInvolved->fetch(PDO::FETCH_OBJ);

        $db = null;
        echo json_encode($directorInfo);
        echo json_encode($movieInvolved);

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Add Director -DONE
$app->post('/api/director/add', function(Request $request, Response $response){
    $last = $request->getParam('last');
    $first = $request->getParam('first');
    $dob = $request->getParam('dob');
    $dod = $request->getParam('dod');

    $mid = $request->getParam('mid');

    $sql_directorInfo = "INSERT INTO Director (id, last, first, dob, dod) VALUES
    (:id, :last, :first, :dob, :dod)";
    $sql_movieInvolved = "INSERT INTO MovieDirector (mid, did) VALUES (:mid, :did)";

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

        // Add director information
        $stmt_directorInfo = $db->prepare($sql_directorInfo);

        $stmt_directorInfo->bindParam(':id',                 $id);
        $stmt_directorInfo->bindParam(':last',             $last);
        $stmt_directorInfo->bindParam(':first',           $first);
        $stmt_directorInfo->bindParam(':dob',               $dob);
        $stmt_directorInfo->bindParam(':dod',               $dod);

        $stmt_directorInfo->execute();

        // Add movies involded
        foreach ($mid as $key => $value) {
            $stmt_movieInvolved = $db->prepare($sql_movieInvolved);

            $stmt_movieInvolved->bindParam(':mid',    $value);
            $stmt_movieInvolved->bindParam(':did',     $id);

            $stmt_movieInvolved->execute();
        }

        echo '{"notice": {"text": "Director Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});


// Update Director - PUT -DONE

$app->put('/api/director/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $last = $request->getParam('last');
    $first = $request->getParam('first');
    $dob = $request->getParam('dob'); 
    $dod = $request->getParam('dod');

    $sql = "UPDATE Director SET
                last          = :last,
                first         = :first,
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
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':dod', $dod);

        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Director Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Delete Director - DONE
$app->delete('/api/director/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_movieInvolved = "DELETE FROM MovieDirector WHERE did = $id";
    $sql_directorInfo = "DELETE FROM Director WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        // Delete child table first to avoid foreign key constrains
        $stmt_movieInvolved = $db->prepare($sql_movieInvolved);
        $stmt_movieInvolved->execute();
        // Delete parent table
        $stmt_directorInfo = $db->prepare($sql_directorInfo);
        $stmt_directorInfo->execute();

        $db = null;
        echo '{"notice": {"text": "Director Deleted"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Add Movie information to an existed Director 
$app->post('/api/director/{did}/movie/add', function(Request $request, Response $response){
    $did = $request->getAttribute('did');
    $mid = $request->getParam('mid');

    $sql = "INSERT INTO MovieDirector (mid, did) VALUES
    (:mid, :did)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':did',     $did);
        $stmt->bindParam(':mid',     $mid);

        $stmt->execute();

        echo '{"notice": {"text": "Actor-Movie Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});