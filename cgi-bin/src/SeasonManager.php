<?php
namespace App;

use Respect\Validation\Validator as v;

class SeasonManager {

    public $active_season = -1;
    public $season_name = "-";
    public $napi_oraszam = 8;
    public $kezdo_oraszam = 8;
    public $elso_tanitasi_nap = ''; // '2018.09.03';
    public $utolso_tanitasi_nap = ''; // '2019.01.25';
    public $nincs_tanitas = array();
    //  a ket datum kozotti naptari hetek (szamitott)
    public $hetek_szama = -1;
    //  hivatalosan ennyi het van a felevben
    public $tanitasi_hetek = -1;
    public $utolso_mentes = '';
    public $heti_max_oraszam = 18;

    private static $_instance = null;

    private $pdo;
    private $settingsManager;

    public static function instance($pdo) {
        if (self::$_instance == null) {
            self::$_instance = new SeasonManager($pdo);
        }
        return self::$_instance;
    }

    private function __construct($pdo) {
        $this->pdo = $pdo;
        $this->settingsManager = \App\SettingsManager::getInstance($this->pdo);
    }

    public function getEmptyForCreation() {
        return array(
            'nev' => '',
            'firstday' => '',
            'lastday' => '',
            'lessonperday' => '',
            'starttime' => '',
            'weeksnum' => ''
        );
    }

    // csak a letrehozas adataival foglalkozik
    public function populateFromArray($data)
    {
        return array(
            'nev' => \App\Helper::get($data, 'nev', ''),
            'firstday' => \App\Helper::get($data, 'firstday', ''),
            'lastday' => \App\Helper::get($data, 'lastday', ''),
            'lessonperday' => (int)\App\Helper::get($data, 'lessonperday', 0),
            'starttime' => (int)\App\Helper::get($data, 'starttime', 0),
            'weeksnum' => (int)\App\Helper::get($data, 'weeksnum', 0)
        );
    }

    public function hasActiveSeason() {
        return ($this->active_season > -1);
    }

    //  helper fuggveny
    public function getDates()
    {
        return $this->elso_tanitasi_nap . ' - ' . $this->utolso_tanitasi_nap;
    }

    //  ===========================================================
    //  ez lehet, hogy nem fog kelleni
    //  egyelore igy oldom meg
    //  ===========================================================
    public function populateTimetableManager($ttm)
    {
        $ttm->season_id = $this->active_season;
        $ttm->napi_oraszam = $this->napi_oraszam;
        $ttm->kezdo_oraszam = $this->kezdo_oraszam;
        $ttm->elso_tanitasi_nap = $this->elso_tanitasi_nap;
        $ttm->utolso_tanitasi_nap = $this->utolso_tanitasi_nap;
        $ttm->nincs_tanitas = $this->nincs_tanitas;
        $ttm->hetek_szama = $this->hetek_szama;
        $ttm->tanitasi_hetek = $this->tanitasi_hetek;
        $ttm->utolso_mentes = $this->utolso_mentes;
        $ttm->heti_max_oraszam = $this->heti_max_oraszam;
    
    }

    //  azok a napok, amikor nincs tanitas
    public function setExceptionDates($list)
    {
        $this->nincs_tanitas = array();
        foreach($list as $d) $this->nincs_tanitas[] = $d;
    }

    public function loadActiveSeason()
    {
        $this->active_season = $this->settingsManager->get('active_season');

        if ($this->active_season < 0)
        {
            $this->season_name = "-";
            $this->elso_tanitasi_nap = ''; 
            $this->utolso_tanitasi_nap = '';
            return;
        }

        $row = $this->get($this->active_season);

        $this->season_name = $row['nev'];
        $this->napi_oraszam = $row['napi_oraszam'];
        $this->kezdo_oraszam = $row['kezdo_oraszam'];
        $this->elso_tanitasi_nap = $row['elso_tanitasi_nap'];
        $this->utolso_tanitasi_nap = $row['utolso_tanitasi_nap'];
        $this->nincs_tanitas = $row['nincs_tanitas'];
        $this->hetek_szama = $row['hetek_szama'];
        $this->tanitasi_hetek = $row['tanitasi_hetek'];
        $this->utolso_mentes = $row['utolso_mentes'];
        $this->heti_max_oraszam = $row['heti_max_oraszam'];
    }

    public function get($id) 
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tt_seasons WHERE season_id = ?;');
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->columnCount() < 1) return null;
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $row['nincs_tanitas'] = unserialize($row['nincs_tanitas']);
        return $row;
    }

    public function getAll($order = '')
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tt_seasons ' . $order . ';');
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($result as $row) {
            $row['nincs_tanitas'] = unserialize($row['nincs_tanitas']);
        }
        return $result;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM tt_lessons WHERE season_id = ?;');
        $stmt->execute(array($id));

        $stmt = $this->pdo->prepare('DELETE FROM tt_schoolclasses WHERE season_id = ?;');
        $stmt->execute(array($id));

        $stmt = $this->pdo->prepare('DELETE FROM tt_subjects WHERE season_id = ?;');
        $stmt->execute(array($id));

        $stmt = $this->pdo->prepare('DELETE FROM tt_teachers WHERE season_id = ?;');
        $stmt->execute(array($id));

        $stmt = $this->pdo->prepare('DELETE FROM tt_timetable WHERE season_id = ?;');
        $stmt->execute(array($id));

        $stmt = $this->pdo->prepare('DELETE FROM tt_seasons WHERE season_id = ?;');
        $stmt->execute(array($id));

        $this->setActive(-1);
    }

    public function insert($data)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tt_seasons(nev, elso_tanitasi_nap, utolso_tanitasi_nap, napi_oraszam, ' .
            'kezdo_oraszam, nincs_tanitas, hetek_szama, tanitasi_hetek, heti_max_oraszam) ' .
            'VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);');

        $stmt->execute(array($data['nev'], $data['elso_tanitasi_nap'], $data['utolso_tanitasi_nap'],
            $data['napi_oraszam'], $data['kezdo_oraszam'], serialize($data['nincs_tanitas']),
            $data['hetek_szama'], $data['tanitasi_hetek'], $data['heti_max_oraszam'] ));
        
        return $this->pdo->lastInsertId();
    }

    public function save()
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tt_seasons SET nev = ?, elso_tanitasi_nap = ?, utolso_tanitasi_nap = ?, ' .
            'napi_oraszam = ?, kezdo_oraszam = ?, nincs_tanitas = ?, hetek_szama = ?, ' .
            'tanitasi_hetek = ?, heti_max_oraszam = ? ' .
            'WHERE season_id = ?;');
        
        $stmt->execute(array(
            $this->season_name, $this->elso_tanitasi_nap, $this->utolso_tanitasi_nap,
            $this->napi_oraszam, $this->kezdo_oraszam, serialize($this->nincs_tanitas),
            $this->hetek_szama, $this->tanitasi_hetek, $this->heti_max_oraszam, $this->active_season ));
    }

    public function createSeason($season)
    {
        $firstdate = \DateTime::createFromFormat('Y.m.d', $season['firstday']);
        $lastdate = \DateTime::createFromFormat('Y.m.d', $season['lastday']);
        $interval = $firstdate->diff($lastdate);
        $days = $interval->format('%a');
        $weeks = ceil($days / 7);
        
        $data = array();
        $data['nev'] = $season['nev'];
        $data['elso_tanitasi_nap'] = $season['firstday'];
        $data['utolso_tanitasi_nap'] = $season['lastday'];
        $data['napi_oraszam'] = $season['lessonperday'];
        $data['kezdo_oraszam'] = $season['starttime'];
        $data['tanitasi_hetek'] = $season['weeksnum'];
        $data['hetek_szama'] = $weeks;
        $data['heti_max_oraszam'] = 18;
        $data['nincs_tanitas'] = array();

        $this->insert($data);
    }

    public function cloneSeason($masterSeason, $nev)
    {
        $newSeason = $masterSeason;
        $newSeason['nev'] = $nev;
        $newSeasonId = $this->insert($newSeason);
        $newSeason['season_id'] = $newSeasonId;
        return $newSeason;
    }

    public function setActive($id)
    {
        $this->settingsManager->save('active_season', $id);
        $this->loadActiveSeason();
    }

    public function renameActiveSeason($nev)
    {
        $this->season_name = $nev;
        $this->save();
    }

    public function validateCreationData($data)
    {
        $rules = array(
            'nev' => v::notEmpty(),
            'firstday' => v::notEmpty()->noWhitespace(),
            'lastday' => v::notEmpty()->noWhitespace(),
            'lessonperday' => v::intType()->notEmpty()->positive(),
            'starttime' => v::intType()->notEmpty()->not(v::negative()),
            'weeksnum' => v::intType()->notEmpty()->positive()
        );
        
        $validator = new \App\Validator();
        $validator->validateArray($data, $rules);
        if ($validator->hasError()) return $validator->getErrors();
        
        $dateValidator = new \App\DateValidator();
        
        if ( !$dateValidator->validateDate($data['firstday'], 'Y.m.d'))
        {
            return array('firstday' => array('Első tanítűsi nap: érvénytelen dátum'));
        }
        
        if (!$dateValidator->validateDate($data['lastday'], 'Y.m.d'))
        {
            return array('lastday' => array('Utolsó tanítási nap: érvénytelen dátum'));
        }
        
        $firstdate = \DateTime::createFromFormat('Y.m.d', $data['firstday']);
        $lastdate = \DateTime::createFromFormat('Y.m.d', $data['lastday']);

        if ($firstdate == $lastdate)
        {
            return array('dates' => array('A két dátum nem lehet egyforma!'));
        }
        
        if ($firstdate > $lastdate)
        {
            return array('dates' => array('Az első tanítási napnak korábbinak kell lennie, mint az utolsó tanítási nap!'));
        }
        
        return array();
    }

}