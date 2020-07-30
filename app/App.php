<?php

namespace App;

use Exception;
use function Symfony\Component\String\s;
use Illuminate\Database\Capsule\Manager as Capsule;
use Jenssegers\Blade\Blade;
use Narrowspark\HttpEmitter\SapiEmitter;
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
            // $request
            $request = new ServerRequest((new ServerRequestFactory())->createFromGlobals());

            // $response
            $response = new Response((new ResponseFactory())->createResponse(200, "OK"), new StreamFactory());

            // core routing
            $target = array_values(array_diff(s($request->getUri()->getPath())->split('/'), [""]));
            $controller = 'Controller\\' . s($target[0] ?? $this->config["app"]["defaultController"])->title() . 'Controller';
            $method = s($target[1] ?? $this->config["app"]["defaultMethod"]);

            $class = new $controller();
            if (!method_exists($class, $method)) {
                throw new Exception("Not Found!!!");
            }
            $response = call_user_func([$class, s($method)->toString()], ...array_merge([$request, $response])) ?? null;
            if ($response instanceof \Slim\Http\Response) {
                (new SapiEmitter())->emit($response);
            }
        } catch (Exception $e) {
            print_r($e->getMessage() . '<br>');
            print_r($e->getTraceAsString());
        } catch (Throwable $e) {
            print_r($e->getMessage() . '<br>');
            print_r($e->getTraceAsString());
        }
    }
}