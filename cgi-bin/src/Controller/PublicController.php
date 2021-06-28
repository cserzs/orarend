<?php
namespace App\Controller;

class PublicController extends Controller
{
    
    public function index($req, $resp)
    {
        return $this->render($resp, 'public_layout.php', array(
            '_page' => $this->fetch('public/index.php')
        ));
    }
    
    public function processLogin($req, $resp)
    {
        $loginManager = new \App\LoginManager($this->settings);
        $post = $req->getParsedBody();
        
        if ($loginManager->isLoggedIn())
        {
            return $resp->withRedirect('/main/index', 301);
        }
        
        if ($loginManager->login(\App\Helper::get($post, 'user', null), \App\Helper::get($post, 'password', null)))
        {
            return $resp->withRedirect('/main/index', 301);
        }
        else
        {
            $this->container->flash->addMessage('system_message', 'Hibas felhasznalonev vagy jelszo!');        
            return $resp->withRedirect('/', 301);
        }
    
    }
}