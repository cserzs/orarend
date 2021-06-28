<?php
namespace App\Controller;

class SubjectController extends Controller
{
    
    public function index($req, $resp)
    {
        $mSubject = new  \App\Subject($this->db);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('subject/index.php', array(
                'tantargyak' => $mSubject->getAll('ORDER BY name')        
            )),
            '_pagetitle' => 'Orarend - tanarok'
        ));
    }

    public function edit($req, $resp, $args)
    {
        $id = (int)isset($args['id']) ? $args['id'] : -1;
        
        $mSubject = new \App\Subject($this->db);
        $subj = array();
        
        $validator = new \App\Validator(); 
        $lessons = array();   
        
        if ($req->isPost())
        {
            $subj = $mSubject->populateFromArray($req->getParsedBody());
            
            $validator->validateArray($subj, $mSubject->getValidationRules());
            
            if ( !$validator->hasError())
            {
                $id = $mSubject->save($subj);
                $this->container->flash->addMessage('system_message', 'Tantargy mentese sikeres: ' . $subj['name'] . ' (#' . $id . ')!');
                return $resp->withRedirect('/subject/index', 301);
            }
        }
        else if ($id == -1)
        {
            //  uj
            $subj = $mSubject->getNew();
        }
        else
        {
            //  letezo szerkesztese
            $subj = $mSubject->get($id);
            if ($subj == null) {
                $subj = $mSubject->getNew();
            }
            else {
                $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
                $lessons = $ttm->getLessonsBySubjectForHumans($id);
            }
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('subject/edit.php', array(
                'tantargy' => $subj,
                'tanorakSzama' => ($id > 0 ? $mSubject->getLessonsCount($id) : 0),
                'tanorak' => $lessons,
                '_validator' => $validator,
            )),            
            '_pagetitle' => 'Tantargy szerkesztese'
        ));
    }
    
    //  post
    public function delete($req, $resp, $args)
    {
        $post = $req->getParsedBody();
        $id = (int)\App\Helper::get($post, 'id', -1);
        
        $mSubject = new \App\Subject($this->db);
        $subj = $mSubject->get($id);
        
        if ($subj == NULL)
        {
            $this->container->flash->addMessage('system_message', 'Nincs #' . $id . ' szamu tantargy!');
            return $resp->withRedirect('/subject/index', 301);
        }
        
        if ($mSubject->getLessonsCount($id) > 0)
        {
            $this->container->flash->addMessage('system_message', 'Nem torolheto, amig hasznalva van!');
            return $resp->withRedirect('/subject/edit/' . $id, 301);
        }
        
        $mSubject->delete($id);

        $this->container->flash->addMessage('system_message', $subj['name'] . ' (#' . $subj['id'] . ') torolve!');
        return $resp->withRedirect('/subject/index', 301);        
    }
    
}