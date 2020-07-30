<?php

use App\App;

require __DIR__ . '/../vendor/autoload.php';

$app = new App(require __DIR__ . '/../config/config.php');
$app->run();

// $app = require __DIR__ . '/../app/bootstrap.php';
// $app->run();

// use Narrowspark\HttpEmitter\SapiEmitter;
// use Slim\Http\Factory\DecoratedResponseFactory;
// use Slim\Http\Factory\DecoratedServerRequestFactory;
// use Slim\Psr7\Factory\ResponseFactory;
// use Slim\Psr7\Factory\ServerRequestFactory;
// use Slim\Psr7\Factory\StreamFactory;

// require __DIR__ . '/../vendor/autoload.php';

// // $request
// $decoratedRequestFactory = new DecoratedServerRequestFactory(new ServerRequestFactory());
// $request = (new ServerRequestFactory())->createFromGlobals();
// $request = $decoratedRequestFactory->createServerRequest($request->getMethod(), $request->getUri());

// // $response
// $decoratedResponseFactory = new DecoratedResponseFactory(new ResponseFactory(), new StreamFactory());
// $response = $decoratedResponseFactory->createResponse(200, 'OK');
// $response = $response->withJson([
//     'data' => $request->getParams(),
// ]);

// //$response = $middleware->handle($response);

// try {
//     (new SapiEmitter())->emit($response);
// } catch (Exception $e) {
//     echo $e->getTraceAsString();
//     die();
// }
