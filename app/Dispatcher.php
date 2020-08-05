<?php

namespace App;

use Closure;
use function Symfony\Component\String\s;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements MiddlewareInterface, RequestHandlerInterface
{
    private $path;
    /**
     * @var MiddlewareInterface[]
     */
    private $middleware;

    /**
     * @var RequestHandlerInterface|null
     */
    private $next;

    /**
     * @param MiddlewareInterface[]|string[]|array[]|Closure[] $middleware
     */
    public function __construct(array $middleware, string $path = "")
    {
        if (empty($middleware)) {
            throw new LogicException('Empty middleware queue');
        }

        $this->middleware = $middleware;
        $this->path = $path;
    }

    /**
     * Magic method to execute the dispatcher as a callable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }

    /**
     * Return the next available middleware in the stack.
     *
     * @return MiddlewareInterface|false
     */
    private function get(ServerRequestInterface $request)
    {
        $middleware = current($this->middleware);
        next($this->middleware);

        if ($middleware === false) {
            return $middleware;
        }

        $middleware = !is_array($middleware) ? [$middleware] : $middleware;
        $conditions = $middleware;
        $middleware = array_pop($conditions);

        foreach ($conditions as $condition) {
            $condition = is_string($condition) ? s($this->path)->endsWith($condition) : $condition;
            $condition = is_array($condition) ? in_array(last(s($this->path)->split("\\"))->toString(), $condition) : $condition;
            $condition = ($condition instanceof Closure) ? call_user_func($condition, $request) ?? true : $condition;

            if ($condition === true) {
                continue;
            }

            if ($condition === false) {
                return $this->get($request);
            }

        }

        if ($middleware instanceof Closure) {
            return self::createMiddlewareFromClosure($middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        throw new InvalidArgumentException(sprintf('No valid middleware provided (%s)', is_object($middleware) ? get_class($middleware) : gettype($middleware)));
    }

    /**
     * Dispatch the request, return a response.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middleware);

        return $this->handle($request);
    }

    /**
     * @see RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->get($request);

        if ($middleware === false) {
            if ($this->next !== null) {
                return $this->next->handle($request);
            }

            throw new LogicException('Middleware queue exhausted');
        }

        return $middleware->process($request, $this);
    }

    /**
     * @see MiddlewareInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $this->next = $next;

        return $this->dispatch($request);
    }

    /**
     * Create a middleware from a closure
     */
    private static function createMiddlewareFromClosure(Closure $handler): MiddlewareInterface
    {
        return new class($handler) implements MiddlewareInterface
        {
            private $handler;

            public function __construct(Closure $handler)
            {
                $this->handler = $handler;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
            {
                return call_user_func($this->handler, $request, $next);
            }
        };
    }
}
