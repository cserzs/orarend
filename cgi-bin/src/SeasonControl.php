<?php
namespace App;

/**
*   Az aktiv season-t ellenorzi.
*/
class SeasonControl
{
    private $container;
    private $exceptions = array(
        'public.index', 'public.processLogin',
        'main.index', 'main.maintenance', 'main.change_settngs', 'main.logout',
        'seasons.index', 'seasons.delete', 'seasons.create', 'seasons.activate', 'seasons.cloneSeason');
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function __invoke($request, $response, $next)
    {
        
        $seasonManager = $this->container->get('season');
        if ($seasonManager->hasActiveSeason())
        {
            return $next($request, $response);
        }
        
        $route = $request->getAttribute('route');

        // ha nem letezik
        if (empty($route)) return $next($request, $response);
        
        $routeName = $route->getName();

        if (in_array($routeName, $this->exceptions))
        {
            return $next($request, $response);
        }

        $this->container->flash->addMessage('system_message', 'Nincs aktív időszak!!');
        return $response->withRedirect('/seasons/index', 301);
    }
}
