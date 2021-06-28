<?php
namespace App;

/**
 * A mertekegysegek millimeterben vannak!
 * 
 */

class MyTCPDF extends \TCPDF
{
    protected $headerTitle = '';
    protected $headerSize = 12;
    protected $headerStyle = '';


    public function setHeaderTitle($title, $size = 12, $style = '')
    {
        $this->headerTitle = $title;
        $this->headerSize = $size;
        $this->headerStyle = $style;
    }

    //Page header
    public function Header()
    {
        $this->SetFont('helvetica', $this->headerStyle, $this->headerSize);
        // Title
        $this->Cell(0, 15, $this->headerTitle, 0, false, 'C', 0, '', 0, false, 'T', 'C');
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
//        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(90, 10, date("Y.m.d"), 0, 0, 'L', false, '', 0, false);
        $this->Cell(0, 10, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, 0, 'R', false, '', 0, false);
    }
}

class ExportManager
{
    /*
     * Teljes orarend, pdf-ben!
     * A TimetableManager->getToPrintGroupByDate() adatait dolgozza fel.
     */
    public function createFullPdf($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $pdf = new MyTCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Csernai Zsolt');
        $pdf->SetTitle('Esti órarend');

        $pdf->SetHeaderData('', 0, 'Teljes', '', array(0, 0, 0), array(0, 0, 0));
        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
//        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 20, 10);
//        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
//        $pdf->SetFont('times', '', 10, '', true);
        $pdf->SetFont('freeserif', '', 12);

        $pdf->SetLineWidth(0.08);

        $pdf->setHeaderTitle('Felnött órarend', 16);
        
        $simavonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $vastagvonal = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $szagatottvonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(0, 0, 0));
        
        $border = array('B' => $simavonal);
        
        $height = 6;
        $dateHeight = 12;
        
        $datumSzelesseg = 50;
        $osztalySzelesseg = 20;
        $oraszamSzelesseg = 25;
        $tanarSzelesseg = 90;
        $tantargySzelesseg = 0; //50;

        $pdf->AddPage();
        
        foreach($orarend as $dateid => $datumResz)
        {
            $pdf->SetFont('freeserif', 'B', 12);
            $nap = $dateHelper->getDayName($dateid, 'Y.m.d');
            $pdf->Cell($datumSzelesseg, $dateHeight, $dateid . ' - ' . $nap, 0, 0, 'L', false, '', 0, false, 'T', 'B');
            $pdf->Ln();
            
            $pdf->SetFont('freeserif', '', 11);
            
//            $pdf->Cell($osztalySzelesseg, $height, 'Osztály', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($oraszamSzelesseg, $height, 'Mikor', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($tanarSzelesseg, $height, 'Tanár', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($tantargySzelesseg, $height, 'Tantárgy', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Ln();
            
            foreach($datumResz as $osztalyid => $osztalyResz)
            {
                $elso = true;
                
                foreach($osztalyResz as $adatok)
                {
                    $pdf->SetFont('freeserif', 'B', 11);                    
                    if ($elso) {
                        $elso = false;
                        $pdf->Cell($osztalySzelesseg, $height, $adatok['osztaly'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    }
                    else {
                        $pdf->Cell($osztalySzelesseg, $height, ' ', $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    }

                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor = '' . $adatok['kezdes'];
                    else $mikor = '' . $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $pdf->SetFont('freeserif', '', 11);
                                
                    $pdf->Cell($oraszamSzelesseg, $height, $mikor, $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    $pdf->Cell($tanarSzelesseg, $height, $adatok['tanar'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)                                                
//                    $pdf->MultiCell($tanarSzelesseg, $height, $adatok['tanar'], $border, 'L', 0, 0, '', '', true, false, true, 0, 'B');
                    $pdf->Cell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
//                    $pdf->MultiCell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 'L', 0, 0, '', '', false, 0, false, true, 0);
                    
                    $pdf->Ln();                    
                }
            }
        }
        
        
        return $pdf;
    }
    
    /*
     * Teljes orarend, excel-ben!
     * A TimetableManager->getToPrintGroupByDate() adatait dolgozza fel.
     */
    public function createFullExcel($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        //  nyomtatasi fejlec, lablec
        $as->getHeaderFooter()->setOddHeader('Felnőtt órarend');
        $as->getHeaderFooter()->setOddFooter('&L&D &R &P / &N');
        
        $row = 1;
        foreach($orarend as $dateid => $datumResz)
        {
            $nap = $dateHelper->getDayName($dateid, 'Y.m.d');
            $as->setCellValueByColumnAndRow(1, $row, $dateid . ' - ' . $nap);
            $cellCoord = 'A' . $row;
            $as->getStyle($cellCoord)->getFont()->setBold('bold');
            
            $row += 1;
            
            //  fejlec, kell??
//            $pdf->Cell($osztalySzelesseg, $height, 'Osztály', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($oraszamSzelesseg, $height, 'Mikor', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($tanarSzelesseg, $height, 'Tanár', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Cell($tantargySzelesseg, $height, 'Tantárgy', $border, 0, 'L', false, '', 0, false, 'T', 'B');
//            $pdf->Ln();
            
            foreach($datumResz as $osztalyid => $osztalyResz)
            {
                $elso = true;
                
                foreach($osztalyResz as $adatok)
                {
                    if ($elso) {
                        $elso = false;
                        $as->setCellValueByColumnAndRow(1, $row, $adatok['osztaly']);
                    }

                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor = ' ' . $adatok['kezdes'];
                    else $mikor = '' . $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $as->setCellValueByColumnAndRow(2, $row, $mikor);
                    $as->setCellValueByColumnAndRow(3, $row, $adatok['tanar']);
                    $as->setCellValueByColumnAndRow(4, $row, $adatok['tantargy_teljes']);                    
                    
                    $row += 1;
                }
            }
            
            $row += 1;
        }
        
        $as->getColumnDimension('C')->setAutoSize(true);
        $as->getColumnDimension('D')->setAutoSize(true);
        
        return $spreadsheet;
    }
    
    /*
     * Teljes orarend, excel-ben! A regi orarendi nezethez hasonlo:
     *                   nap  |  nap
     *      hetfo 13.A
     *            13.B
     *      kedd  13.D ...
     * Csak azok a napok jelennek meg, ahol van tanitas az osztalynak!
     * A TimetableManager->getToPrintGroupByDate() adatait dolgozza fel.
     */
    public function createClassicFullExcel($orarend, $osztalyok, $elsoNap, $utolsoNap)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        //  nyomtatasi fejlec, lablec
        $as->getHeaderFooter()->setOddHeader('Felnőtt órarend');
        $as->getHeaderFooter()->setOddFooter('&L&D &R &P / &N');
        
        
        
        $napnevek = array('Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek');
        
        /*
         * tomb felepites:
         *      napid => array(
         *          datumsor => ebben a sorban van a naphoz tartozo datum
         *          osztalyok => array(
         *              osztalyid => hanyadik sor tartozik hozza
         *              ...
         *          )
         *      )
         */
        $felepitesVaz = array(
            1 => array(
                'datumsor' => 1,
                'osztalyok' => array()
            ),
            2 => array(
                'datumsor' => 1,
                'osztalyok' => array()
            ),
            3 => array(
                'datumsor' => 1,
                'osztalyok' => array()
            ),
            4 => array(
                'datumsor' => 1,
                'osztalyok' => array()
            ),
            5 => array(
                'datumsor' => 1,
                'osztalyok' => array()
            ),
        );
        
        $sor = 1;
        for($i = 0; $i < 5; $i++)
        {
            $napid = $i + 1;
            
            $felepitesVaz[$napid]['datumsor'] = $sor;
            $sor += 1;
            
            $napnev = $napnevek[$i];
            
            $as->setCellValueByColumnAndRow(1, $sor, $napnev);

            foreach($osztalyok as $osztaly)
            {
                if ($osztaly['day' . $napid] == 1)
                {
                    $felepitesVaz[$napid]['osztalyok'][$osztaly['id']] = $sor;
                    $as->setCellValueByColumnAndRow(2, $sor, $osztaly['short_name']);
                    $sor += 1;
                }
            }
        }
        
        
        $alapHossz = 3;
        $hetKezdoOszlopa = 2;
        $hetMaxSzelessege = 1;
        
        $currentDate = \DateTime::createFromFormat('Y.m.d', $elsoNap);
        $endDate = \DateTime::createFromFormat('Y.m.d', $utolsoNap);
        while($currentDate <= $endDate)
        {
            $dateid = $currentDate->format('Y.m.d');
            $napid = (int)$currentDate->format('N');
            
            if ($napid == 1)
            {
                //  uj het
                if ($hetMaxSzelessege < 1) $hetMaxSzelessege = 1;
                $hetKezdoOszlopa += $hetMaxSzelessege;
                $hetMaxSzelessege = 0;
            }
            
            if ($napid > 5)
            {
                $currentDate->add(new \DateInterval('P1D'));
                continue;
            }
            
            $as->setCellValueByColumnAndRow($hetKezdoOszlopa, $felepitesVaz[$napid]['datumsor'], $dateid);

            if ( !isset($orarend[$dateid]))
            {
                $currentDate->add(new \DateInterval('P1D'));
                continue;
            }
            
            foreach($felepitesVaz[$napid]['osztalyok'] as $osztalyid => $sor)
            {
                if ( !isset($orarend[$dateid][$osztalyid])) continue;
                
                $oszlop = $hetKezdoOszlopa;
                $szelesseg = 0;
                
                foreach($orarend[$dateid][$osztalyid] as $adatok)
                {
                    $s = $adatok['tanar_rovid'];
                    
                    $hossz = (int)$adatok['vege'] - (int)$adatok['kezdes'] + 1;                    
                    if ($hossz != $alapHossz) $s .= " (" . $hossz . ")";
                    
                    $as->setCellValueByColumnAndRow($oszlop, $sor, $s);
                    
                    $oszlop += 1;
                    $szelesseg += 1;
                }
                
                if ($szelesseg > $hetMaxSzelessege) $hetMaxSzelessege = $szelesseg;
            }

            $currentDate->add(new \DateInterval('P1D'));
        }
        
        return $spreadsheet;
    }
    
    /*
     * Teljes orarend, excel-ben! A regi orarendi nezethez hasonlo, de egy het vizszintesen van:
     *                   hetfo  |  kedd ...
     *      1. het 13.A
     *             13.B
     *      2. het 13.D ...
     * A TimetableManager->getToPrintGroupByDate() adatait dolgozza fel.
     */
    public function createClassicFullExcel2($orarend, $osztalyok, $elsoNap, $utolsoNap, $nincsTanitas)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        //  nyomtatasi fejlec, lablec
        $as->getHeaderFooter()->setOddHeader('Esti órarend');
        //  ez a verzio mindig az aktualis datumot mutatja
        //$as->getHeaderFooter()->setOddFooter('&L&D &R &P / &N');
        //  ez a verzio az exportalas datumat mutatja
        $as->getHeaderFooter()->setOddFooter('&L' . date("Y.m.d") . ' &R &P / &N');
        
        $napnevek = array('Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek');
        
        $alapHossz = 3;
        $napMaxSzelessege = 1;
        $napKezdoOszlopa = 2;
        $datumSor = 1;
        $oszlop = 1;
        $het = 1;
        $tableWidth = 1;
        
        $currentDate = \DateTime::createFromFormat('Y.m.d', $elsoNap);
        $endDate = \DateTime::createFromFormat('Y.m.d', $utolsoNap);
        while($currentDate <= $endDate)
        {
            $dateid = $currentDate->format('Y.m.d');
            $datumExcelbe = $dateHelper->getMonthName($currentDate) . '. ' . $currentDate->format('d');
            $napid = (int)$currentDate->format('N');
            
            $napMaxSzelessege = 2;
            
            if ($napid == 1)
            {
                $as->setCellValueByColumnAndRow(1, $datumSor, $het . ". hét");                
            }
            
            if ($napid == 7)
            {
                //  uj het
                $napKezdoOszlopa = 2;
                $datumSor += count($osztalyok) + 1;
                $het += 1;
            }
            
            if ($napid > 5)
            {
                $currentDate->add(new \DateInterval('P1D'));
                continue;
            }
            
            $as->setCellValueByColumnAndRow($napKezdoOszlopa, $datumSor, $datumExcelbe);
            
            $sor = $datumSor + 1;
            foreach($osztalyok as $osztaly)
            {
                if ($napid == 1) $as->setCellValueByColumnAndRow(1, $sor, $osztaly['short_name']);
                
                $oszlop = $napKezdoOszlopa;
                $szelesseg = 0;
                
                if (in_array($dateid, $nincsTanitas))
                {
//                    $as->setCellValueByColumnAndRow($oszlop, $sor, "--");
                    $as->setCellValueByColumnAndRow($oszlop, $sor, "X");
                    $oszlop += 1;
                    $szelesseg += 1;
                    $as->setCellValueByColumnAndRow($oszlop, $sor, "X");
                }
                
                if (isset($orarend[$dateid][$osztaly['id']]))
                {
                    foreach($orarend[$dateid][$osztaly['id']] as $adatok)
                    {
                        $s = $adatok['tanar_rovid'];

                        $hossz = (int)$adatok['vege'] - (int)$adatok['kezdes'] + 1;                    
                        //if ($hossz != $alapHossz) $s .= " x" . $hossz;
                        $s .= " x" . $hossz;

                        $as->setCellValueByColumnAndRow($oszlop, $sor, $s);
                        //  kozepre igazit
                        $as->getStyleByColumnAndRow($oszlop, $sor)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $oszlop += 1;
                        $szelesseg += 1;
                    }
                }
                
                if ($szelesseg > $napMaxSzelessege) $napMaxSzelessege = $szelesseg;
                $sor += 1;
            }

            //  datumok cellainak egyesitese
            $as->mergeCellsByColumnAndRow($napKezdoOszlopa, $datumSor, ($napKezdoOszlopa + $napMaxSzelessege - 1), $datumSor);
            
            //  a nap bal szelere border
            $styleArray = [
                'borders' => [
                    'left' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $as->getStyleByColumnAndRow($napKezdoOszlopa, $datumSor, $napKezdoOszlopa, $datumSor + count($osztalyok) + 1)
                ->applyFromArray($styleArray);
            
            $napKezdoOszlopa += $napMaxSzelessege;
            if ($napKezdoOszlopa > $tableWidth) $tableWidth = $napKezdoOszlopa;
            $currentDate->add(new \DateInterval('P1D'));
            
            //  datumsor: aluhuzva, felkover, kozepen
            $styleArray = [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],                
            ];
            $as->getStyleByColumnAndRow(1, $datumSor, ($napKezdoOszlopa - 1), $datumSor)->applyFromArray($styleArray);
            
            $styleArray = [
                'borders' => [
                    'horizontal' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $as->getStyleByColumnAndRow(1, $datumSor + 1, $napKezdoOszlopa - 1, $datumSor + count($osztalyok) + 1)->applyFromArray($styleArray);
        }
        

        $styleArray = [
            'borders' => [
                'diagonal' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'diagonalDirection' => \PhpOffice\PhpSpreadsheet\Style\Borders::DIAGONAL_BOTH,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
//        $as->getStyleByColumnAndRow(15, 1, 16, 1)->applyFromArray($styleArray);
//        $as->getStyleByColumnAndRow(15, 1)->getBorders()->applyFromArray([
//            'diagonal' => [
//                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                'diagonalDirection' => \PhpOffice\PhpSpreadsheet\Style\Borders::DIAGONAL_BOTH,
//                'color' => ['rgb' => 'ff0000'],
//            ]
//        ]);
        

                    
        return $spreadsheet;
    }
    
    /*
     * Teljes orarend, osztalyonkent csoportositott nezet!
     * A TimetableManager->getToPrintGroupByClass() adatait dolgozza fel.
     */
    public function createClassPdf($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $pdf = new \App\MyTCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Csernai Zsolt');
        $pdf->SetTitle('Esti órarend');

        $pdf->SetHeaderData('', 0, 'Teljes', '', array(0, 0, 0), array(0, 0, 0));
        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
//        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 20, 10);
//        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
//        $pdf->SetFont('times', '', 10, '', true);
        $pdf->SetFont('freeserif', '', 12);

        $pdf->SetLineWidth(0.08);

        $pdf->setHeaderTitle('Felnött órarend');
        
        $simavonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $vastagvonal = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $szagatottvonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(0, 0, 0));
        
        $border = array('B' => $simavonal);
        
        $height = 6;
        $dateHeight = 12;

        $osztalySzelesseg = 0;        
        $datumSzelesseg = 50;
        $oraszamSzelesseg = 20;
        $tanarSzelesseg = 25;
        $tantargySzelesseg = 20; //50;


        foreach($orarend as $osztalyid => $osztalyResz)
        {
            $pdf->AddPage();            
            
            $pdf->resetColumns();
            
            $pdf->SetFont('freeserif', 'B', 16);
            $pdf->Cell($osztalySzelesseg, $height, $osztalyid, 0, 0, 'C', false, '', 0, false, 'T', 'B');
            $pdf->Ln();
            
            $pdf->setEqualColumns(2);
            
            foreach($osztalyResz as $dateid => $datumResz)
            {
                $nap = $dateHelper->getDayName($dateid, 'Y.m.d');
                $pdf->SetFont('freeserif', 'B', 11);
                $pdf->Cell($datumSzelesseg, $dateHeight, $dateid . ' - ' . $nap, 0, 0, 'L', false, '', 0, false, 'T', 'B');
                $pdf->Ln();
                
                $pdf->SetFont('freeserif', '', 11);
                            
                foreach($datumResz as $adatok)
                {
                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor = '' . $adatok['kezdes'];
                    else $mikor = '' . $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $pdf->Cell($oraszamSzelesseg, $height, $mikor, $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    $pdf->Cell($tanarSzelesseg, $height, $adatok['tanar'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)                                                
//                    $pdf->MultiCell($tanarSzelesseg, $height, $adatok['tanar'], $border, 'L', 0, 0, '', '', true, false, true, 0, 'B');
                    $pdf->Cell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
//                    $pdf->MultiCell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 'L', 0, 0, '', '', false, 0, false, true, 0);
                    
                    $pdf->Ln();                    
                }
            }
        }
        
        return $pdf;
    }
    
    /*
     * Teljes orarend, osztalyonkent csoportositott nezet!
     * A TimetableManager->getToPrintGroupByClass() adatait dolgozza fel.
     */
    public function createClassExcel($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Esti órarend");
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        

        $sor = 2;
        foreach($orarend as $osztalyid => $osztalyResz)
        {
            $as->setCellValueByColumnAndRow(1, $sor, $osztalyid);
            $as->getStyleByColumnAndRow(1, $sor)->getFont()->setBold(true);
            $sor += 1;
            
            foreach($osztalyResz as $dateid => $datumResz)
            {
                $currentDate = \DateTime::createFromFormat('Y.m.d', $dateid);
                $rovidDatum = $dateHelper->getMonthName($currentDate) . '. ' . $currentDate->format('d');
                
                $nap = $dateHelper->getDayName($dateid, 'Y.m.d');
//                $as->setCellValueByColumnAndRow(2, $sor, $dateid . ' - ' . $nap);
                $as->setCellValueByColumnAndRow(2, $sor, $rovidDatum . ' - ' . $nap);
                
                foreach($datumResz as $adatok)
                {
                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor = '' . $adatok['kezdes'];
                    else $mikor = '' . $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $hossz = (int)($adatok['vege'] - $adatok['kezdes'] + 1);
                    if ($hossz != 3) $mikor .= ' (' . $hossz . ')';
                    
                    $as->setCellValueByColumnAndRow(3, $sor, $mikor);
                    $as->setCellValueByColumnAndRow(4, $sor, $adatok['tanar']);
                    $as->setCellValueByColumnAndRow(5, $sor, $adatok['tantargy']);
                    
                    $sor += 1;
                }
            }
        }
        
        return $spreadsheet;
    }

    /*
     * Teljes orarend, osztalyonkent csoportositott nezet!
     * A TimetableManager->getToPrintGroupByClass() adatait dolgozza fel.
     */
    public function createFullList($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Órarend");
        //$as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        //$as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        $as->setCellValueByColumnAndRow(2, 1, "Dátum");
        $as->setCellValueByColumnAndRow(3, 1, "Nap");
        $as->setCellValueByColumnAndRow(4, 1, "Hányadik órában");
        $as->setCellValueByColumnAndRow(5, 1, "Hány óra");
        $as->setCellValueByColumnAndRow(6, 1, "Tanár");
        $as->setCellValueByColumnAndRow(7, 1, "Tantárgy");
        

        $sor = 2;
        foreach($orarend as $osztalyid => $osztalyResz)
        {
            /*
            $as->setCellValueByColumnAndRow(1, $sor, $osztalyid);
            $as->getStyleByColumnAndRow(1, $sor)->getFont()->setBold(true);
            $sor += 1;
            */
            
            foreach($osztalyResz as $dateid => $datumResz)
            {
                foreach($datumResz as $adatok)
                {
                    $as->setCellValueByColumnAndRow(1, $sor, $osztalyid);

                    $currentDate = \DateTime::createFromFormat('Y.m.d', $dateid);

                    //$rovidDatum = $dateHelper->getMonthName($currentDate) . '. ' . $currentDate->format('d');
                    $as->setCellValueByColumnAndRow(2, $sor, $currentDate->format('Y.m.d'));
                    
                    $nap = $dateHelper->getDayName($dateid, 'Y.m.d');                
                    $as->setCellValueByColumnAndRow(3, $sor, $nap);

                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor = '' . $adatok['kezdes'];
                    else $mikor = '' . $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    $as->setCellValueByColumnAndRow(4, $sor, $mikor);

                    $hossz = (int)($adatok['vege'] - $adatok['kezdes'] + 1);
                    $as->setCellValueByColumnAndRow(5, $sor, $hossz);

                    $as->setCellValueByColumnAndRow(6, $sor, $adatok['tanar']);
                    $as->setCellValueByColumnAndRow(7, $sor, $adatok['tantargy']);
                    
                    $sor += 1;
                }
            }
        }
        
        return $spreadsheet;
    }
    
    /*
     * Teljes orarend, tanaronkent csoportositott nezet!
     * A TimetableManager->getToPrintGroupByTeacher() adatait dolgozza fel.
     */
    public function createTeacherPdf($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $pdf = new \App\MyTCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Csernai Zsolt');
        $pdf->SetTitle('Esti órarend');

        $pdf->setHeaderTitle('Felnött órarend');
        
        $pdf->SetHeaderData('', 0, 'Teljes', '', array(0, 0, 0), array(0, 0, 0));
        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
//        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 20, 10);
//        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
//        $pdf->SetFont('times', '', 10, '', true);
        $pdf->SetFont('freeserif', '', 12);

        $pdf->SetLineWidth(0.08);

        $simavonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $vastagvonal = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $szagatottvonal = array('width' => 0.08, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(0, 0, 0));
        
        $border = array('B' => $simavonal);
        
        $height = 6;
        $dateHeight = 12;

        $tanarSzelesseg = 0;        
        $datumSzelesseg = 40;        
        $osztalySzelesseg = 15;        
        $oraszamSzelesseg = 20;
        $tantargySzelesseg = 0; //50;


        foreach($orarend as $tanarid => $tanarResz)
        {
            $pdf->AddPage();

            $pdf->resetColumns();
            
            $pdf->SetFont('freeserif', 'B', 16);
            $pdf->Cell($tanarSzelesseg, $height, $tanarResz['tanar'], 0, 0, 'C', false, '', 0, false, 'T', 'B');
            $pdf->Ln();

            $pdf->setEqualColumns(2, 85);

            
            foreach($tanarResz as $key => $datumResz)
            {
                if ($key == 'tanar') continue;
                
                $nap = $dateHelper->getDayName($key, 'Y.m.d');
                
                $pdf->SetFont('freeserif', '', 11);
                            
                foreach($datumResz as $adatok)
                {
                    $mikor = '';
                    if ($adatok['kezdes'] < 10) $mikor = '  ';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor .= $adatok['kezdes'];
                    else $mikor .= $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $pdf->Cell($datumSzelesseg, $height, $key . ' - ' . $nap, $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    $pdf->Cell($osztalySzelesseg, $height, $adatok['osztaly'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    
                    $pdf->Cell($oraszamSzelesseg, $height, $mikor, $border, 0, 'L', false, '', 0, false, 'T', 'B');
                    
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)                                                
//                    $pdf->MultiCell($tanarSzelesseg, $height, $adatok['tanar'], $border, 'L', 0, 0, '', '', true, false, true, 0, 'B');
                    $pdf->Cell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 0, 'L', false, '', 0, false, 'T', 'B');
//                    $pdf->MultiCell($tantargySzelesseg, $height, $adatok['tantargy'], $border, 'L', 0, 0, '', '', false, 0, false, true, 0);
                    
                    $pdf->Ln();                    
                }
            }
        }
        
        return $pdf;
    }
    
    /*
     * Teljes orarend, tanaronkent csoportositott nezet!
     * A TimetableManager->getToPrintGroupByTeacher() adatait dolgozza fel.
     */
    public function createTeacherExcel($orarend)
    {
        $dateHelper = new \App\DateHelper();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Esti órarend");
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        
        $sor = 2;
        foreach($orarend as $tanarResz)
        {
            $as->setCellValueByColumnAndRow(1, $sor, $tanarResz['tanar']);
            $as->getStyleByColumnAndRow(1, $sor)->getFont()->setBold(true);
            $sor += 1;

            foreach($tanarResz as $key => $datumResz)
            {
                if ($key == 'tanar') continue;
                
                $currentDate = \DateTime::createFromFormat('Y.m.d', $key);
                $rovidDatum = $dateHelper->getMonthName($currentDate) . '. ' . $currentDate->format('d');
                
                $nap = $dateHelper->getDayName($key, 'Y.m.d');
                
                $as->setCellValueByColumnAndRow(2, $sor, $rovidDatum . ' - ' . $nap);
                
                foreach($datumResz as $adatok)
                {
                    $mikor = '';
                    if ($adatok['kezdes'] == $adatok['vege']) $mikor .= $adatok['kezdes'];
                    else $mikor .= $adatok['kezdes'] . ' - ' . $adatok['vege'];
                    
                    $hossz = (int)($adatok['vege'] - $adatok['kezdes'] + 1);
                    if ($hossz != 3) $mikor .= " (" . $hossz . ")";
                    
//                    $as->setCellValueByColumnAndRow(2, $sor, $rovidDatum);
//                    $as->setCellValueByColumnAndRow(3, $sor, $nap);
                    $as->setCellValueByColumnAndRow(3, $sor, $adatok['osztaly']);
                    $as->setCellValueByColumnAndRow(4, $sor, $mikor);
                    $as->setCellValueByColumnAndRow(5, $sor, $adatok['tantargy']);
                    
                    $sor += 1;
                }
            }
        }
        
        return $spreadsheet;
    }

    /**
     * Tanorak, tanronkenti csoportositasban
     * 
     * tanar
     *      osztaly - tantargy - oraszam
     * @param array $lessons A ttm->getLessonsForHumans eredmenye
     */
    public function createLessonsGroupByTeacherExcel($lessons)
    {
        $adatok = array();
        foreach($lessons as $lesson)
        {
            if ( !isset($adatok[$lesson['tanar']])) $adatok[$lesson['tanar']] = array();
            if ( !isset($adatok[$lesson['tanar']][$lesson['osztaly']])) $adatok[$lesson['tanar']][$lesson['osztaly']] = array();
            
            $adatok[$lesson['tanar']][$lesson['osztaly']][] = $lesson;
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Esti oktatas taorai");
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        
        $sor = 2;
        foreach($adatok as $tanar => $tanarResz)
        {
            $as->setCellValueByColumnAndRow(1, $sor, $tanar);
            $as->getStyleByColumnAndRow(1, $sor)->getFont()->setBold(true);
            $sor += 1;
            
            foreach($tanarResz as $osztaly => $osztalyResz)
            {
                $as->setCellValueByColumnAndRow(2, $sor, $osztaly);
                $sor += 1;
                
                foreach($osztalyResz as $tanora)
                {
                    $as->setCellValueByColumnAndRow(3, $sor, $tanora['tantargy']);
                    $as->setCellValueByColumnAndRow(4, $sor, $tanora['num']);
                    $sor += 1;
                }
            }
        }
        
        return $spreadsheet;
    }
    
    /**
     * Tanorak, osztalyonkenti csoportositasban
     * 
     * osztaly
     *      tanar - tantargy - oraszam
     * @param array $lessons A ttm->getLessonsForHumans eredmenye
     */
    public function createLessonsGroupByClassExcel($lessons)
    {
        $adatok = array();
        foreach($lessons as $lesson)
        {
            if ( !isset($adatok[$lesson['osztaly']])) $adatok[$lesson['osztaly']] = array();
            
            $adatok[$lesson['osztaly']][] = $lesson;
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Esti oktatas tanorai");
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        
        $as->setCellValueByColumnAndRow(1, 2, "Osztaly");
        $as->setCellValueByColumnAndRow(2, 2, "Tanar");
        $as->setCellValueByColumnAndRow(3, 2, "Tantargy");
        $as->setCellValueByColumnAndRow(4, 2, "Oraszam");
        
        $sor = 3;
        foreach($adatok as $osztaly => $osztalyResz)
        {
            $as->setCellValueByColumnAndRow(1, $sor, $osztaly);
            $as->getStyleByColumnAndRow(1, $sor)->getFont()->setBold(true);
            $sor += 1;
            
            foreach($osztalyResz as $tanora)
            {
                $as->setCellValueByColumnAndRow(2, $sor, $tanora['tanar']);                
                $as->setCellValueByColumnAndRow(3, $sor, $tanora['tantargy']);
                $as->setCellValueByColumnAndRow(4, $sor, $tanora['num']);
                $sor += 1;
            }
        }
        
        return $spreadsheet;
    }
    
    /**
     * 
     * @param array $teachers
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function createTeachersList($teachers)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $as = $spreadsheet->getActiveSheet();

        $as->setCellValueByColumnAndRow(1, 1, "Tanarok");
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
        $as->getStyleByColumnAndRow(1, 1)->getFont()->setSize(14);
        
        $sor = 2;
        foreach($teachers as $tanar)
        {
            $as->setCellValueByColumnAndRow(1, $sor, $tanar['name']);
            $as->setCellValueByColumnAndRow(2, $sor, $tanar['short_name']);
            $as->setCellValueByColumnAndRow(3, $sor, $tanar['num']);
            $sor += 1;
        }
        
        return $spreadsheet;
    }
    
    /*
     * Teljes orarend!
     * A TimetableManager->getToPrintGroupByDate() adatait dolgozza fel.
     * HTML tablazatatot ad vissza.
     */
    public function createFullHtml($orarend)
    {
        $html = '<table style="border: solid 1px #00ff00;">';
        
        foreach($orarend as $dateid => $datumResz)
        {
            $html .= '<tr><td colspan="4" style="font-weight: bold; margin-top: 10px;">' . $dateid . '</td></tr>';
            
            $html .= '<tr><td>Osztaly</td><td>Oraszam</td><td>Tanar</td><td>Tantargy</td></tr>';
            
            foreach($datumResz as $osztaly => $osztalyResz)
            {
                $elso = true;
                
                foreach($osztalyResz as $adatok)
                {
                    $html .= '<tr style="border: solid 2px #ffffff;">';
                    
                    $html .= '<td>';
                    if ($elso) {
                        $elso = false;
                        $html .= $osztaly;
                    }
                    $html .= '</td>';
                    
                    if ($adatok['kezdes'] == $adatok['vege']) $html .= '<td>' . $adatok['kezdes'] . '</td>';
                    else $html .= '<td>' . $adatok['kezdes'] . ' - ' . $adatok['vege'] . '</td>';
                    $html .= '<td>' . $adatok['tanar'] . '</td>';
                    $html .= '<td>' . $adatok['tantargy'] . '</td>';
                    
                    $html .= '</tr>';
                }
            }
        }
        
        $html .= '</table>';
             
        return $html;
    }
    

    /**
     * A kapott koordinatat atalakitja az Excel fele betu-szam koordinatara pl: 2,1 => B1
     * @param int $col
     * @param int $row
     */
    public function convertCoordinateToExcelCoord($col, $row)
    {
        return strtoupper( $this->convertNumberToLetter($col) ) . $row;
    }
    
    /**
     * Egy szamrol megmondja, melyik betut jeloli az angol abc-ben.
     * a = 1 
     * Ha a szam nagyobb 26, akkor duplazza pl: 27 = aa
     * @param int $col
     * @return string
     */
    public function convertNumberToLetter($col)
    {
        if ($col < 1) return "";
        $alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $len = count($alphabet);
        $r = "";        
        
        if ($col > $len)
        {
            $r .= convert((int)floor($col / $len));
            $col = $col % $len;
        }
    
        $r .= $alphabet[$col - 1];
        return $r;
    }
}
