<?php
namespace App;

class DateHelper
{
    /**
     * Megadja, hogy milyen napra esett a datum.
     * 
     * @param string $date Formatum: Y.m.d
     * @return string A nap neve magyarul.
     */
    public function getDayName($date,  $format = 'Y.m.d')
    {
        if ($date instanceof \DateTime) {
            $datum = $date;
        }
        else {
            $datum = \DateTime::createFromFormat($format, $date);            
        }
        $day = (int)$datum->format("N");
        $s = '';
        switch($day)
        {
            case 1: $s = 'Hétfő'; break;
            case 2: $s = 'Kedd'; break;
            case 3: $s = 'Szerda'; break;
            case 4: $s = 'Csütörtök'; break;
            case 5: $s = 'Péntek'; break;
            case 6: $s = 'Szombat'; break;
            case 7: $s = 'Vasárnap'; break;
        }
        return $s;
    }

    public function getMonthName($date,  $format = 'Y.m.d')
    {
        if ($date instanceof \DateTime) {
            $datum = $date;
        }
        else {
            $datum = \DateTime::createFromFormat($format, $date);            
        }
        $month = (int)$datum->format("n");
        $s = '';
        switch($month)
        {
            case 1: $s = 'jan'; break;
            case 2: $s = 'febr'; break;
            case 3: $s = 'márc'; break;
            case 4: $s = 'ápr'; break;
            case 5: $s = 'máj'; break;
            case 6: $s = 'jún'; break;
            case 7: $s = 'júl'; break;
            case 8: $s = 'aug'; break;
            case 9: $s = 'szept'; break;
            case 10: $s = 'okt'; break;
            case 11: $s = 'nov'; break;
            case 12: $s = 'dec'; break;
        }
        return $s;
    }
    
    
}
