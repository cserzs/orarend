<?php
namespace App;

use Respect\Validation\Validator as v;

class Lesson
{
    protected $pdo;
    protected $season_id = -1;
            
    function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->season_id = \App\SeasonManager::instance($pdo)->active_season;
    }

    public function setSeasonId($season_id)
    {
        $this->season_id = $season_id;
    }

    public function getNew()
    {
        return array(
            'id' => -1,
            'teacher_id' => -1,
            'class_id' => -1,
            'subject_id' => -1,
            'num' => 0,
            'free_num' => 0,
            'num_in_tt' => 0,
            'practice' => 0,
            'group_1_teacher_id' => 0,
            'group_2_teacher_id' => 0,
        );
    }

    public function populateFromArray($data)
    {
        $n = (int)\App\Helper::get($data, 'lesson_num', 0);
        return array(
            'id' => (int)\App\Helper::get($data, 'lesson_id'),
            'teacher_id' => (int)\App\Helper::get($data, 'teacher_id'),
            'class_id' => (int)\App\Helper::get($data, 'class_id'),
            'subject_id' => (int)\App\Helper::get($data, 'subject_id'),
            'num' => $n,
            'free_num' => (int)\App\Helper::get($data, 'free_lesson_num', $n),
            'num_in_tt' => (int)\App\Helper::get($data, 'num_in_tt', 0),
            'practice' => (int)\App\Helper::get($data, 'practice', 0),
            'group_1_teacher_id' => (int)\App\Helper::get($data, 'group_1_teacher_id', 0),
            'group_2_teacher_id' => (int)\App\Helper::get($data, 'group_2_teacher_id', 0),
        );
    }
    
    //  az osszes tanora szama
    public function getSum()
    {
        $rows = $this->pdo->query('SELECT COUNT(id) FROM tt_lessons WHERE season_id = ' . $this->season_id . ';')->fetchColumn(); 
        return $rows;        
    }

    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT *, ' .
            '@dbnum := (SELECT COUNT(tt_timetable.lesson_id) FROM tt_timetable WHERE tt_timetable.lesson_id = ?) AS num_in_tt, ' .
            'tt_lessons.num - @dbnum AS free_num ' .
            'FROM tt_lessons WHERE season_id = ? AND id = ?;');
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(3, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->columnCount() < 1) return null;
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getAll($order = '')
    {
//        $stmt = $this->pdo->query('SELECT *, (SELECT COUNT(lesson_id) FROM timetable WHERE lesson_id = lessons.id) AS num_in_tt FROM lessons;');
        $stmt = $this->pdo->query('SELECT *, ' .
            '@dbnum := (SELECT COUNT(lesson_id) FROM tt_timetable WHERE lesson_id = tt_lessons.id) AS num_in_tt, ' .
            'tt_lessons.num - @dbnum AS free_num ' .
            'FROM tt_lessons WHERE season_id = ' . $this->season_id .
            ' ' . $order . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }

    /**
     * Osztalyonkent a tanorak szama es az ossz oraszama.
     * @return array
     */
    public function getClassesSum()
    {
        $stmt = $this->pdo->prepare(
            'SELECT class_id, COUNT(id) as num, SUM(num) AS sum ' .
            'FROM tt_lessons WHERE season_id = ' . $this->season_id . ' GROUP BY class_id;');
        $stmt->execute();
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['class_id'] ] = $v; }
        return $result;
    }
    
    public function save($lesson)
    {
        if (!isset($lesson['id'])) return -1;
        
        if ($lesson['id'] < 0)
        {
            $lesson['free_num'] = $lesson['num'];
            return $this->insert($lesson);
        }
        else
        {
            $regi = $this->get($lesson['id']);
            if ($lesson['num'] >= $regi['num']) {
                $lesson['free_num'] += ($lesson['num'] - $regi['num']);
            }
            else {
                $lesson['free_num'] -= ($regi['num'] - $lesson['num']);
            }
            
            return $this->update($lesson);
        }
    }
    
    public function insert($lesson)
    {
        $stmt = $this->pdo->prepare('INSERT INTO tt_lessons(season_id, class_id, teacher_id, subject_id, num, practice, group_1_teacher_id, group_2_teacher_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->execute(array($this->season_id, $lesson['class_id'], $lesson['teacher_id'], $lesson['subject_id'], $lesson['num'], $lesson['practice'], $lesson['group_1_teacher_id'], $lesson['group_2_teacher_id']));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($lesson)
    {
        $stmt = $this->pdo->prepare('UPDATE tt_lessons SET class_id = ?, teacher_id = ?, subject_id = ?, num = ?, practice = ?, group_1_teacher_id = ?, group_2_teacher_id = ? WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($lesson['class_id'], $lesson['teacher_id'], $lesson['subject_id'], $lesson['num'], $lesson['practice'], $lesson['group_1_teacher_id'], $lesson['group_2_teacher_id'], $this->season_id, $lesson['id']));
        
        return $lesson['id'];
    }

    //  atalakitja a tomboket, amikor egyszerre tobb tanorat adunk hozza
    public function prepareToSaveFromPost($classid, $teachers, $subjects, $nums, $weeknums, $numberOfWeeks)
    {
        $lessons = array();
        $n = count($teachers);
        for($i = 0; $i < $n; $i++)
        {
            $oraszam = 0;
            if ($nums[$i] > 0) $oraszam = $nums[$i];
            else $oraszam = (int)($weeknums[$i] * $numberOfWeeks);
            
            $lessons[] = array(
                'id' => -1,
                'teacher_id' => $teachers[$i],
                'class_id' => $classid,
                'subject_id' => $subjects[$i],
                'num' => $oraszam
                // 'free_num' => $oraszam
            );
        }
        return $lessons;
    }
    
    //  nem vegez ellenorzest!
    public function insertMultiple($lessons)
    {
        $sql = 'INSERT INTO tt_lessons (season_id, class_id, teacher_id, subject_id, num, practice) VALUES ';
        
        $elsoAdat = true;
        
        foreach($lessons as $row)
        {
            if ($elsoAdat) { $elsoAdat = false; }
            else { $sql .= ', '; }
            
            $sql .= '(' . $this->season_id . ', ' . $row['class_id'] . ', ' . $row['teacher_id'] . ', ' . $row['subject_id'] . ', ' . $row['num'] . ', 0)';
        }
        
        $n = $this->pdo->exec($sql);
        return $n;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM tt_lessons WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($this->season_id, $id));
        return $stmt->rowCount();
    }
    
    public function saveFromJs($data)
    {
        $stat = array(
            'total' => count($data),
            'saved' => 0
        );
        
        if (empty($data)) return $stat;

        $sql = 'INSERT INTO tt_lessons (id, free_num) VALUES ';
        
        $elsoAdat = true;
        
        foreach($data as $row)
        {
            $id = (int)$row[0];
            if ($id < 0) continue;
            
            $free_num = (int)$row[1];
            if ($free_num < 0) continue;
            
            if ($elsoAdat) { $elsoAdat = false; }
            else { $sql .= ', '; }
            
            $sql .= '(' . $id . ', ' . $free_num . ')';
        }
        
        $sql .= ' ON DUPLICATE KEY UPDATE free_num = VALUES(free_num);';
        $stat['saved'] = $this->pdo->exec($sql);
        
        return $stat;
    }
    
    public function cloneSeason($oldSeasonid, $newSeasonid, $teachersLookup, $subjectsLookup, $classLookup)
    {
        $this->setSeasonId($oldSeasonid);
        $rows = $this->getAll();
        $this->setSeasonId($newSeasonid);
        $lookup = array();
        foreach($rows as $row) {
            if (isset($classLookup[ $row['class_id'] ])) $row['class_id'] = $classLookup[ $row['class_id'] ];
            if (isset($teachersLookup[ $row['teacher_id'] ])) $row['teacher_id'] = $teachersLookup[ $row['teacher_id'] ];
            if (isset($subjectsLookup[ $row['subject_id'] ])) $row['subject_id'] = $subjectsLookup[ $row['subject_id'] ];
            if ($row['group_1_teacher_id'] > 0) $row['group_1_teacher_id'] = $teachersLookup[ $row['group_1_teacher_id'] ];
            if ($row['group_2_teacher_id'] > 0) $row['group_2_teacher_id'] = $teachersLookup[ $row['group_2_teacher_id'] ];

            $newid = $this->insert($row);
            $lookup[$row['id']] = $newid;
        }

        return $lookup;
    }

    public function getValidationRules()
    {
        return array(
            'id' => v::intType()->notEmpty(),
            'teacher_id' => v::intType()->notEmpty()->positive(),
            'class_id' => v::intType()->notEmpty()->positive(),
            'subject_id' => v::intType()->notEmpty()->positive(),
            'num' => v::intType()->positive(),
            'practice' => v::intType()->not(v::negative()),
            'group_1_teacher_id' => v::intType()->not(v::negative()),
            'group_2_teacher_id' => v::intType()->not(v::negative()),
        );
    }
    
    //  egy tombot ad vissza a hibakkal
    public function validateLesson($lesson)
    {
        $validator = new \App\Validator();
        $validator->validateArray($lesson, $this->getValidationRules());
        if ($validator->hasError()) return $validator->getErrors();

        if ($lesson['group_1_teacher_id'] < 1 || $lesson['group_2_teacher_id'] < 1) {
            if ($lesson['group_1_teacher_id'] != $lesson['group_2_teacher_id']) {
                return array('group_2_teacher_id' => array('Csoportbontás esetén mindkét csoporthoz kell tanárt rendelni!'));
            }
        }
        else {
            if ($lesson['group_1_teacher_id'] == $lesson['group_2_teacher_id']) {
                return array('group_2_teacher_id' => array('A csoportbontást két különböző tanárnak kell tartani!'));            
            }
        }

        $oldLesson = $this->get($lesson['id']);
        if ($oldLesson == null) return array();

        $newNum = $lesson['num'];
        if ($newNum < $oldLesson['num']) {
            $used = $oldLesson['num'] - $oldLesson['free_num'];
            if ($newNum < $used)
            {
                //$this->session->set_flashdata('system_message', 'Az oraszam nem lehet kisebb, mint az orarendben mar elhelyezett szam!');
                return array('num' => array('Az óraszám nem lehet kisebb, mint az órarendben már elhelyezett szám!'));
            }
        }
        
        if ($oldLesson['free_num'] < $oldLesson['num'])
        {
            //  osztaly, tanar: nem valtoztathato
            if ($oldLesson['teacher_id'] != $lesson['teacher_id'])
            {
                return array('num' => array('A tanár nem változtatható meg!'));
            }
            if ($oldLesson['class_id'] != $lesson['class_id'])
            {
                return array('num' => array('Az osztály nem változtatható meg!'));
            }
        }

        return array();
    }
    
    //  tobb tanora ellenorzese
    //  egy tombot ad vissza a hibakkal
    public function validateLessons($classid, $teachers, $subjects, $nums, $weeknums)
    {
        $classid = (int)$classid;
        if ($classid < 1) return array('Ervenytelen osztaly id: ' . $classid);
        
        $n = count($teachers);
        $errors = array();
        if (count($subjects) != $n || count($nums) != $n || count($weeknums) != $n)
            return array('Nem egyeznek meg az oszlopokban az adatok!');
        
        for($i = 0; $i < $n; $i++)
        {
            $teachers[$i] = (int)$teachers[$i];
            if ($teachers[$i] < 1)
            {
                $errors[] = 'Hibas tanár, sor: ' . $i;
                continue;
            }
            
            $subjects[$i] = (int)$subjects[$i];
            if ($subjects[$i] < 1)
            {
                $errors[] = 'Hibás tantárgy, sor: ' . $i;
                continue;
            }
            
            $nums[$i] = (int)$nums[$i];
            $weeknums[$i] = (float)$weeknums[$i];
            if ($nums[$i] < 1 && $weeknums[$i] * 100  < 1)
            {
                $errors[] = 'Hibás óraszám, sor: ' . $i;
            }
        }
        
        return $errors;
    }
    
}
