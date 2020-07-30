<?php
namespace Controller;

use Slim\Http\Response;
use Slim\Http\ServerRequest;

class WelcomeController
{

    public function index(ServerRequest $request, Response $response)
    {
        return $response
            ->withJson([
                "success" => true,
                "data" => "ini welcome",
            ]);

        // atau $this->template->render(..,..);
    }

    public function home(ServerRequest $request, Response $response)
    {
        return $response->withJson([
            "success" => true,
            "data" => $request->getParams(),
            "data2" => $_POST,
        ]);
    }

}
