<?php
namespace Controller;

use Slim\Http\Response;
use Slim\Http\ServerRequest;

class FrontController
{
    public function __construct()
    {
        // see https://github.com/middlewares/psr15-middlewares for awesome middleware
        $this->middleware = [

            // apply to all method in this class.
            // new InterfaceMiddleware(),

            // apply to method index only
            // ['index', new InterfaceMiddleware()],

            //      apply to method
            // ['index', new InterfaceMiddleware()],

            // apply if some condition was true
            // [true/* or some logic like $this->validate():bool */, new InterfaceMiddleware()],
            //
            // or pass the closure with request param
            // [function (ServerRequestInterface $request) {
            //     if ($request->hasHeader("forbidden")) {
            //         return false;
            //     }
            //     return true;
            // }, new InterfaceMiddleware()],

            // you can provide a closuse as middleware
            // apply to all method in this class
            //
            // function ($request, $handler) {
            //     $response = $handler->handle($request);
            //     return $response;
            // },

            //TODO : make middleware using container like : InterfaceMiddleware::class
        ];
    }

    public function index(ServerRequest $request, Response $response)
    {
        return $response->write("Welcome");
    }

    public function page(ServerRequest $request, Response $response)
    {
        return $response->write('ini page');
    }
}
