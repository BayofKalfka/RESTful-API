<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



// Get All movies - GET -DONE
$app->get('/api/movies', function(Request $request, Response $response){
    $sql = "SELECT * FROM Movie";


    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->query($sql);
        $movies = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo json_encode($movies);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Get Single Movie - GET - DONE
$app->get('/api/movie/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_movieInfo = "SELECT * FROM Movie WHERE id = $id";

    $sql_directorInvolved = "SELECT D.last, D.first      
                                FROM Director D, MovieDirector MD 
                            WHERE MD.mid=$id AND D.id=MD.did";

    $sql_actorInvolved = "SELECT A.id, A.first, A.last, MA.role 
                            FROM Actor A, MovieActor MA 
                        WHERE MA.mid=$id AND A.id=MA.aid ORDER BY A.id";
   
    $sql_movieGenre = "SELECT genre FROM MovieGenre WHERE mid=$id"; 
    $sql_movieReview = "SELECT name, time, rating, comment FROM Review WHERE mid=$id"; 
    
    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        // Get Movie Information
        $stmt_movieInfo = $db->query($sql_movieInfo);
        $movieInfo = $stmt_movieInfo->fetch(PDO::FETCH_OBJ);
        // Get Director Involved
        $stmt_directorInvolved = $db->query($sql_directorInvolved);
        $directorInvolved = $stmt_directorInvolved->fetchAll(PDO::FETCH_OBJ); // may have multiple directors
        // Get Actors Involved
        $stmt_actorInvolved = $db->query($sql_actorInvolved);
        $actorInvolved = $stmt_actorInvolved->fetchAll(PDO::FETCH_OBJ); // may have multiple actors
        // Get Movie Genre
        $stmt_movieGenre = $db->query($sql_movieGenre);
        $movieGenre = $stmt_movieGenre->fetchAll(PDO::FETCH_OBJ); // may have multiple genres
        // Get Movie Review
        $stmt_movieReview = $db->query($sql_movieReview);
        $movieReview = $stmt_movieReview->fetchAll(PDO::FETCH_OBJ); // may have multiple genres

        $db = null;
        echo json_encode($movieInfo);
        echo json_encode($directorInvolved);
        echo json_encode($actorInvolved );
        echo json_encode($movieGenre);
        echo json_encode($movieReview);
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Add Movie - DONE
$app->post('/api/movie/add', function(Request $request, Response $response){
    $title = $request->getParam('title');
    $year = $request->getParam('year');
    $rating = $request->getParam('rating');
    $company = $request->getParam('company');
    
    $genre = $request->getParam('genre');

    $sql_movieInfo = "INSERT INTO Movie (id, title, year, rating, company) VALUES
    (:id, :title, :year, :rating, :company)";
    $sql_movieGenre = "INSERT INTO MovieGenre (mid, genre) VALUES
    (:mid, :genre)";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        // Get current max movie id from db and update max movie id
        $lookup_id_query = "SELECT * FROM MaxMovieID";
        $lookup_id_result = $db->query($lookup_id_query);
        $id = current($lookup_id_result->fetch(PDO::FETCH_ASSOC))+1;
        $update_id_query = "UPDATE MaxMovieID SET id=$id";
        $update_result =  $db->query($update_id_query);

        // Add Movie Information
        $stmt_movieInfo = $db->prepare($sql_movieInfo);

        $stmt_movieInfo->bindParam(':id',                 $id);
        $stmt_movieInfo->bindParam(':title',           $title);
        $stmt_movieInfo->bindParam(':year',             $year);
        $stmt_movieInfo->bindParam(':rating',         $rating);
        $stmt_movieInfo->bindParam(':company',       $company);

        $stmt_movieInfo->execute();

        // Add Movie Genre
        $stmt_movieGenre = $db->prepare($sql_movieGenre);

        $stmt_movieGenre->bindParam(':mid',                 $id);
        $stmt_movieGenre->bindParam(':genre',           $genre);

        $stmt_movieGenre->execute();

        echo '{"notice": {"text": "Movie Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});


// Update Movie - PUT -DONE

$app->put('/api/movie/update/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $title = $request->getParam('title');
    $year = $request->getParam('year');
    $rating = $request->getParam('rating'); 
    $company = $request->getParam('company');

    $sql = "UPDATE Movie SET
                title          = :title,
                year         = :year,
                rating           = :rating,
                company           = :company
            WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':company', $company);

        $stmt->execute();
        $db = null;
        echo '{"notice": {"text": "Movie Updated"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Delete Movie - DONE
$app->delete('/api/movie/delete/{id}', function(Request $request, Response $response){
    $id = $request->getAttribute('id');

    $sql_directorInvolved = "DELETE FROM MovieDirector WHERE mid = $id";
    $sql_actorInvolved = "DELETE FROM MovieActor WHERE mid = $id";
    $sql_movieGenre = "DELETE FROM MovieGenre WHERE mid = $id";
    $sql_movieReview = "DELETE FROM Review WHERE mid = $id";
    $sql_movieInfo = "DELETE FROM Movie WHERE id = $id";

    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();
        // Delete child table first to avoid foreign key constraints
        // Delete directors involved
        $stmt_directorInvolved = $db->prepare($sql_directorInvolved);
        $stmt_directorInvolved->execute();
        // Delete actors involved
        $stmt_actorInvolved = $db->prepare($sql_actorInvolved);
        $stmt_actorInvolved->execute();
        // Delete movie genre
        $stmt_movieGenre = $db->prepare($sql_movieGenre);
        $stmt_movieGenre->execute();
        // Delete movie review
        $stmt_movieReview = $db->prepare($sql_movieReview);
        $stmt_movieReview->execute();
        // Delete movie information
        $stmt_movieInfo = $db->prepare($sql_movieInfo);
        $stmt_movieInfo->execute();


        $db = null;
        echo '{"notice": {"text": "Movie Deleted"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});

// Add Movie Review -DONE
$app->post('/api/movie/{id}/review/add', function(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $name = $request->getParam('name');
    $time = $request->getParam('time');
    $rating = $request->getParam('rating');
    $comment = $request->getParam('comment');

    $sql = "INSERT INTO Review (mid, name, time, rating, comment) VALUES
    (:mid, :name, :time, :rating, :comment)";


    try{
        // Get DB Object
        $db = new db();
        // Connect
        $db = $db->connect();

        // Add Movie Review
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':mid',                 $id);
        $stmt->bindParam(':name',              $name);
        $stmt->bindParam(':time',             $time);
        $stmt->bindParam(':rating',         $rating);
        $stmt->bindParam(':comment',       $comment);

        $stmt->execute();



        echo '{"notice": {"text": "User Comment Added"}';

    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
    }
});