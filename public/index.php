<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php'; //Use .. to indicate the parent directory
require '../src/config/db.php';

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// Actor Route
require '../src/routes/actors.php';
// Director Route
require '../src/routes/directors.php';
// Movie Route
require '../src/routes/movies.php';
// Search Route
require '../src/routes/search.php';
// // MovieActor Route
// require '../src/routes/movie_actor.php';


$app->run();