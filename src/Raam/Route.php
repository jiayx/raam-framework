<?php
namespace Raam;

use Raam\Application;
use Raam\Request;
// 路由
class Route
{
    protected $rules = [
        'GET' => [],
        'POST' => [],
        'ALL' => [],
        'MISSING' => '',
    ];
    protected $app;
    protected $request;
    protected $uri;    

    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
        $this->uri = $request->uri();
    }

    public function run()
    {
        $routes = self::routes();
        if (array_key_exists($this->uri, $routes)) {
            if (self::dispatch($routes[$this->uri])) {
                return;
            }
        } else {
            foreach ($routes as $key => $route) {
                if (! preg_match('~^' . $key . '$~', $this->uri, $match)) {
                    continue;
                }
                array_shift($match);
                if (self::dispatch($route, $match)) {
                    return;
                }
            }
        }
        self::dispatch($this->rules['MISSING']);
    }

    // 路由分发
    private function dispatch(&$callback, $params = [])
    {
        if (is_string($callback)) {
            if ($isCallable = self::isCallable($callback)) {
                // call_user_func_array($isCallable, $params);
                $this->app->invoke($isCallable, $params);
                return true;
            }
        } elseif (is_callable($callback)) {
            // call_user_func_array($callback, $params);
            $this->app->invoke($callback, $params);
            return true;
        } else {
            // todo 
            return false;
        }
    }

    // 是否可调用
    private function isCallable($method)
    {
        $method = explode('@', $method, 2);
        if (count($method) < 2) {
            return false;
        }
        $className = $method[0];
        $methodName = $method[1];
        if (! class_exists($className)) {
            // echo 'class: ', $className , ' not found!';
            return false;
        }
        $class = new $className;
        if (! method_exists($class, $methodName)) {
            // echo 'method: ', $methodName, ' not found!';
            return false;
        }

        return [$class, $methodName];
    }

    private function routes()
    {
        $routes = isset($this->rules[REQUEST_METHOD]) ? $this->rules[REQUEST_METHOD] : [];
        return $routes + $this->rules['ALL'];
    }

    // get请求路由
    public function get($uri, $method)
    {
        $this->rules['GET'][$uri] = $method;
    }
    
    // post请求路由
    public function post($uri, $method)
    {
        $this->rules['POST'][$uri] = $method;
    }

    // 任意请求路由
    public function all($uri, $method)
    {
        $this->rules['ALL'][$uri] = $method;
    }

    // 找不到路由时
    public function missing($method)
    {
        $this->rules['MISSING'] = $method;
    }

}