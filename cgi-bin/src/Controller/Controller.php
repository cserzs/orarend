<?php
namespace App\Controller;

class Controller
{
    protected $container;
    protected $db;
    protected $settings;
    protected $seasonManager;
    protected $logger;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
        $this->settings = $container->get('settings');
        $this->logger = $container->get('logger');
        
        $this->seasonManager = $container->get('season');
    }
    
    protected function render($response, $template, $data = array())
    {
        $data['season_name'] = $this->seasonManager->season_name;
        $data['season_date'] = $this->seasonManager->getDates();
        return $this->container->view->render($response, $template, $data);
    }
    
    protected function fetch($template, $data = array())
    {
        return $this->container->view->fetch($template, $data);
    }
    
}