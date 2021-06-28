<?php
namespace App\Controller;

class ApiController extends Controller
{
    
    public function class($req, $resp)
    {
        $mClass = new \App\Schoolclass($this->db);
        return $resp->withJson($mClass->toApi());
    }
    
    public function lesson($req, $resp, $args)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        return $resp->withJson($ttm->getLessonstoApi());
    }

    public function teacher($req, $resp)
    {
        $mTeacher = new \App\Teacher($this->db);
        return $resp->withJson($mTeacher->toApi());
    }

    public function tt($req, $resp)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        return $resp->withJson($ttm->getTimetableToJs());
    }

    public function basedata($req, $resp)
    {
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);

        $elsoNap = \DateTime::createFromFormat('Y.m.d', $ttm->elso_tanitasi_nap);
        $utolsoNap = \DateTime::createFromFormat('Y.m.d', $ttm->utolso_tanitasi_nap);

        $data = array(
            'napi_oraszam' => $ttm->napi_oraszam,
            'kezdo_oraszam' => $ttm->kezdo_oraszam,            
            //'elso_tanitasi_nap' => '' . $elsoNap->format('Y') . ',' . ($elsoNap->format('n')) . ',' . $elsoNap->format('j'),
            //'elso_tanitasi_nap' => $ttm->elso_tanitasi_nap,
            'elso_tanitasi_nap' => $elsoNap->format('r'),
            //'utolso_tanitasi_nap' => '' . $utolsoNap->format('Y') . ',' . ($utolsoNap->format('n')-1) . ',' . $utolsoNap->format('j'),
            //'utolso_tanitasi_nap' => $ttm->utolso_tanitasi_nap,
            'utolso_tanitasi_nap' => $utolsoNap->format('r'),
            'hetek_szama' => $ttm->hetek_szama,
            'utolso_mentes' => $ttm->utolso_mentes,
            'heti_max_oraszam' => $ttm->heti_max_oraszam
        );

        return $resp->withJson($data);
    }

}