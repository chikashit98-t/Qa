<?php

namespace App\Cores;

final class Router
{
    /** @var list<array{method:string,regex:string,params:list<string>,handler:array}> */
    public array $routes = [];

    public function add(string $method, string $pattern, array $handler)
    {
        $params = [];
        $regex = '#^' . preg_replace_callback('/\{([a-z_]+)(?::(.+))?\}/i', static function (array $matches) use (&$params) {
            $params[] = $matches[1];

            return '(' . ($matches[2] ?? '[^/]+') . ')';
        }, $pattern) . '$#u';
        $this->routes[] = [
            'method' => strtoupper($method),
            'params' => $params,
            'regex' => $regex,
            'handler' => $handler
        ];
    }

    public function match(Request $req): ?Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $req->method) {
                continue;
            }
            if (!preg_match($route['regex'], $req->path, $matches)) {
                continue;
            }
            array_shift($matches);
            $args = [];
            foreach ($route['params'] as $i => $name) {
                $args[$name] = $matches[$i] ?? '';
            }

            return $this->dispatch($route['handler'], $args, $req);
        }
        return null;
    }

    private function dispatch(array $handler, array $args, Request $req): Response
    {
        [$class, $method] = $handler;
        return $class::$method($req, $args);
    }
}
