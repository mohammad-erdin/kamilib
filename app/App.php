<?php

namespace App;

use Exception;
use function Symfony\Component\String\s;
use Illuminate\Database\Capsule\Manager as Capsule;
use Jenssegers\Blade\Blade;
use Narrowspark\HttpEmitter\SapiEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Throwable;

class App
{
    public $config;
    public $template;
    public $attributes = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->bootTemplate();
        $this->bootEloquent();
    }

    public function bootTemplate()
    {
        !is_dir($this->config["template"]["cache"]) && mkdir($this->config["template"]["cache"]);
        $this->template = new Blade($this->config["template"]["view"], $this->config["template"]["cache"]);
    }

    private function bootEloquent()
    {
        $capsule = new Capsule;
        $capsule->addConnection($this->config["database"]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function run()
    {
        if ($this->config["displayError"]) {
            Capsule::enableQueryLog();
        }

        $response = null;
        try {

            // request
            $request = new ServerRequest((new ServerRequestFactory())->createFromGlobals()); //ServerRequestInterface

            // core Routing
            $target = array_values(array_diff(s($request->getUri()->getPath())->split('/'), [""]));
            $controller = s($target[0] ?? $this->config["app"]["defaultController"])->title()->toString();
            $method = s($target[1] ?? $this->config["app"]["defaultMethod"])->toString();
            $controller = 'Controller\\' . $controller . 'Controller';

            if (!class_exists($controller)) {
                $method = s($target[0] ?? $this->config["app"]["defaultController"])->title()->toString();
                $controller = 'Controller\\' . $this->config["app"]["defaultController"] . 'Controller';
            }

            $path = $controller . '\\' . $method;
            $class = new $controller();

            if (!method_exists($class, $method)) {
                throw new Exception("Method $method not found in $controller");
            }

            // Middleware
            $listMiddleware = [];
            array_push($listMiddleware, ...$class->middleware);
            array_push($listMiddleware, function (ServerRequestInterface $request, $test) use ($class, $method) {
                $response = new Response((new ResponseFactory())->createResponse(200, "OK"), new StreamFactory());
                return $class->$method($request, $response);
            });
            $response = (new Dispatcher($listMiddleware, $path))->dispatch($request);

            // Emit Response
            if ($response instanceof \Slim\Http\Response) {
                (new SapiEmitter())->emit($response);
            }
        } catch (Exception $e) {
            echo $this->template->render('error.error', ['error' => $e]);
        } catch (Throwable $e) {
            echo $this->template->render('error.error', ['error' => $e]);
        }
    }
}
