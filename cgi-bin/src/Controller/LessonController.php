<?php
namespace App\Controller;

class LessonController extends Controller
{
    
    public function index($req, $resp)
    {
        $mClass = new \App\Schoolclass($this->db);
        $mTeacher = new \App\Teacher($this->db);
        $mLesson = new \App\Lesson($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        $page = (int)isset($_GET['page']) ? $_GET['page']: 1;
        if ($page < 1) $page = 1;
        $limit = (int)isset($_GET['limit']) ? $_GET['limit']: 25;
        if ($limit < 1) $limit = 25;
        
        $osszesTanora = $mLesson->getSum();
        
        $offset = ($page - 1) * $limit;
        $lastPage = (ceil($osszesTanora / $limit) == 0 ? 1 : ceil($osszesTanora / $limit));
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('lesson/index.php', array(
                'tanorak' => $ttm->getLessonsForHumans($limit, $offset),
                'osszesTanora' => $osszesTanora,
                'osztalyok' => $mClass->getAll('ORDER BY short_name'),
                'tanarok' => $mTeacher->getAll('ORDER BY name'),
                'tanitasi_hetek' => $ttm->tanitasi_hetek,
                'paginationVisible' => $osszesTanora > $limit,
                'lastPage' => $lastPage
            )),
            '_pagetitle' => 'Orarend - Tanorak',
        ));
    }

    public function edit($req, $resp, $args)
    {
        $id = (int)(isset($args['id']) ? $args['id'] : -1);
        
        $mLesson = new \App\Lesson($this->db);
        $mTeacher = new \App\Teacher($this->db);
        $mSubject = new \App\Subject($this->db);
        $mClass = new \App\Schoolclass($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        $lesson = array();
        $errors = array();
        
        if ($req->isPost())
        {
            $data = $req->getParsedBody();
            $lesson = $mLesson->populateFromArray($data);
            $errors = $mLesson->validateLesson($lesson);
            if (empty($errors))
            {
                $id = $mLesson->save($lesson);
                $this->container->flash->addMessage('system_message', 'Az ora mentese sikeres (id: ' . $id . ')!');    
                if (isset($data['saveAndNew'])) return $resp->withRedirect('/lesson/edit', 301);
                //return $resp->withRedirect('/lesson/index', 301);
                return $resp->withRedirect('/lesson/showClass/' . $lesson['class_id'], 301);
            }
        }
        else if ($id == -1)
        {
            //  uj
            $lesson = $mLesson->getNew();
        }
        else
        {
            //  letezo szerkesztese
            $lesson = $mLesson->get($id);
            if ($lesson == null) $lesson = $mLesson->getNew();
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('lesson/edit.php', array(
                'tanarok' => $mTeacher->getAll('ORDER BY name'),
                'osztalyok' => $mClass->getAll('ORDER BY name'),
                'tantargyak' => $mSubject->getAll('ORDER BY name'),
                'tanora' => $lesson,
                'tanitasi_hetek' => $ttm->tanitasi_hetek,
                'errors' => $errors
            )),            
            '_pagetitle' => 'Orarend - tanora szerkesztese'
        ));
        
    }

    public function showClass($req, $resp, $args)
    {
        $classid = -1;
        if ($req->isGet()) $classid = (int)(isset($args['classid']) ? $args['classid'] : -1);
        if ($req->isPost()) {
            $data = $req->getParsedBody();
            $classid = (int)(isset($data['classid']) ? $data['classid'] : -1);
        }
        if ($classid == -1)
        {
            $this->container->flash->addMessage('system_message', 'Hianyzo parameter!');    
            return $resp->withRedirect('/lesson/index', 301);
        }

        $mClass = new \App\Schoolclass($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        $osztaly = $mClass->get($classid);
        if ($osztaly == NULL)
        {
            $this->container->flash->addMessage('system_message', 'Nincs osztaly ilyen azonositoval: ' . $classid);    
            return $resp->withRedirect('/lesson/index', 301);
        }
        
        $lessons = $ttm->getLessonsOfClassForHumans($classid);
        $sum = $ttm->getLessonsSum($lessons);
        $gyakorlatok_szama = $ttm->getPracticeLessonsSum($lessons);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('lesson/show_partials.php', array(
                'owner' => $osztaly['name'],
                'tanorak' => $lessons,
                'osszoraszam' => $sum,
                'gyakorlatok_szama' => $gyakorlatok_szama,
                'tanitasi_hetek' => $ttm->tanitasi_hetek,
                'from' => 'class_' . $osztaly['id']
            )),
            '_pagetitle' => $osztaly['name'] . ' osztaly orai',
        ));
        
    }

    public function showTeacher($req, $resp, $args)
    {
        $teacherid = -1;
        if ($req->isGet()) $teacherid = (int)(isset($args['teacherid']) ? $args['teacherid'] : -1);
        if ($req->isPost()) {
            $data = $req->getParsedBody();
            $teacherid = (int)(isset($data['teacherid']) ? $data['teacherid'] : -1);
        }
        if ($teacherid == -1)
        {
            $this->container->flash->addMessage('system_message', 'Hianyzo parameter!');    
            return $resp->withRedirect('/lesson/index', 301);
        }
        
        $mTeacher = new \App\Teacher($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);
        
        $tanar = $mTeacher->get($teacherid);
        if ($tanar == NULL)
        {
            $this->container->flash->addMessage('system_message', 'Nincs tanar ilyen azonositoval: ' . $teacherid);    
            return $resp->withRedirect('/lesson/index', 301);
        }
        
        $lessons = $ttm->getLessonsOfTeacherForHumans($teacherid);
        $sum = $ttm->getLessonsSum($lessons);
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('lesson/show_partials.php', array(
                'owner' => $tanar['name'],
                'tanorak' => $lessons,
                'osszoraszam' => $sum,
                'tanitasi_hetek' => $ttm->tanitasi_hetek,
                'from' => 'teacher_' . $tanar['id']
            )),
            '_pagetitle' => $tanar['name'] . ' orai',
        ));
    
    }

    public function delete($req, $resp, $args)
    {
        $redirectUrl = '/lesson/index';        
        $redirectUrls = array(
            'class' => '/lesson/showClass/',
            'teacher' => '/lesson/showTeacher/'
        );

        $id = (int)(isset($args['id']) ? $args['id'] : -1);

        $fromParam = $req->getQueryParam('from', null);
        if ($fromParam != null) {
            $parts = explode("_", $fromParam);
            if (count($parts) == 2 && array_key_exists($parts[0], $redirectUrls)) {
                $redirectUrl = $redirectUrls[$parts[0]];
                $fromId = (int)$parts[1];
                $redirectUrl .= $fromId;
            }
        }

        $mLesson = new \App\Lesson($this->db);
        $lesson = $mLesson->get($id);
        
        if ($lesson == null) {
            $this->container->flash->addMessage('system_message', 'Nincs ' . $id . ' szamú tanóra!');
            return $resp->withRedirect($redirectUrl, 301);
        }
        
        if ($lesson['num_in_tt'] > 0) {
            $this->container->flash->addMessage('system_message', 'Nem törölhető!<br/>Csak olyan tanóra törölhető, ami nincs órarendben!');
            return $resp->withRedirect($redirectUrl, 301);
        }
       
        $mLesson->delete($id);

        $this->container->flash->addMessage('system_message', 'Tanóra sikeresen törölve!');        
        return $resp->withRedirect($redirectUrl, 301);        
    }

    public function addToClass($req, $resp, $args)
    {
        $mLesson = new \App\Lesson($this->db);
        $mTeacher = new \App\Teacher($this->db);
        $mSubject = new \App\Subject($this->db);
        $mClass = new \App\Schoolclass($this->db);
        $ttm = new \App\TimetableManager($this->db, $this->seasonManager);

        $classid = (int)(isset($args['id']) ? $args['id'] : -1);
        
        $lessons = array();
        $errors = array();
        
        if ($req->isPost()) 
        {
            $data = $req->getParsedBody();
            $classid = $data['classid'];
            $teachers = $data['teachers'];
            $subjects = $data['subjects'];
            $nums = $data['nums'];
            $weeknums = $data['weeknums'];

            $osztaly = $mClass->get($classid);

            $errors = $mLesson->validateLessons($classid, $teachers, $subjects, $nums, $weeknums);
            if (count($errors) < 1) 
            {
                $lessons = $mLesson->prepareToSaveFromPost($classid, $teachers, $subjects, $nums, $weeknums, $ttm->tanitasi_hetek);
                $n = $mLesson->insertMultiple($lessons);
                
                $this->container->flash->addMessage('system_message', $n . ' új óra került rögzítésre a ' . $osztaly['name'] . ' számára!');
                return $resp->withRedirect('/lesson/index', 301);
            }
        }
        
        return $this->render($resp, 'main_layout.php', array(
            '_page' => $this->fetch('lesson/add_to_class.php', array(
                'osztaly' => $mClass->get($classid),
                'tanarok' => $mTeacher->getAll('ORDER BY name'),
                'tantargyak' => $mSubject->getAll('ORDER BY name'),
                'tanitasi_hetek' => $ttm->tanitasi_hetek,
                'tanorak' => $lessons,
                'errors' => $errors
            )),
            '_pagetitle' => 'Uj tanorak',
        ));
        
    }
    
}