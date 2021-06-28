<?php
namespace App;

use Respect\Validation\Validator as v;

class TimetableManager
{
    public $kretaHeader = array(
        'Hetirend',
        'Nap',
        'Óra (az adott napon belül)',
        'Osztály',
        'Csoport',
        'Tantárgy',
        'Tanár',
        'Helyiség'
    );
    public $kretaHeaderWithValid = array(
        'Óra érvényességének kezdete',
        'Óra érvényességének vége',
        'Hetirend',
        'Nap',
        'Óra (adott napon belül)',
        'Osztály',
        'Csoport',
        'Tantárgy',
        'Tanár',
        'Helyiség'
    );


    public $season_id;
    public $napi_oraszam = 8;
    public $kezdo_oraszam = 8;
    public $elso_tanitasi_nap = '2018.09.03';
    public $utolso_tanitasi_nap = '2019.01.25';
    public $nincs_tanitas = array();
    //  a ket datum kozotti naptari hetek (szamitott)
    public $hetek_szama = -1;
    //  hivatalosan ennyi het van a felevben
    public $tanitasi_hetek = -1;
    public $utolso_mentes = '';
    public $heti_max_oraszam = 18;
    public $export_kivetelek = array();

    private $saveNames_del = array('napi_oraszam', 'kezdo_oraszam', 'elso_tanitasi_nap', 'utolso_tanitasi_nap',
        'nincs_tanitas', 'hetek_szama', 'tanitasi_hetek', 'utolso_mentes', 'heti_max_oraszam', 'export_kivetelek');
    
    //private $fileName = "tt_settings";
    //private $filePath;
    
    private $db;
    private $seasonManager;
    private $settingsManager;
            
    public function __construct($pdo, $seasonManager)
    {
        $this->db = $pdo;
        
        $this->settingsManager = \App\SettingsManager::getInstance($pdo);
        $this->seasonManager = $seasonManager;

        //  nem a legjobb megoldas, de egyelore megfelel
        $this->seasonManager->populateTimetableManager($this);
        /*
        $this->filePath = __DIR__.DIRECTORY_SEPARATOR.$this->fileName;
        $this->loadSettings();
        */
    }
    
    //  DELETE
    //  nem kell mert a SeasonManager-t hasznaljuk helyette
    private function loadSettings_del()
    {
        $values = $this->settingsManager->getMultiple($this->saveNames);
        foreach($values as $key => $value) {
            if ($value != null) {
                $this->$key = $value;
            }
        }
        
        /*
        if ( !file_exists($this->filePath))
        {
            $settings = $this->prepareSettingsToSave();
            file_put_contents($this->filePath, json_encode($settings));
            return;
        }
        
        $settings = json_decode(file_get_contents($this->filePath), true);
        foreach($settings as $key => $value)
        {
            $this->$key = $value;
        }
        */
    }
    
    //  DELETE
    //  nem kell mert a SeasonManager-t hasznaljuk helyette
    public function saveSettings_del()
    {
        $settings = $this->prepareSettingsToSave();
        //file_put_contents($this->filePath, json_encode($settings));

        $this->settingsManager->saveMultiple($settings);
    }
    
    //  DELETE
    //  nem kell mert a SeasonManager-t hasznaljuk helyette
    private function prepareSettingsToSave_Del()
    {
        $r = array();
        foreach($this->saveNames as $n)
        {
            $r[$n] = $this->$n;
        }
        return $r;
    }
    
    //  DELETE
    //  atalakitja a szamokat int-re
    public function getExportExceptions_del()
    {
        $a = explode(";", $this->export_kivetelek);
        $r = array();
        foreach($a as $v) $r[] = (int)$v;
        return $r;
    }
    
    //  DELETE
    //  nem kell mert a SeasonManager-t hasznaljuk helyette
    public function setupNewTimetable_del($firstday, $lastday, $lessonperday, $starttime, $weeksnum)
    {
        $firstdate = \DateTime::createFromFormat('Y.m.d', $firstday);
        $lastdate = \DateTime::createFromFormat('Y.m.d', $lastday);
        $interval = $firstdate->diff($lastdate);
        $days = $interval->format('%a');
        $weeks = ceil($days / 7);
        
        $stmt = $this->db->exec('TRUNCATE TABLE tt_timetable');
        
        $this->elso_tanitasi_nap = $firstday;
        $this->utolso_tanitasi_nap = $lastday;
        $this->napi_oraszam = $lessonperday;
        $this->kezdo_oraszam = $starttime;
        $this->tanitasi_hetek = $weeksnum;
        $this->hetek_szama = $weeks;
        $this->nincs_tanitas = array();
        $this->saveSettings();
    }

    /**
     * Az id-k helyett szoveg van.
     * @return array(id, tanar, tantargy, osztaly, num, free_num)
     */
    public function getLessonsForHumans($limit = 0, $startIndex = 0)
    {
        //SELECT l.id, tn.nev, tt.nev FROM lessons AS l LEFT JOIN tanarok AS tn ON l.teacher_id = tn.id LEFT JOIN tantargyak AS tt ON l.subject_id = tt.id;
        
        $limitStr = '';
        if ($limit > 0) {
            if ($startIndex == 0) $limitStr = ' LIMIT ' . $limit;
            else $limitStr = ' LIMIT ' . $startIndex . ', ' . $limit;
        }
        
//        $sql =  'SELECT lessons.id AS id, teachers.name AS tanar, subjects.name AS tantargy, schoolclasses.name AS osztaly, lessons.num, lessons.free_num ' .
//                'FROM lessons ' .
//                'LEFT JOIN teachers ON lessons.teacher_id = teachers.id ' .
//                'LEFT JOIN subjects ON lessons.subject_id = subjects.id ' . 
//                'LEFT JOIN schoolclasses ON lessons.class_id = schoolclasses.id ' .
//                'ORDER BY osztaly, tanar, tantargy ' .
//                $limitStr;
        
        $sql =  'SELECT tt_lessons.id AS id, tt_teachers.name AS tanar, tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_lessons.num, ' .
                '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = tt_lessons.id) AS num_in_tt, ' .
                '(tt_lessons.num - @dbnum) AS free_num, tt_lessons.practice AS gyakorlat ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.season_id = ' . $this->season_id . ' ' .
                'ORDER BY osztaly, tanar, tantargy ' .
                $limitStr;
        
        $stmt = $this->db->query($sql);
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }
    
    /**
     * Egy osztaly orai.
     * @param int $classid
     * @return array
     */
    public function getLessonsOfClassForHumans($classid)
    {
        $stmt = $this->db->prepare(
                'SELECT tt_lessons.id AS id, tt_teachers.name AS tanar, tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_lessons.num, ' .
                '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = tt_lessons.id) AS num_in_tt, ' .
                'tt_lessons.practice AS gyakorlat, ' .
                '(tt_lessons.num - @dbnum) AS free_num ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.class_id = ? AND tt_lessons.season_id = ? ' .
                'ORDER BY tanar, tantargy;');
        $stmt->bindValue(1, $classid, \PDO::PARAM_INT);
        $stmt->bindValue(2, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }

    /**
     * Egy tanar orai.
     * @param int $teacherid
     * @return array
     */
    public function getLessonsOfTeacherForHumans($teacherid)
    {
        $stmt = $this->db->prepare(
                'SELECT tt_lessons.id AS id, tt_teachers.name AS tanar, tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_lessons.num, ' .
                '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = tt_lessons.id) AS num_in_tt, ' .
                '(tt_lessons.num - @dbnum) AS free_num, tt_lessons.practice AS gyakorlat ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.teacher_id = ? AND tt_lessons.season_id = ? ' .
                'ORDER BY osztaly, tantargy;');
        $stmt->bindValue(1, $teacherid, \PDO::PARAM_INT);
        $stmt->bindValue(2, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }

    /**
     * Azok a tanorak, amelyekben szerepel a parameterben kapott tantargy.
     */
    public function getLessonsBySubjectForHumans($subjectId) {
        $stmt = $this->db->prepare(
                'SELECT tt_lessons.id AS id, tt_teachers.name AS tanar, tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_lessons.num, ' .
                'tt_lessons.num  AS num, ' .
                'tt_teachers.id AS tanarid, tt_schoolclasses.id AS osztalyid ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.subject_id = ? AND tt_lessons.season_id = ? ' .
                'ORDER BY osztaly, tantargy;');
        $stmt->bindValue(1, $subjectId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }

    public function getLessonsSum($lessons)
    {
        $sum = 0;
        foreach($lessons as $l) $sum += $l['num'];
        return $sum;
    }

    public function getPracticeLessonsSum($lessons) {
        $sum = 0;
        foreach($lessons as $l) {
            if ($l['gyakorlat']) $sum += $l['num'];
        }
        return $sum;
    }

    public function getLessonsToJs()
    {
        $stmt = $this->db->query(
                'SELECT tt_lessons.id AS id, tt_teachers.id AS teacherid, tt_teachers.name AS tanar, tt_teachers.short_name AS shortname, tt_subjects.name AS tantargy, tt_subjects.id AS tantargyid, tt_schoolclasses.id AS osztalyid, tt_schoolclasses.short_name AS osztalynev, tt_lessons.num AS oraszam, ' .
                '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = tt_lessons.id) AS num_in_tt, ' .
                '(tt_lessons.num - @dbnum) AS szabadoraszam ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.season_id = ' . $this->season_id . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) {
//            $result[ $v['id'] ] = $v;
            $result[] = array(
                'id' => (int)$v['id'],
                'tanarteljes' => $v['tanar'],
                'tanarid' => $v['teacherid'],
                'tanar' => $v['shortname'],
                'tantargy' => $v['tantargy'],
                'tantargyid' => $v['tantargyid'],
                'osztalyid' => (int)$v['osztalyid'],
                'osztalynev' => $v['osztalynev'],
                'osszoraszam' => (int)$v['oraszam'],
                'oraszam' => (int)$v['szabadoraszam'],
                '_oraszam' => (int)$v['szabadoraszam']
            );
        }
        return json_encode($result);
    }

    public function getLessonstoApi()
    {
        $stmt = $this->db->query(
                'SELECT tt_lessons.id AS id, tt_teachers.id AS teacherid, tt_teachers.name AS tanar, tt_teachers.short_name AS shortname, tt_subjects.name AS tantargy, tt_subjects.id AS tantargyid, tt_schoolclasses.id AS osztalyid, tt_schoolclasses.short_name AS osztalynev, tt_lessons.num AS oraszam, tt_lessons.practice AS practice, ' .
                '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = tt_lessons.id) AS num_in_tt, ' .
                '(tt_lessons.num - @dbnum) AS szabadoraszam ' .
                'FROM tt_lessons ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' . 
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_lessons.season_id = ' . $this->season_id . ' ' .
                'ORDER BY tanar, osztalynev, tantargy;');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) {
        // $result[ $v['id'] ] = $v;
            $result[] = array(
                'id' => (int)$v['id'],
                'tanarteljes' => $v['tanar'],
                'tanarid' => $v['teacherid'],
                'tanar' => $v['shortname'],
                'tantargy' => $v['tantargy'],
                'tantargyid' => $v['tantargyid'],
                'osztalyid' => (int)$v['osztalyid'],
                'osztalynev' => $v['osztalynev'],
                'osszoraszam' => (int)$v['oraszam'],
                'oraszam' => (int)$v['szabadoraszam'],
                '_oraszam' => (int)$v['szabadoraszam'],
                'practice' => (int)$v['practice']
            );
        }
        return $result;
    }

    /*
     * tomb felepitese = array(
     *      array(
     *          van_tanitas => logikai
     *          datum => Y.m.d formatumu datum
     *      )
     * )
     */
    public function getSemesterDates()
    {
        $dateHelper = new \App\DateHelper();
        
        $currentDate = \DateTime::createFromFormat('Y.m.d', $this->elso_tanitasi_nap);
        $endDate = \DateTime::createFromFormat('Y.m.d', $this->utolso_tanitasi_nap);
        
        $kivetelek = $this->nincs_tanitas;
        
        $napok = array();
        while($currentDate <= $endDate)
        {
            $nap = $currentDate->format('N');
            if ($nap > 5)
            {
                $currentDate->add(new \DateInterval('P1D'));
                continue;
            }
            
            $d = $currentDate->format('Y.m.d');
            $napok[] = array(
                'van_tanitas' => !in_array($d, $kivetelek),
                'datum' => $d,
                'nap_neve' => $dateHelper->getDayName($currentDate)
            );
            
            $currentDate->add(new \DateInterval('P1D'));
        }
        
        return $napok;
    }
    
    /*
     * A teljes orarend olyan formaban, amit a JS-nek jo.
     */
    public function getTimetableToJs()
    {
        $mClass = new \App\Schoolclass($this->db);
        
        $osztalyok = $mClass->getAll();
        $napok = $this->getSemesterDates();
        
        $stmt = $this->db->query(
                "SELECT DATE_FORMAT(tt_timetable.ttdate, '%Y.%m.%d') AS datum, tt_timetable.lesson_id AS lessonid, tt_timetable.position AS pos, tt_lessons.class_id AS osztalyid " .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' . 
                'WHERE tt_timetable.season_id = ' . $this->season_id . ';');
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $orarend = array();
        foreach($resultSet as $row) {
            $orarend[$row['datum'] . '@' . $row['osztalyid'] . '@' . $row['pos']] = (int)$row['lessonid'];
        }
        
        
        $weekCounter = -1;
        $week = -1;
        
        $result = array();
        foreach($napok as $nap)
        {
            $dateid = $nap['datum'];
            $date = \DateTime::createFromFormat('Y.m.d', $dateid);
            
            if ($weekCounter < 0)
            {
                $weekCounter = 0;
                $week = $date->format('W');
            }
            else
            {
                if ($date->format('W') != $week)
                {
                    $week = $date->format('W');
                    $weekCounter += 1;
                }
            }
            
            $result[$dateid] = array(
                'het' => $weekCounter,
                'van_tanitas' => $nap['van_tanitas']
            );
            
            foreach($osztalyok as $osztaly)
            {
                $oid = 'oid' . $osztaly['id'];
                
                $result[$dateid][$oid] = array(
                    'van_tanitas' => ($osztaly['day' . $date->format('N')] == 1)
                );
                for($i = 0; $i < $this->napi_oraszam; $i++)
                {
                    //  ha van ora: lesson_id
                    //  else -1
                    if (isset($orarend[$dateid . '@' . $osztaly['id'] . '@' . $i])) $result[$dateid][$oid][$i] = $orarend[$dateid . '@' . $osztaly['id'] . '@' . $i];
                    else $result[$dateid][$oid][$i] = -1;
                }
            }
        }
        
        return $result;
    }

    /**
     * Az eredmenybe beleveszi a kezdo es a zaro datumot is.
     * @param string $startDate format: Y-m-d
     * @param string $endDate  format: Y-m-d
     * @return type
     */
    public function getTimetableForKreta($startDate, $endDate)
    {
        $dateHelper = new \App\DateHelper();
        
        $kezdoOraszam = $this->kezdo_oraszam;

//SELECT timetable.ttdate AS datum, timetable.position AS idopont, timetable.lesson_id AS lesson_id, subjects.name AS tantargy, schoolclasses.name AS osztaly, teachers.name AS tanar FROM timetable LEFT JOIN lessons ON timetable.lesson_id = lessons.id LEFT JOIN teachers ON lessons.teacher_id = teachers.id LEFT JOIN subjects ON lessons.subject_id = subjects.id LEFT JOIN schoolclasses ON lessons.class_id = schoolclasses.id WHERE DATE(datum) >= "2018-09-03" AND DATE(datum) <= "2018-09-04";
                
        //hetirend, nap, ora (az adott napon belul), osztaly, csoport, tantargy, tanar, helyiseg<br/>
        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE DATE(tt_timetable.ttdate) >= ? AND DATE(tt_timetable.ttdate) <= ? AND tt_timetable.season_id = ? ' .
                'ORDER BY tt_timetable.ttdate, tt_schoolclasses.id, tt_timetable.position;');
        $stmt->bindValue(1, $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(2, $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(3, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $result = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $result[] = array(
                'datum' => $v['datum'],
                'lesson_id' => $v['lesson_id'],
                'nap' => $dateHelper->getDayName($d),
                'ora' => (int)($v['idopont'] + $kezdoOraszam),
                'osztaly' => $v['osztaly'],
                'tantargy' => $v['tantargy'],
                'tanar' => $v['tanar']
            );
        }
        return $result;
    }

    public function getExcelForKreta($startDate, $endDate)
    {
        $dateHelper = new \App\DateHelper();
        
        $kezdoOraszam = $this->kezdo_oraszam;

        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE DATE(tt_timetable.ttdate) >= ? AND DATE(tt_timetable.ttdate) <= ? AND tt_timetable.season_id = ? ' .
                'ORDER BY tt_timetable.ttdate, osztaly, tt_timetable.position;');
        $stmt->bindValue(1, $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(2, $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(3, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $len = count($this->kretaHeader);
        for($i = 0; $i < $len; $i++)
        {
            //  oszlop, sor, cella ertek
            $as->setCellValueByColumnAndRow(($i+1), 1, $this->kretaHeader[$i]);
        }
        
        $row = 2;
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            //hetirend, nap, ora (az adott napon belul), osztaly, csoport, tantargy, tanar, helyiseg            
            $as->setCellValueByColumnAndRow(1, $row, 'Minden héten');
            $as->setCellValueByColumnAndRow(2, $row, $dateHelper->getDayName($d));
            $as->setCellValueByColumnAndRow(3, $row, (int)($v['idopont'] + $kezdoOraszam));
            $as->setCellValueByColumnAndRow(4, $row, $v['osztaly']);
            $as->setCellValueByColumnAndRow(5, $row, '');
            $as->setCellValueByColumnAndRow(6, $row, $v['tantargy']);
            $as->setCellValueByColumnAndRow(7, $row, $v['tanar']);
            $as->setCellValueByColumnAndRow(8, $row, '132');
            
            $row += 1;
        }
        
        return $spreadsheet;
    }

    //  teljes felev
    public function getExcelForKretaFull($startDate, $endDate)
    {
        $dateHelper = new \App\DateHelper();
        
        $kezdoOraszam = $this->kezdo_oraszam;

        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar, ' .
                'gt1.name AS csoport1, gt2.name AS csoport2 ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'LEFT JOIN tt_teachers gt1 ON tt_lessons.group_1_teacher_id = gt1.id ' .
                'LEFT JOIN tt_teachers gt2 ON tt_lessons.group_2_teacher_id = gt2.id ' .
                'WHERE DATE(tt_timetable.ttdate) >= ? AND DATE(tt_timetable.ttdate) <= ? AND tt_timetable.season_id = ? ' .
                'ORDER BY tt_timetable.ttdate, osztaly, tt_timetable.position;');
        $stmt->bindValue(1, $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(2, $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(3, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $len = count($this->kretaHeader);
        $as->setCellValueByColumnAndRow(1, 1, 'Dátum');
        for($i = 0; $i < $len; $i++)
        {
            //  oszlop, sor, cella ertek
            $as->setCellValueByColumnAndRow(($i+2), 1, $this->kretaHeader[$i]);
        }
        
        $row = 2;
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $tanar = $v['tanar'];
            $csoport = "";

            if ($v['csoport1'] != null) {
                $tanar = $v['csoport1'];
                $csoport = "" . $v['osztaly'] . " 2-1";
            }

            // datum, hetirend, nap, ora (az adott napon belul), osztaly, csoport, tantargy, tanar, helyiseg            
            $as->setCellValueByColumnAndRow(1, $row, $d->format('Y.m.d'));
            $as->setCellValueByColumnAndRow(2, $row, 'Minden héten');
            $as->setCellValueByColumnAndRow(3, $row, $dateHelper->getDayName($d));
            $as->setCellValueByColumnAndRow(4, $row, (int)($v['idopont'] + $kezdoOraszam));
            $as->setCellValueByColumnAndRow(5, $row, $v['osztaly']);
            $as->setCellValueByColumnAndRow(6, $row, $csoport);
            $as->setCellValueByColumnAndRow(7, $row, $v['tantargy']);
            $as->setCellValueByColumnAndRow(8, $row, $tanar);
            $as->setCellValueByColumnAndRow(9, $row, '132');
            
            if ($v['csoport1'] != null) {
                $tanar = $v['csoport2'];
                $csoport = $v['osztaly'] . " 2-2";
                
                $row += 1;

                $as->setCellValueByColumnAndRow(1, $row, $d->format('Y.m.d'));
                $as->setCellValueByColumnAndRow(2, $row, 'Minden héten');
                $as->setCellValueByColumnAndRow(3, $row, $dateHelper->getDayName($d));
                $as->setCellValueByColumnAndRow(4, $row, (int)($v['idopont'] + $kezdoOraszam));
                $as->setCellValueByColumnAndRow(5, $row, $v['osztaly']);
                $as->setCellValueByColumnAndRow(6, $row, $csoport);
                $as->setCellValueByColumnAndRow(7, $row, $v['tantargy']);
                $as->setCellValueByColumnAndRow(8, $row, $tanar);
                $as->setCellValueByColumnAndRow(9, $row, '132');
            }
            
            $row += 1;
        }
        
        return $spreadsheet;
    }

    //  teljes felev, ervenyessegi idovel
    public function getExcelForKretaFullWithValidColumn($startDate, $endDate)
    {
        $dateHelper = new \App\DateHelper();
        
        $kezdoOraszam = $this->kezdo_oraszam;
        /*
        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar, ' .
                'tt_lessons.group_1_teacher_id, tt_lessons.group_2_teacher_id ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE DATE(tt_timetable.ttdate) >= ? AND DATE(tt_timetable.ttdate) <= ? ' .
                'ORDER BY tt_timetable.ttdate, osztaly, tt_timetable.position;');
        */
        $stmt = $this->db->prepare(
            'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
            'tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar, ' .
            'tt_lessons.group_1_teacher_id, tt_lessons.group_2_teacher_id, ' .
            'gt1.name AS csoport1, gt2.name AS csoport2 ' .
            'FROM tt_timetable ' .
            'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
            'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
            'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
            'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
            'LEFT JOIN tt_teachers gt1 ON tt_lessons.group_1_teacher_id = gt1.id ' .
            'LEFT JOIN tt_teachers gt2 ON tt_lessons.group_2_teacher_id = gt2.id ' .
            'WHERE DATE(tt_timetable.ttdate) >= ? AND DATE(tt_timetable.ttdate) <= ? AND tt_timetable.season_id = ? ' .
            'ORDER BY tt_timetable.ttdate, osztaly, tt_timetable.position;');

/*
                SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, tt_subjects.name AS tantargy, tt_schoolclasses.name AS osztaly, tt_teachers.name AS tanar,        tt_lessons.group_1_teacher_id, gt1.name AS csoport1, tt_lessons.group_2_teacher_id, gt2.name AS csoport2 
                FROM tt_timetable
                LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id
                LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id
                LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id
                LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id
                LEFT JOIN tt_teachers gt1 ON tt_lessons.group_1_teacher_id = gt1.id 
                LEFT JOIN tt_teachers gt2 ON tt_lessons.group_2_teacher_id = gt2.id 
                WHERE DATE(tt_timetable.ttdate) >= '2021-03-02' AND DATE(tt_timetable.ttdate) <= '2021-03-02'
                ORDER BY tt_timetable.ttdate, osztaly, tt_timetable.position;                
            */
        $stmt->bindValue(1, $startDate, \PDO::PARAM_STR);
        $stmt->bindValue(2, $endDate, \PDO::PARAM_STR);
        $stmt->bindValue(3, $this->season_id, \PDO::PARAM_INT);
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $len = count($this->kretaHeaderWithValid);
        for($i = 0; $i < $len; $i++)
        {
            //  oszlop, sor, cella ertek
            $as->setCellValueByColumnAndRow(($i+1), 1, $this->kretaHeaderWithValid[$i]);
        }
        
        $row = 2;
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);

            $csoport = "";
            $tanar = $v['tanar'];
            
            if ($v['csoport1'] != null) {
                $tanar = $v['csoport1'];
                $csoport = "" . $v['osztaly'] . " 2-1";
            }

            // Óra érvényességének kezdete, Óra érvényességének vége, hetirend, nap, ora (az adott napon belul), osztaly, csoport, tantargy, tanar, helyiseg            
            $as->setCellValueByColumnAndRow(1, $row, $d->format('Y.m.d'));
            $as->setCellValueByColumnAndRow(2, $row, $d->format('Y.m.d'));
            $as->setCellValueByColumnAndRow(3, $row, 'Minden héten');
            $as->setCellValueByColumnAndRow(4, $row, $dateHelper->getDayName($d));
            $as->setCellValueByColumnAndRow(5, $row, (int)($v['idopont'] + $kezdoOraszam));
            $as->setCellValueByColumnAndRow(6, $row, $v['osztaly']);
            $as->setCellValueByColumnAndRow(7, $row, $csoport);
            $as->setCellValueByColumnAndRow(8, $row, $v['tantargy']);
            $as->setCellValueByColumnAndRow(9, $row, $tanar);
            $as->setCellValueByColumnAndRow(10, $row, '132');
            
            if ($v['csoport1'] != null) {
                $tanar = $v['csoport2'];
                $csoport = $v['osztaly'] . " 2-2";
                
                $row += 1;

                $as->setCellValueByColumnAndRow(1, $row, $d->format('Y.m.d'));
                $as->setCellValueByColumnAndRow(2, $row, $d->format('Y.m.d'));
                $as->setCellValueByColumnAndRow(3, $row, 'Minden héten');
                $as->setCellValueByColumnAndRow(4, $row, $dateHelper->getDayName($d));
                $as->setCellValueByColumnAndRow(5, $row, (int)($v['idopont'] + $kezdoOraszam));
                $as->setCellValueByColumnAndRow(6, $row, $v['osztaly']);
                $as->setCellValueByColumnAndRow(7, $row, $csoport);
                $as->setCellValueByColumnAndRow(8, $row, $v['tantargy']);
                $as->setCellValueByColumnAndRow(9, $row, $tanar);
                $as->setCellValueByColumnAndRow(10, $row, '132');
    
            }

            $row += 1;
        }
        
        return $spreadsheet;
    }

    /**
     * A teljes orarend Asc-hez hasonlo formatumban.
     * tomb felepitese: [datum][osztaly][ora] => array(
     *      tanar =>
     *      tantargy =>
     * )
     * @return type
     */
    public function getTimetableToPrint()
    {
        $kezdoOraszam = $this->kezdo_oraszam;

//SELECT timetable.ttdate AS datum, timetable.position AS idopont, timetable.lesson_id AS lesson_id, subjects.name AS tantargy, schoolclasses.name AS osztaly, teachers.name AS tanar FROM timetable LEFT JOIN lessons ON timetable.lesson_id = lessons.id LEFT JOIN teachers ON lessons.teacher_id = teachers.id LEFT JOIN subjects ON lessons.subject_id = subjects.id LEFT JOIN schoolclasses ON lessons.class_id = schoolclasses.id WHERE DATE(datum) >= "2018-09-03" AND DATE(datum) <= "2018-09-04";
                
        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.short_name AS tantargy, tt_schoolclasses.short_name AS osztaly, tt_teachers.short_name AS tanar, ' .
                'tt_teachers.short_name AS tanar_rovid ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' . 
                'WHERE tt_timetable.season_id = ' . $this->season_id . ';');
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $result = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $dateid = $d->format('Y.m.d');
            $ora = $kezdoOraszam + $v['idopont'];
            
            if ( !isset($result[$dateid])) $result[$dateid] = array();
            if ( !isset($result[$dateid][$v['osztaly']])) $result[$dateid][$v['osztaly']] = array();

            if ( !isset($result[$dateid][$v['osztaly']][$ora])) $result[$dateid][$v['osztaly']][$ora] = array();
            $result[$dateid][$v['osztaly']][$ora]['tanar'] = $v['tanar']; 
            $result[$dateid][$v['osztaly']][$ora]['tanar_rovid'] = $v['tanar_rovid']; 
            $result[$dateid][$v['osztaly']][$ora]['tantargy'] = $v['tantargy']; 
            
        }
        return $result;
    }
    
    /**
     * A teljes orarend datum szerint csoportositva, egyforma orak osszevonva.
     * tomb felepitese: [datum][osztaly] => array(
     *          'kezdes' => 8
     *          'vege' => 10
     *          'tanar' => tanar_neve
     *          'tanar_rovid' => tanar rovidites
     *          'tantargyid' => tantargy id
     *          'tantargy' => tantargy rovidites
     *          'tantargy_teljes' => teljes nev
     *      ) 
     * @return type
     */
    public function getToPrintGroupByDate()
    {
        $kezdoOraszam = $this->kezdo_oraszam;

//SELECT timetable.ttdate AS datum, timetable.position AS idopont, timetable.lesson_id AS lesson_id, subjects.name AS tantargy, schoolclasses.name AS osztaly, teachers.name AS tanar FROM timetable LEFT JOIN lessons ON timetable.lesson_id = lessons.id LEFT JOIN teachers ON lessons.teacher_id = teachers.id LEFT JOIN subjects ON lessons.subject_id = subjects.id LEFT JOIN schoolclasses ON lessons.class_id = schoolclasses.id WHERE DATE(datum) >= "2018-09-03" AND DATE(datum) <= "2018-09-04";
                
        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.id AS tantargyid, tt_subjects.short_name AS tantargy, tt_subjects.name AS tantargy_teljes, ' .
                'tt_schoolclasses.id AS osztalyid, tt_schoolclasses.short_name AS osztaly, ' . 
                'tt_teachers.name AS tanar, tt_teachers.short_name AS tanar_rovid ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_timetable.season_id = ' . $this->season_id . ';');
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        //  seged felepitese: [datum][osztaly][ora] = array('tanar' => '', 'tantargy' => '', 'lessonid' => '')
        $seged = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $dateid = $d->format('Y.m.d');
            $ora = $kezdoOraszam + $v['idopont'];
            
            if ( !isset($seged[$dateid])) $seged[$dateid] = array();
            if ( !isset($seged[$dateid][$v['osztalyid']])) $seged[$dateid][$v['osztalyid']] = array();

            if ( !isset($seged[$dateid][$v['osztalyid']][$ora])) $seged[$dateid][$v['osztalyid']][$ora] = array();
            $seged[$dateid][$v['osztalyid']][$ora]['osztaly'] = $v['osztaly']; 
            $seged[$dateid][$v['osztalyid']][$ora]['lessonid'] = $v['lesson_id']; 
            $seged[$dateid][$v['osztalyid']][$ora]['tanar'] = $v['tanar']; 
            $seged[$dateid][$v['osztalyid']][$ora]['tanar_rovid'] = $v['tanar_rovid']; 
            $seged[$dateid][$v['osztalyid']][$ora]['tantargyid'] = $v['tantargyid']; 
            $seged[$dateid][$v['osztalyid']][$ora]['tantargy'] = $v['tantargy']; 
            $seged[$dateid][$v['osztalyid']][$ora]['tantargy_teljes'] = $v['tantargy_teljes']; 
        }
        
        $result = array();
        foreach($seged as $dateid => $datumResz)
        {
            if ( !isset($result[$dateid])) $result[$dateid] = array();
            
            foreach($datumResz as $osztalyid => $osztalyResz)
            {
                if ( !isset($result[$dateid][$osztalyid])) $result[$dateid][$osztalyid] = array();
                
                $prevLessonid = -1;
                $prevLessontime = -1;
                $current = null;
                
                foreach($osztalyResz as $ora => $adatok)
                {
                    if ($prevLessonid == -1) {
                        //  uj                        
                        $prevLessonid = $adatok['lessonid'];
                        $current = array(
                            'osztaly' => $adatok['osztaly'],
                            'kezdes' => $ora,
                            'vege' => (int)$ora,
                            'tanar' => $adatok['tanar'],
                            'tanar_rovid' => $adatok['tanar_rovid'],
                            'tantargyid' => $adatok['tantargyid'],
                            'tantargy' => $adatok['tantargy'],
                            'tantargy_teljes' => $adatok['tantargy_teljes']
                        );
                    }
                    else {
                        if ($prevLessonid == $adatok['lessonid'] && $prevLessontime + 1 == $ora) {
                            $current['vege'] += 1;
                        }
                        else {
                            //  elozo mentese
                            $result[$dateid][$osztalyid][] = $current;
                            //  uj
                            $prevLessonid = $adatok['lessonid'];
                            $current = array(
                                'osztaly' => $adatok['osztaly'],
                                'kezdes' => $ora,
                                'vege' => (int)$ora,
                                'tanar' => $adatok['tanar'],
                                'tanar_rovid' => $adatok['tanar_rovid'],
                                'tantargyid' => $adatok['tantargyid'],
                                'tantargy' => $adatok['tantargy'],
                                'tantargy_teljes' => $adatok['tantargy_teljes']
                            );
                        }
                    }
                    
                    $prevLessontime = (int)$ora;
                }
                
                if ($current != NULL) {
                    $result[$dateid][$osztalyid][] = $current;
                }
            }
        }

        return $result;
    }
    
    /**
     * A teljes orarend osztaly szerint csoportositva, egyforma orak osszevonva.
     * tomb felepitese: [osztaly][datum][] => array(
     *          'kezdes' => 8
     *          'vege' => 10
     *          'tanar' => tanar_neve
     *          'tantargy' => tantargy_neve
     *      ) 
     * @return type
     */
    public function getToPrintGroupByClass()
    {
        $kezdoOraszam = $this->kezdo_oraszam;

        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.short_name AS tantargy_rovid, tt_subjects.name AS tantargy, ' .
                'tt_schoolclasses.short_name AS osztaly, ' .
                'tt_teachers.short_name AS tanar_rovid, tt_teachers.name AS tanar ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' . 
                'WHERE tt_timetable.season_id = ' . $this->season_id . ' ' .
                'ORDER BY tt_schoolclasses.short_name, tt_timetable.ttdate, tt_timetable.position;');
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        //  seged felepitese: [osztaly][datum][ora] = array('tanar' => '', 'tantargy' => '', 'lessonid' => '')
        $seged = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $dateid = $d->format('Y.m.d');
            $ora = $kezdoOraszam + $v['idopont'];
            
            if ( !isset($seged[$v['osztaly']])) $seged[$v['osztaly']] = array();
            if ( !isset($seged[$v['osztaly']][$dateid])) $seged[$v['osztaly']][$dateid] = array();            

            if ( !isset($seged[$v['osztaly']][$dateid][$ora])) $seged[$v['osztaly']][$dateid][$ora] = array();
            $seged[$v['osztaly']][$dateid][$ora]['lessonid'] = $v['lesson_id']; 
            $seged[$v['osztaly']][$dateid][$ora]['tanar'] = $v['tanar']; 
            $seged[$v['osztaly']][$dateid][$ora]['tantargy'] = $v['tantargy']; 
            $seged[$v['osztaly']][$dateid][$ora]['tantargy_rovid'] = $v['tantargy_rovid']; 
        }
        
        $result = array();
        foreach($seged as $osztalyid => $osztalyResz)
        {
            if ( !isset($result[$osztalyid])) $result[$osztalyid] = array();
            
            foreach($osztalyResz as $dateid => $datumResz)
            {
                if ( !isset($result[$osztalyid][$dateid])) $result[$osztalyid][$dateid] = array();
                
                $prevLessonid = -1;
                $prevLessontime = -1;
                $current = null;
                
                foreach($datumResz as $ora => $adatok)
                {
                    if ($prevLessonid == -1) {
                        //  uj                        
                        $prevLessonid = $adatok['lessonid'];
                        $current = array(
                            'kezdes' => $ora,
                            'vege' => (int)$ora,
                            'tanar' => $adatok['tanar'],
                            'tantargy' => $adatok['tantargy'],
                            'tantargy_rovid' => $adatok['tantargy_rovid']
                        );
                    }
                    else {
                        if ($prevLessonid == $adatok['lessonid'] && $prevLessontime + 1 == $ora) {
                            $current['vege'] += 1;
                        }
                        else {
                            //  elozo mentese
                            $result[$osztalyid][$dateid][] = $current;
                            //  uj
                            $prevLessonid = $adatok['lessonid'];
                            $current = array(
                                'kezdes' => $ora,
                                'vege' => (int)$ora,
                                'tanar' => $adatok['tanar'],
                                'tantargy' => $adatok['tantargy'],
                                'tantargy_rovid' => $adatok['tantargy_rovid']
                            );
                        }
                    }
                    
                    $prevLessontime = (int)$ora;
                }
                
                if ($current != NULL) {
                    $result[$osztalyid][$dateid][] = $current;
                }
            }
        }

        return $result;
    }
    
    public function getToPrintGroupByTeacher2()
    {
        $kezdoOraszam = $this->kezdo_oraszam;

        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.short_name AS tantargy_rovid, tt_subjects.name AS tantargy, ' .
                'tt_schoolclasses.short_name AS osztaly, ' .
                'tt_teachers.name AS tanar, tt_teachers.id AS tanar_id ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_timetable.season_id = ' . $this->season_id . ' ' .
                'ORDER BY tt_teachers.id, tt_timetable.ttdate, tt_timetable.lesson_id, tt_timetable.position;');
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
//     tomb felepitese: array(
//              'tanar' => array(
//                  'datum' => array(
//                  
//                  )
//              ),
//              [datum][] => array(
//                  'kezdes' => 8
//                  'vege' => 10
//                  'osztaly' => osztaly_nev
//              'tantargy' => tantargy_neve
        
        $result = array();
        $tanar = array(
            'id' => -1,
            'lessons' => array()
        );
        $lesson = array(
            'id' => -1
        );
        foreach($temp as $row)
        {
            if ($tanar['id'] != $row['tanar_id'])
            {
                $tanar = array();
                $tanar['id'] = $row['tanar_id'];
                $tanar['lessons'] = array();
            }
            
            if ($lesson['datum'] == $row['datum'])
            {
                
            }
            else
            {
                
            }
        }
        
        
        
        //  seged felepitese: [tanarid][datum][ora] = array('tanar' => '', 'osztaly' => '', 'tantargy' => '', 'lessonid' => '')
        $seged = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $dateid = $d->format('Y.m.d');
            $ora = $kezdoOraszam + $v['idopont'];
            
            if ( !isset($seged[$v['tanar_id']]))
                $seged[$v['tanar_id']] = array(
                    'tanar' => $v['tanar']
                );
            if ( !isset($seged[$v['tanar_id']][$dateid])) $seged[$v['tanar_id']][$dateid] = array();            

            if ( !isset($seged[$v['tanar_id']][$dateid][$ora])) $seged[$v['tanar_id']][$dateid][$ora] = array();
            $seged[$v['tanar_id']][$dateid][$ora]['lessonid'] = $v['lesson_id']; 
            $seged[$v['tanar_id']][$dateid][$ora]['osztaly'] = $v['osztaly']; 
            $seged[$v['tanar_id']][$dateid][$ora]['tantargy'] = $v['tantargy']; 
            $seged[$v['tanar_id']][$dateid][$ora]['tantargy_rovid'] = $v['tantargy_rovid']; 
        }
        
        $result = array();
        foreach($seged as $tanarid => $tanarResz)
        {
            if ( !isset($result[$tanarid])) $result[$tanarid] = array('tanar' => $tanarResz['tanar']);
            
            foreach($tanarResz as $dateid => $datumResz)
            {
                if ($dateid == 'tanar') continue;
                
                if ( !isset($result[$tanarid][$dateid])) $result[$tanarid][$dateid] = array();
                
                $prevLessonid = -1;
                $prevLessontime = -1;
                $current = null;
                
                foreach($datumResz as $ora => $adatok)
                {
                    if ($prevLessonid == -1) {
                        //  uj                        
                        $prevLessonid = $adatok['lessonid'];
                        $current = array(
                            'kezdes' => $ora,
                            'vege' => (int)$ora,
                            'osztaly' => $adatok['osztaly'],
                            'tantargy' => $adatok['tantargy'],
                            'tantargy_rovid' => $adatok['tantargy_rovid']
                        );
                    }
                    else {
                        if ($prevLessonid == $adatok['lessonid'] && $prevLessontime + 1 == $ora) {
                            $current['vege'] += 1;
                        }
                        else {
                            //  elozo mentese
                            $result[$tanarid][$dateid][] = $current;
                            //  uj
                            $prevLessonid = $adatok['lessonid'];
                            $current = array(
                                'kezdes' => $ora,
                                'vege' => (int)$ora,
                                'osztaly' => $adatok['osztaly'],
                                'tantargy' => $adatok['tantargy'],
                                'tantargy_rovid' => $adatok['tantargy_rovid']
                            );
                        }
                    }
                    
                    $prevLessontime = (int)$ora;
                }
                
                if ($current != NULL) {
                    $result[$tanarid][$dateid][] = $current;
                }
            }
        }

        return $result;
        
    }
    
    /**
     * A teljes orarend tanar szerint csoportositva, egyforma orak osszevonva.
     * tomb felepitese: [tanarid] => array(
     *          'tanar' => tanar_nev,
     *          [datum][] => array(
     *              'kezdes' => 8
     *              'vege' => 10
     *              'osztaly' => osztaly_nev
     *              'tantargy' => tantargy_neve
     * @return type
     */
    public function getToPrintGroupByTeacher()
    {
        $kezdoOraszam = $this->kezdo_oraszam;

//SELECT timetable.ttdate AS datum, timetable.position AS idopont, timetable.lesson_id AS lesson_id, subjects.name AS tantargy, schoolclasses.name AS osztaly, teachers.name AS tanar FROM timetable LEFT JOIN lessons ON timetable.lesson_id = lessons.id LEFT JOIN teachers ON lessons.teacher_id = teachers.id LEFT JOIN subjects ON lessons.subject_id = subjects.id LEFT JOIN schoolclasses ON lessons.class_id = schoolclasses.id WHERE DATE(datum) >= "2018-09-03" AND DATE(datum) <= "2018-09-04";
                
        $stmt = $this->db->prepare(
                'SELECT tt_timetable.ttdate AS datum, tt_timetable.position AS idopont, tt_timetable.lesson_id AS lesson_id, ' .
                'tt_subjects.short_name AS tantargy_rovid, tt_subjects.name AS tantargy, tt_schoolclasses.short_name AS osztaly, tt_teachers.name AS tanar, tt_teachers.id AS tanar_id ' .
                'FROM tt_timetable ' .
                'LEFT JOIN tt_lessons ON tt_timetable.lesson_id = tt_lessons.id ' .
                'LEFT JOIN tt_teachers ON tt_lessons.teacher_id = tt_teachers.id ' .
                'LEFT JOIN tt_subjects ON tt_lessons.subject_id = tt_subjects.id ' .
                'LEFT JOIN tt_schoolclasses ON tt_lessons.class_id = tt_schoolclasses.id ' .
                'WHERE tt_timetable.season_id = ' . $this->season_id . ' ' .
                'ORDER BY tt_teachers.name, tt_timetable.ttdate, tt_timetable.position, tt_timetable.lesson_id;');
        $stmt->execute();
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        //  seged felepitese: [tanarid][datum][ora] = array('tanar' => '', 'osztaly' => '', 'tantargy' => '', 'lessonid' => '')
        $seged = array();
        foreach($temp as $v) {
            $d = new \DateTime($v['datum']);
            
            $dateid = $d->format('Y.m.d');
            $ora = $kezdoOraszam + $v['idopont'];
            
            if ( !isset($seged[$v['tanar_id']]))
                $seged[$v['tanar_id']] = array(
                    'tanar' => $v['tanar']
                );
            if ( !isset($seged[$v['tanar_id']][$dateid])) $seged[$v['tanar_id']][$dateid] = array();            

            if ( !isset($seged[$v['tanar_id']][$dateid][$ora])) $seged[$v['tanar_id']][$dateid][$ora] = array();
            $seged[$v['tanar_id']][$dateid][$ora]['lessonid'] = $v['lesson_id']; 
            $seged[$v['tanar_id']][$dateid][$ora]['osztaly'] = $v['osztaly']; 
            $seged[$v['tanar_id']][$dateid][$ora]['tantargy'] = $v['tantargy']; 
            $seged[$v['tanar_id']][$dateid][$ora]['tantargy_rovid'] = $v['tantargy_rovid']; 
        }
        
        $result = array();
        foreach($seged as $tanarid => $tanarResz)
        {
            if ( !isset($result[$tanarid])) $result[$tanarid] = array('tanar' => $tanarResz['tanar']);
            
            foreach($tanarResz as $dateid => $datumResz)
            {
                if ($dateid == 'tanar') continue;
                
                if ( !isset($result[$tanarid][$dateid])) $result[$tanarid][$dateid] = array();
                
                $prevLessonid = -1;
                $prevLessontime = -1;
                $current = null;
                
                foreach($datumResz as $ora => $adatok)
                {
                    if ($prevLessonid == -1) {
                        //  uj                        
                        $prevLessonid = $adatok['lessonid'];
                        $current = array(
                            'kezdes' => $ora,
                            'vege' => (int)$ora,
                            'osztaly' => $adatok['osztaly'],
                            'tantargy' => $adatok['tantargy'],
                            'tantargy_rovid' => $adatok['tantargy_rovid']
                        );
                    }
                    else {
                        if ($prevLessonid == $adatok['lessonid'] && $prevLessontime + 1 == $ora) {
                            $current['vege'] += 1;
                        }
                        else {
                            //  elozo mentese
                            $result[$tanarid][$dateid][] = $current;
                            //  uj
                            $prevLessonid = $adatok['lessonid'];
                            $current = array(
                                'kezdes' => $ora,
                                'vege' => (int)$ora,
                                'osztaly' => $adatok['osztaly'],
                                'tantargy' => $adatok['tantargy'],
                                'tantargy_rovid' => $adatok['tantargy_rovid']
                            );
                        }
                    }
                    
                    $prevLessontime = (int)$ora;
                }
                
                if ($current != NULL) {
                    $result[$tanarid][$dateid][] = $current;
                }
            }
        }

        return $result;
    }
    
    public function cloneSeason($oldid, $newid, $lessonLookup)
    {
        $this->season_id = $oldid;
        $stmt = $this->db->prepare('SELECT season_id, ttdate, position, lesson_id FROM tt_timetable WHERE season_id = ' . $oldid . ';');
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sql = 'INSERT INTO tt_timetable(season_id, ttdate, position, lesson_id) VALUES ';
        $elsoAdat = true;
        foreach($rows as $row) {
            $row['lesson_id'] = $lessonLookup[ $row['lesson_id'] ];

            if ($elsoAdat) { $elsoAdat = false; }
            else { $sql .= ', '; }
            $sql .= '(' . $newid . ', "' . $row['ttdate'] . '", ' . $row['position'] . ', ' . $row['lesson_id'] . ')';
        }
        $this->db->exec($sql);
        
    }

    /**
     * Elmenti a napokat, amikor nincs tanitas.
     * @param array $list
     */
    public function saveExceptionDates($list)
    {
        $this->nincs_tanitas = array();
        foreach($list as $d) $this->nincs_tanitas[] = $d;
        $this->saveSettings();
    }
    
    /**
    * A paramter tomb felepitese: datum, pozicio, lesson_id
    */
    public function saveTimetable($data)
    {
        $stat = array(
            'msg' => '',
            'total' => count($data),
            'saved' => 0,
            'errors' => array()
        );
        
        if (empty($data)) 
        {
//            file_put_contents('mentes_proba.txt', 'ures tomb');
            return $stat;
        }
        
        $sql = 'INSERT INTO tt_timetable(season_id, ttdate, position, lesson_id) VALUES ';
        
        $elsoAdat = true;
        $start = \DateTime::createFromFormat('Y.m.d', $this->elso_tanitasi_nap);
        $end = \DateTime::createFromFormat('Y.m.d', $this->utolso_tanitasi_nap);
        
        foreach($data as $row)
        {
            if ( !$this->validateDate($row[0], 'Y.m.d'))
            {
                $stat['errors'][] = 'Ervenytelen datum: ' . $row[0];
                continue;
            }
            $d = \DateTime::createFromFormat('Y.m.d', $row[0]);
            if ($d < $start || $d > $end)
            {
                $stat['errors'][] = 'Hibas intervallum: ' . $d;
                continue;
            }
            
            $pos = (int)$row[1];
            if ($pos < 0 || $pos >= $this->napi_oraszam)
            {
                $stat['errors'][] = 'Hibas ora pozicio: ' . $row[1];
                continue;
            }
            
            $lessonid = (int)$row[2];
            if ($lessonid < 1)
            {
                $stat['errors'][] = 'Hibas tanora: ' . $row[2];
                continue;
            }

            if ($elsoAdat) { $elsoAdat = false; }
            else { $sql .= ', '; }
            
            $sql .= '(' . $this->season_id . ', "' . $row[0] . '", ' . $pos . ', ' . $lessonid . ')';
        }
        
//        file_put_contents('mentes_proba.txt', $sql);
        
        $this->clearTimetable();
        $stat['saved'] = $this->db->exec($sql);
        
        return $stat;
    }
    
    public function clearTimetable()
    {
        //$stmt = $this->db->exec('TRUNCATE TABLE tt_timetable');
        $stmt = $this->db->exec('DELETE FROM tt_timetable WHERE season_id = ' . $this->season_id . ';');
    }

    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    public function validateNewTTData($data)
    {
        $rules = array(
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
            return array('firstday' => array('Elso tanitasi nap: ervenytelen datum'));
        }
        
        if (!$dateValidator->validateDate($data['lastday'], 'Y.m.d'))
        {
            return array('lastday' => array('Utolso tanitasi nap: ervenytelen datum'));
        }
        
        $firstdate = \DateTime::createFromFormat('Y.m.d', $data['firstday']);
        $lastdate = \DateTime::createFromFormat('Y.m.d', $data['lastday']);

        if ($firstdate == $lastdate)
        {
            return array('dates' => array('A ket datum nem lehet egyforma!'));
        }
        
        if ($firstdate > $lastdate)
        {
            return array('dates' => array('Az elso tanitasi napnak korabbinak kell lennie, mint az utolso tanitasi nap!'));
        }
        
        return array();
    }
}