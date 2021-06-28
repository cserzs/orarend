<?php
namespace App\Controller;

class TeacherController extends Controller
{
    
    public function index($req, $resp)
    {
        $mTeacher = new \App\Teacher($this->db);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('teacher/index.php', array(
                'tanarok' => $mTeacher->getAll('ORDER BY name')        
            )),
            '_pagetitle' => 'Orarend - tanarok'
        ));
    }

    public function edit($req, $resp, $args)
    {
        $id = (int)isset($args['id']) ? $args['id'] : -1;
        
        $mTeacher = new \App\Teacher($this->db);
        $teacher = array();
        $lessons = array();
        
        $validator = new \App\Validator();    
        
        if ($req->isPost())
        {
            $teacher = $mTeacher->populateFromArray($req->getParsedBody());
            
            $validator->validateArray($teacher, $mTeacher->getValidationRules());
            
            if ( !$validator->hasError())
            {
                $id = $mTeacher->save($teacher);
                $this->container->flash->addMessage('system_message', 'Tanar mentese sikeres: ' . $teacher['name'] . ' (#' . $id . ')!');
                return $resp->withRedirect('/teacher/index', 301);
            }
        }
        else if ($id == -1)
        {
            //  uj
            $teacher = $mTeacher->getNew();
        }
        else
        {
            //  letezo szerkesztese
            $teacher = $mTeacher->get($id);
            if ($teacher == null) {
                $teacher = $mTeacher->getNew();
            } 
            else {
                $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
                $lessons = $ttm->getLessonsOfTeacherForHumans($id);
            }
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('teacher/edit.php', array(
                'tanar' => $teacher,
                'tanorakSzama' => ($id > 0 ? $mTeacher->getLessonsCount($id) : 0),
                'tanorak' => $lessons,
                '_validator' => $validator,
            )),            
            '_pagetitle' => 'Tanar szerkesztese'
        ));
    }
    
    //  post
    public function delete($req, $resp, $args)
    {
        $post = $req->getParsedBody();
        $id = (int)\App\Helper::get($post, 'id', -1);
        
        $mTeacher = new \App\Teacher($this->db);
        $teacher = $mTeacher->get($id);
        
        if ($teacher == NULL)
        {
            $this->container->flash->addMessage('system_message', 'Nincs #' . $id . ' szamu tanar!');
            return $resp->withRedirect('/teacher/index', 301);
        }
        
        if ($mTeacher->getLessonsCount($id) > 0)
        {
            $this->container->flash->addMessage('system_message', 'Nem törölhető, amíg van órája!');
            return $resp->withRedirect('/teacher/edit/' . $id, 301);
        }
        
        $mTeacher->delete($id);

        $this->container->flash->addMessage('system_message', $teacher['name'] . ' (#' . $teacher['id'] . ') törölve!');
        return $resp->withRedirect('/teacher/index', 301);        
    }
    
}