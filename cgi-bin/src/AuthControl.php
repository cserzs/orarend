<?php
namespace App;

/**
*   A bejelentkezest ellenorzi.
*   Azok a route-k, ahol nincs nev megadva vagy public-al kezdodik, nem kell bejelentkezni.
*/
class AuthControl
{
    private $container;
    private $publicPrefix = "public";
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function __invoke($request, $response, $next)
    {
        $route = $request->getAttribute('route');
        if (empty($route)) return $next($request, $response);
        
        $routeName = $route->getName();
        if (empty($routeName)) return $next($request, $response);
        if (substr($routeName, 0, strlen($this->publicPrefix)) == $this->publicPrefix) return $next($request, $response);

        $loginManager = $this->container->get('loginManager');

        //if ($this->container->settings['login.disabled']) return $next($request, $response);
        
        if ($loginManager->isLoggedIn()) return $next($request, $response);
        
        return $response->withRedirect('/', 301);
    }
}
