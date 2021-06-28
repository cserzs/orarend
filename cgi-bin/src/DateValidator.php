<?php
namespace App;

class DateValidator
{
    /**
     * Megnezi, hogy ervenyes datum-e a parameter.
     * @param string $date
     * @param string $format
     * @return boolean
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}