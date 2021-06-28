<?php
namespace App\Controller;

class ClassController extends Controller
{
    
    public function index($req, $resp)
    {
        $mClass = new \App\Schoolclass($this->db);
        $mLesson = new \App\Lesson($this->db);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('schoolclass/index.php', array(
                'osztalyok' => $mClass->getAll('ORDER BY name'),
                'oraszamok' => $mLesson->getClassesSum()
            )),
            '_pagetitle' => 'Orarend - osztalyok'
        ));
    }
    
    public function edit($req, $resp, $args)
    {
        $id = (int)isset($args['id']) ? $args['id'] : -1;
        
        $mClass = new \App\Schoolclass($this->db);

        $validator = new \App\Validator();    
        
        if ($req->isPost())
        {
            $class = $mClass->populateFromArray($req->getParsedBody());
            
            $validator->validateArray($class, $mClass->getValidationRules());
            
            if ( !$validator->hasError())
            {
                $mClass->save($class);
                return $resp->withRedirect('/class/index', 301);
            }
        }
        else if ($id == -1)
        {
            //  uj
            $class = $mClass->getNew();
        }
        else
        {
            //  letezo szerkesztese
            $class = $mClass->get($id);
            if ($class == null) $class = $mClass->getNew();
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('schoolclass/edit.php', array(
                'osztaly' => $class,
                'orak_szama' => $mClass->getLessonsCount($id),
                '_validator' => $validator,
            )),            
            '_pagetitle' => 'Osztaly szerkesztese'
        ));
    }

    public function delete($req, $resp, $args)
    {
        $post = $req->getParsedBody();
        $id = (int)\App\Helper::get($post, 'id', -1);
        
        $mClass = new \App\Schoolclass($this->db);
        $class = $mClass->get($id);
        
        if ($class == NULL)
        {
            $this->container->flash->addMessage('system_message', 'Nincs #' . $id . ' szamu osztaly!');
            return $resp->withRedirect('/class/index', 301);
        }
        
        if ($mClass->getLessonsCount($id) > 0)
        {
            $this->container->flash->addMessage('system_message', 'Nem torolheto, amig vannak orai!');
            return $resp->withRedirect('/class/edit/' . $id, 301);
        }
        
        $mClass->delete($id);

        $this->container->flash->addMessage('system_message', $class['name'] . ' torolve!');
        return $resp->withRedirect('/class/index', 301);        
    }
}