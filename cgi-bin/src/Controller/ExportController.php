<?php
namespace App\Controller;

class ExportController extends Controller
{
    
    public function index($req, $resp)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('export/index.php', array(
                'kretaFejlec' => $ttm->kretaHeader,
                'napok' => $ttm->getSemesterDates()        
            )),
            '_pagetitle' => 'Export',
        ));
    }

    public function toKreta($req, $resp)
    {
        $data = $req->getParsedBody();
        
        $datum = isset($data['date']) ? $data['date'] : null;
        $period = isset($data['period']) ? $data['period'] : null;
        $withValidColumn = isset($data['valid-column']);        
        
        if ($datum == null || $period == NULL) return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Hianyzo parameterek!');
        
        $periodId = (int)$period;
        
        $dateValidator = new \App\DateValidator();
        if ( !$dateValidator->validateDate($datum, 'Y.m.d'))
        {
            $this->container->flash->addMessage('system_message', 'Hibás dátum forma: ' . $datum);    
            return $resp->withRedirect('/export/index', 301);
        }

        if ($periodId < 1 || $periodId > 3) {
            $this->container->flash->addMessage('system_message', 'Hibás periódus: ' . $periodId);    
            return $resp->withRedirect('/export/index', 301);

        }
        
    //        $datum = '2018.09.03';
    //        $periodId = 1;

        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        if ($periodId == 1) {
            //  egesz het
            //  megkeresni a datum hetenek elso napjat
            $d = \DateTime::createFromFormat('Y.m.d', $datum);
            $napszam = (int)$d->format('N');
            if ($napszam != 1) {
                $offset = $napszam - 1;
                $d->sub(new \DateInterval('P' . $offset . 'D'));
                $datum = $d->format('Y.m.d');
            }
            $startDate = \DateTime::createFromFormat('Y.m.d', $datum);
            $startDate = $startDate->format('Y-m-d');
            $endDate = \DateTime::createFromFormat('Y.m.d', $datum);
            $endDate->add(new \DateInterval('P5D'));
            $endDate = $endDate->format('Y-m-d');
        }
        else if ($periodId == 2) {
            //  egy nap
            $startDate = \DateTime::createFromFormat('Y.m.d', $datum);
            $startDate = $startDate->format('Y-m-d');
            $endDate = $startDate;
        }
		else {
            //  teljes felev
            $datum = new \DateTime();
            $datum = $datum->format('Y.m.d');
			$startDate = $ttm->elso_tanitasi_nap;
			$endDate = $ttm->utolso_tanitasi_nap;
		}
		

    //        $orarend = $this->ttm->getTimetableForKreta($startDate, $endDate);
            
    //        echo '<pre>';
    //        print_r($orarend);

        $filename = 'kreta esti orarend ' . $datum;
        if ($periodId == 1) $filename .= ' heti';
		if ($periodId == 3) $filename .= ' teljes';
        $filename .= '.xlsx';
        
        if ($periodId == 3) {
            if ($withValidColumn) $xls = $ttm->getExcelForKretaFullWithValidColumn($startDate, $endDate);
            else $xls = $ttm->getExcelForKretaFull($startDate, $endDate);
        }
        else {
            $xls = $ttm->getExcelForKreta($startDate, $endDate);
        }
        /*
        echo '<pre>';
        print_r($xls);
        */
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($xls, 'Xlsx');
        
        $response = $resp->withStatus(200)
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $filename . '"')
            ->withHeader('Cache-Control', 'max-age=0');
        
        return $response->write( $writer->save('php://output') );
    }

    public function toPrint($req, $resp, $args)
    {
        $type = (int)isset($args['type']) ? $args['type'] : 1;
        
        $exportManager = new \App\ExportManager();
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);

        $fileType = "excel";
        $filename = '';
        
        switch($type)
        {
            case 1:
                //  teljes pdf
                $fileType = "pdf";
                $orarend = $ttm->getToPrintGroupByDate();
                $result = $exportManager->createFullPdf($orarend);
                $filename = "esti orarend - " . date("Y.m.d") . ".pdf";
                break;
            case 2:
                //  teljes excel
                $orarend = $ttm->getToPrintGroupByDate();
                $result = $exportManager->createFullExcel($orarend);
                $filename = "esti orarend - " . date("Y.m.d") . ".xlsx";
                break;
            case 3:
                //  tanari
                $orarend = $ttm->getToPrintGroupByTeacher();
//                $result = $exportManager->createTeacherPdf($orarend);
//                $filename = "esti tanari orarend - " . date("Y.m.d") . ".pdf";                
                $result = $exportManager->createTeacherExcel($orarend);
                $filename = "esti tanari orarend - " . date("Y.m.d") . ".xlsx";
                break;
            case 4:
                //  osztalyonkenti
//                $orarend = $ttm->getToPrintGroupByClass();
//                $result = $exportManager->createClassPdf($orarend);
//                $filename = "esti osztaly orarend - " . date("Y.m.d") . ".pdf";
                $orarend = $ttm->getToPrintGroupByClass();
                $result = $exportManager->createClassExcel($orarend);
                $filename = "esti osztaly orarend - " . date("Y.m.d") . ".xlsx";
                break;
            case 5:
                //  teljes excel, tablazatos
                $mClass = new \App\Schoolclass($this->db);
                $orarend = $ttm->getToPrintGroupByDate();
                $result = $exportManager->createClassicFullExcel2($orarend, $mClass->getAll('ORDER BY short_name'), $ttm->elso_tanitasi_nap, $ttm->utolso_tanitasi_nap, $ttm->nincs_tanitas);
                $filename = "esti orarend - " . date("Y.m.d") . ".xlsx";
                break;
            case 6:
                // Felsorolva minden óra, minden adatával együtt (excel)
                $orarend = $ttm->getToPrintGroupByClass();
                $result = $exportManager->createFullList($orarend);
                $filename = "esti orarend lista - " . date("Y.m.d") . ".xlsx";
                break;
                
            case 11:
                //  tanorak, tanar szerint csoportositva
                $result = $exportManager->createLessonsGroupByTeacherExcel($ttm->getLessonsForHumans());
                $filename = "tanorak, tanaronkent - " . date("Y.m.d") . ".xlsx";
                break;

            case 12:
                //  tanorak, osztaly szerint csoportositva
                $result = $exportManager->createLessonsGroupByClassExcel($ttm->getLessonsForHumans());
                $filename = "tanorak, osztalyonkent - " . date("Y.m.d") . ".xlsx";
                break;

            case 21:
                //  tanarok
                $mTeacher = new \App\Teacher($this->db);
                $result = $exportManager->createTeachersList($mTeacher->getAllWithLessonsnum());
                $filename = "tanarok - " . date("Y.m.d") . ".xlsx";
                break;
        }
        
        if (empty($filename))
        {
            $this->container->flash->addMessage('system_message', 'Ismeretlen export!');    
            return $resp->withRedirect('/export/index', 301);
        }
        
//            echo '<pre>';
//            print_r($orarend);
        if ($fileType == "excel")
//        if ($type == 2 || $type == 5)
        {
            $response = $resp->withStatus(200)
                ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->withHeader('Content-Disposition', 'attachment;filename="' . $filename . '"')
                ->withHeader('Cache-Control', 'max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($result, 'Xlsx');
            return $response->write( $writer->save('php://output') );
        }
        else
        {
            $response = $resp->withHeader('Content-type', 'application/pdf' );
            return $response->write( $result->Output($filename, 'I') );
        }
        
    }
    
    public function webview($req, $resp)
    {
        $mClass = new \App\Schoolclass($this->db);
        $mTeacher = new \App\Teacher($this->db);
        $mLesson = new \App\Lesson($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);        
        $dateHelper = new \App\DateHelper();
        
        $napok = $ttm->getSemesterDates();
        $hetek = array();
        $hetid = 1;
        $napid = 1;
        foreach($napok as $nap)
        {
            if ( !isset($hetek[$hetid])) $hetek[$hetid] = array();
            $hetek[$hetid][] = array(
                'van_tanitas' => $nap['van_tanitas'],
                'datum' => $nap['datum'],
                'napnev' => $dateHelper->getDayName($nap['datum'])
            );
            
            if ($napid % 5 == 0)
            {
                $hetid += 1;
                $napid = 0;
            }
            
            $napid += 1;
        }
        
        return $this->render($resp, 'export/webview.php', array(
            '_pagetitle' => 'Export',
            'elsoNap' => $ttm->elso_tanitasi_nap,
            'utolsoNap' => $ttm->utolso_tanitasi_nap,
            'napi_oraszam' => $ttm->napi_oraszam,
            'kezdo_oraszam' => $ttm->kezdo_oraszam,
            'datumok' => $hetek,
            'osztalyok' => $mClass->getAll(),
            'tanarok' => $mTeacher->getAll(),
            'tanorak' => $mLesson->getAll(),
            'orarend' => $ttm->getTimetableToPrint()
        ));
        
    }
    
}