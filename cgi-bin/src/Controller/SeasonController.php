<?php
namespace App\Controller;

class SeasonController extends Controller {

    public function index($req, $resp) {
        $seasons = $this->seasonManager->getAll("ORDER BY season_id");

        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('season/index.php', array(
                'seasons' => $seasons,
                'active_season' => $this->seasonManager->active_season,
                'delurl' => $this->container['settings']['baseUrl'] . 'seasons/delete/'
            )),
            '_pagetitle' => 'Orarend'
        ));
    }

    public function create($req, $resp)
    {
        $season = $this->seasonManager->getEmptyForCreation();
        
        $errors = array();

        if ($req->isPost())
        {
            $season = $this->seasonManager->populateFromArray($req->getParsedBody());
            $errors = $this->seasonManager->validateCreationData($season);

            if (empty($errors))
            {
                $this->seasonManager->createSeason($season);

                $this->container->flash->addMessage('system_message', 'Új időszak rögzítése sikeres!');    
                return $resp->withRedirect('/seasons/index', 301);
            }
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_pagetitle' => 'Új időszak',
            '_page' => $this->fetch('season/create.php', array(
                'adatok' => $season,
                'errors' => $errors
            ))
        ));
    }

    public function delete($req, $resp, $args)
    {
        $id = (int)(isset($args['id']) ? $args['id'] : -1);
        
        $season = $this->seasonManager->get($id);
        if ($season == null) {
            $this->container->flash->addMessage('system_message', 'Nincs #' . $id . ' azonosítójú időszak!');
            return $resp->withRedirect('/seasons/index', 301);
        }

        $this->seasonManager->delete($id);

        $this->container->flash->addMessage('system_message', 'Az időszak sikeresen törölve!');        
        return $resp->withRedirect('/seasons/index', 301);        
    }

    public function activate($req, $resp, $args)
    {
        $id = (int)(isset($args['id']) ? $args['id'] : -1);

        $season = $this->seasonManager->get($id);
        if ($season == null) {
            $this->container->flash->addMessage('system_message', 'Nincs ' . $id . ' azonosítójú időszak!');
            return $resp->withRedirect('/seasons/index', 301);
        }

        $this->seasonManager->setActive($id);

        $this->container->flash->addMessage('system_message', 'Aktív időszak: ' . $season['nev']);        
        return $resp->withRedirect('/seasons/index', 301);        
    }

    public function cloneSeason($req, $resp)
    {
        if ( !$req->isPost()) return $resp->withRedirect('/seasons/index', 301);

        $nev = \App\Helper::get($req->getParsedBody(), 'season_nev', '');
        $masterSeasonId = (int)\App\Helper::get($req->getParsedBody(), 'season_master_id', -1);

        $nev = trim($nev);
        $nev = filter_var($nev, FILTER_SANITIZE_STRING);        
        if (strlen($nev) < 1)
        {
            $this->container->flash->addMessage('system_message', 'A név megadása kötelező!');    
            return $resp->withRedirect('/seasons/index', 301);
        }

        $masterSeason = $this->seasonManager->get($masterSeasonId);
        if ($masterSeason == null) {
            $this->container->flash->addMessage('system_message', 'Nincs ilyen időszak, id = ' . masterSeasonId);    
            return $resp->withRedirect('/seasons/index', 301);
        }

        $this->logger->info("---------------- Idoszak klonozas kezdese - " . $masterSeasonId);
        //  uj season letrehozasa
        $newSeason = $this->seasonManager->cloneSeason($masterSeason, $nev);
        $this->logger->info("- Idoszak klonozas kesz");

        $teacherModel = new \App\Teacher($this->db);
        $teachersLookup = $teacherModel->cloneSeason($masterSeasonId, $newSeason['season_id']);
        $this->logger->info("- Tanar klonozas kesz");

        $subjectModel = new \App\Subject($this->db);
        $subjectsLookup = $subjectModel->cloneSeason($masterSeasonId, $newSeason['season_id']);
        $this->logger->info("- Tantargy klonozas kesz");

        $classModel = new \App\Schoolclass($this->db);
        $classLookup = $classModel->cloneSeason($masterSeasonId, $newSeason['season_id']);
        $this->logger->info("- Osztaly klonozas kesz");

        $mLesson = new \App\Lesson($this->db);
        $lessonLookup = $mLesson->cloneSeason($masterSeasonId, $newSeason['season_id'], $teachersLookup, $subjectsLookup, $classLookup);
        $this->logger->info("- Tanorak klonozas kesz");

        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        $ttm->cloneSeason($masterSeasonId, $newSeason['season_id'], $lessonLookup);
        $this->logger->info("- Orarend klonozas kesz");
        $this->logger->info("------------- Idoszak klonozas kesz");

        $this->container->flash->addMessage('system_message', 'Új időszak létrehozva: ' . $nev);    
        return $resp->withRedirect('/seasons/index', 301);
    }
}