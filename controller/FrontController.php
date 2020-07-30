<?php
namespace Controller;

use Slim\Http\Response;
use Slim\Http\ServerRequest;

class FrontController
{

    public function index(ServerRequest $request, Response $response)
    {
        return $response
            ->withJson([
                "success" => true,
            ]);

        // atau $this->template->render(..,..);
    }

}
