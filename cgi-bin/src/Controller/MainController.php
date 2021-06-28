<?php
namespace App\Controller;

use Respect\Validation\Validator as v;

class MainController extends Controller
{
    
    public function index($req, $resp)
    {
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('main/index.php'),
            '_pagetitle' => 'Orarend'
        ));
    }

    public function general($req, $resp)
    {
        if ($req->isPost())
        {
            $data = \App\Helper::get($req->getParsedBody(), 'exceptionDate', array());
            //$ttm->saveExceptionDates( $data );
            $this->seasonManager->setExceptionDates($data);
            $this->seasonManager->save();
            $this->container->flash->addMessage('system_message', 'Sikeres mentés!');
            return $resp->withRedirect('/main/general', 301);
        }

        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        $seasons = $this->seasonManager->getAll("ORDER BY season_id");

        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('main/general.php', array(
                'season_name' => $this->seasonManager->season_name,
                'ttm' => $ttm,
                'napok' => $ttm->getSemesterDates(),
                'seasons' => $seasons
            )),
            '_pagetitle' => 'Orarend'
        ));        
    }

    public function maintenance($req, $resp)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);

        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('main/maintenance.php'),
            '_pagetitle' => 'Karbantartas'
        ));
    }

    //  orarend torlese
    public function cleartt($req, $resp)
    {
        if ( !$req->isPost()) return $resp->withRedirect('/main/general', 301);
        
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        $ttm->clearTimetable();
        
        $this->container->flash->addMessage('system_message', 'Az órarend törlése sikeres!');    
        return $resp->withRedirect('/main/general', 301);
    }
    
    //  NEM KELL
    //  maskepp vannak kezelve a gyakorlatok
    //  klinikai gyakorlat tantargy id
    private function saveExcercise($req, $resp)
    {
        if ( !$req->isPost()) return $resp->withRedirect('/main/maintenance', 301);
        
        $id = (int)\App\Helper::get($req->getParsedBody(), 'id', -1);
        
        if ($id < 1) {
            $this->container->flash->addMessage('system_message', 'A gyakorlati ora id csak pozitiv szam lehet!');    
            return $resp->withRedirect('/main/maintenance', 301);
        }
        
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        $ttm->gyakorlat_id = $id;
        $ttm->saveSettings();
        
        $this->container->flash->addMessage('system_message', 'A gyakorlati ora id mentese sikeres! (#' . $id . ")");
        return $resp->withRedirect('/main/maintenance', 301);
    }
    
    public function importToSeason($req, $resp)
    {
        if ( !$req->isPost()) return $resp->withRedirect('/main/general', 301);

        $season_origin_id = \App\Helper::get($req->getParsedBody(), 'season_origin_id', -1);
        $subject = \App\Helper::get($req->getParsedBody(), 'subject', 0);
        $schoolclass = \App\Helper::get($req->getParsedBody(), 'schoolclass', 0);
        $teacher = \App\Helper::get($req->getParsedBody(), 'teacher', 0);
       
        if ($subject != 1 && $schoolclass != 1 && $teacher != 1) {
            $this->container->flash->addMessage('system_message', 'Legalább 1 dolgot ki kell választani!');
            return $resp->withRedirect('/main/general', 301);
        }

        if ($season_origin_id == $this->seasonManager->active_season) {
            $this->container->flash->addMessage('system_message', 'Önmagától nem lehet importálni!');
            return $resp->withRedirect('/main/general', 301);
        }

        $r = "";
        if ($teacher == 1)
        {
            $r .= " tanárok";
            $teacherModel = new \App\Teacher($this->db);
            $teachersLookup = $teacherModel->cloneSeason($season_origin_id, $this->seasonManager->active_season);
        }

        if ($subject == 1)
        {
            $r .= " tantárgyak";
            $subjectModel = new \App\Subject($this->db);
            $subjectsLookup = $subjectModel->cloneSeason($season_origin_id, $this->seasonManager->active_season);
        }

        if ($schoolclass == 1)
        {
            $r .= " osztályok";
            $classModel = new \App\Schoolclass($this->db);
            $classLookup = $classModel->cloneSeason($season_origin_id, $this->seasonManager->active_season);
        }

        $this->container->flash->addMessage('system_message', 'Sikeres importálás: ' . $r);    
        return $resp->withRedirect('/main/general', 301);
    }

    public function renameSeason($req, $resp)
    {
        if ( !$req->isPost()) return $resp->withRedirect('/main/general', 301);

        $nev = \App\Helper::get($req->getParsedBody(), 'nev', '');
        $nev = trim($nev);
        $nev = filter_var($nev, FILTER_SANITIZE_STRING);        
        if (strlen($nev) < 1)
        {
            $this->container->flash->addMessage('system_message', 'A név megadása kötelező!');    
            return $resp->withRedirect('/main/general', 301);
        }

        $this->seasonManager->renameActiveSeason($nev);

        $this->container->flash->addMessage('system_message', 'Az aktív időszak új neve: ' . $nev);    
        return $resp->withRedirect('/main/general', 301);
    }

    public function edit_timetable($req, $resp)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        if ($ttm->tanitasi_hetek < 0)
        {
            $this->container->flash->addMessage('system_message', 'Nincsennek beallitva a felev adatai!');    
            return $resp->withRedirect('/main/index', 301);
        }

        return $this->render($resp, 'edit_layout.php', array(
            '_pagetitle' => 'Orarend - szerkesztes',
            '_page' => $this->fetch('main/edit_timetable.php')
        ));
        
    }

    public function changeSettings($req, $resp)
    {
        $post = $req->getParsedBody();
        $key = isset($post['key']) ? $post['key'] : null;
        $value = isset($post['value']) ? $post['value'] : null;

        $msg = "";
        $settingsManager = \App\SettingsManager::getInstance($this->db);
        if ($key != null) {
            if ($value != null) {
                $settingsManager->save($key, $value);
                $msg = 'Változtatások elmentve! ' . $key . ' = ' . $value;
            }
            else {
                $value = $settingsManager->get($key);
                $msg = 'A "' . $key . '" kulcs erteke = ' . $value;
            }
        }

        $this->container->flash->addMessage('system_message', $msg);
        return $resp->withRedirect('/main/maintenance', 301);
    }

    /**
     * A visszatero tomb felepitese:
     *      time: a script futtasi ideje
     *      error: hiba uzenet
     *      total: a kapott orarendi adatok szama
     *      saved: a mentett orarendi sorok szama
     *      errors: array, hibas orarendi adatok
     * @return array
     */
    public function save_timetable($req, $resp)
    {
        if ( !$req->isXhr()) return $resp->withJson(array('error' => 'Nem AJAX keres!'));
        if ( !$req->isPost()) return $resp->withJson(array('error' => 'Nem POST keres!'));
        
        $startTime = microtime(TRUE);

        $post = $req->getParsedBody();
        
        $orarend = isset($post['orarend']) ? $post['orarend'] : null;
        if ($orarend == null)
        {
            return $resp->withJson(array('error' => 'Nincs orarend adat!'));
        }
        
        $orarend = json_decode($orarend);
        
        if ( !is_array($orarend) )
        {
            return $resp->withJson(array('error' => 'Orarend nem tomb!'));
        }
        
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        $result = $ttm->saveTimetable($orarend);

        /*
        $ttm->utolso_mentes = date("Y.m.d G:i");
        $ttm->saveSettings();
        */
        $this->seasonManager->utolso_mentes = date("Y.m.d G:i");
        $ttm->utolso_mentes = $this->seasonManager->utolso_mentes;
        $this->seasonManager->save();

        $result['msg'] = "" . $ttm->utolso_mentes;
        $result['time'] = microtime(TRUE) - $startTime;
        
        return $resp->withJson($result);
    }

    public function logout($req, $resp)
    {
        $this->container->loginManager->logout();
        return $resp->withRedirect('/', 301);
    }

    
}