<?php
namespace App;

class LoginManager
{
    private $loginstatus;
    private $username;
    private $password;

    function __construct($settings)
    {
        $this->loginstatus = isset($_SESSION['login.status']) ? $_SESSION['login.status'] : 0;
        if ($settings['login.disabled']) $this->loginstatus = 1;
        $this->username = $settings['login']['user'];
        $this->password = $settings['login']['password'];
    }

    public function setDisabled()
    {
        $this->loginstatus = 1;
    }

    public function isLoggedIn()
    {
        return $this->loginstatus == 1;
    }
    
    /**
     * Belepteti a felhasznalot
     * @param string $username
     * @param string $password
     * @return boolean sikerult-e a belepes
     */
    public function login($username, $password)
    {
        if ($this->username == $username && $this->password == $password)
        {
            $this->loginstatus = 1;
            $_SESSION['login.status'] = 1;
            return true;
        }
        else
        {
            $this->loginstatus = 0;
            $_SESSION['login.status'] = 0;
            return false;
        }
    }
    
    public function logout()
    {
        $this->loginstatus = 0;
        $_SESSION['login.status'] = 0;
    }
    
}
