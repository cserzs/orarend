<?php
namespace App;

use Respect\Validation\Validator as v;

class Teacher
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
            'name' => '',
            'short_name' => '',
            'day1' => 0,
            'day2' => 0,
            'day3' => 0,
            'day4' => 0,
            'day5' => 0
        );
    }
    
    public function populateFromArray($data)
    {
        return array(
            'id' => (int)\App\Helper::get($data, 'id'),
            'name' => \App\Helper::get($data, 'name', ''),
            'short_name' => \App\Helper::get($data, 'short_name', ''),
            'day1' => (int)\App\Helper::get($data, 'day1', 0),
            'day2' => (int)\App\Helper::get($data, 'day2', 0),
            'day3' => (int)\App\Helper::get($data, 'day3', 0),
            'day4' => (int)\App\Helper::get($data, 'day4', 0),
            'day5' => (int)\App\Helper::get($data, 'day5', 0),
        );
    }
    
    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tt_teachers WHERE season_id = ? AND id = ?;');
        $stmt->bindValue(1, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->columnCount() < 1) return null;
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getAll($order = '')
    {
        $stmt = $this->pdo->query('SELECT * FROM tt_teachers WHERE season_id = ' . $this->season_id  . ' ' . $order . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }
    
    public function toApi($order = '')
    {
        $stmt = $this->pdo->query('SELECT * FROM tt_teachers WHERE season_id = ' . $this->season_id . ' ORDER BY name;');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[] = array(
            'id' => (int)$v['id'],
            'name' => $v['name'],
            'short_name' => $v['short_name'],
            'day1' => (int)$v['day1'],
            'day2' => (int)$v['day2'],
            'day3' => (int)$v['day3'],
            'day4' => (int)$v['day4'],
            'day5' => (int)$v['day5']
            ); }
        return $result;
    }
    
    public function getAllWithLessonsnum($order = '')
    {
        $stmt = $this->pdo->query(
                'SELECT tt_teachers.id, tt_teachers.name, tt_teachers.short_name, ' .
                '(SELECT COUNT(tt_lessons.id) FROM tt_lessons WHERE season_id = ' . $this->season_id . ' AND tt_lessons.teacher_id = tt_teachers.id) AS num ' . 
                'FROM tt_teachers ' .
                'WHERE season_id = ' . $this->season_id . ' ' . $order . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }
    
    
    public function save($teacher)
    {
        if (!isset($teacher['id'])) return;
        
        if ($teacher['id'] < 0)
        {
            return $this->insert($teacher);
        }
        else
        {
            return $this->update($teacher);
        }
        
    }
    
    public function insert($teacher)
    {
        $stmt = $this->pdo->prepare('INSERT INTO tt_teachers(season_id, name, short_name, day1, day2, day3, day4, day5) VALUES(?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->execute(array($this->season_id, $teacher['name'], $teacher['short_name'], $teacher['day1'], $teacher['day2'], $teacher['day3'], $teacher['day4'], $teacher['day5']));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($teacher)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tt_teachers SET name = ?, short_name = ?, day1 = ?, day2 = ?, day3 = ?, day4 = ?, day5 = ? WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($teacher['name'], $teacher['short_name'], $teacher['day1'], $teacher['day2'], $teacher['day3'], $teacher['day4'], $teacher['day5'], $this->season_id, $teacher['id']));
        
        return $teacher['id'];
    }
    
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM tt_teachers WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($this->season_id, $id));
        return $stmt->rowCount();
    }
    
    /**
     * Hany oraja van?
     * @param int $id tanargy id
     */
    public function getLessonsCount($id)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(id) FROM tt_lessons WHERE season_id = ? AND teacher_id = ?;');
        $stmt->bindValue(1, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn(); 
    }
    
    public function cloneSeason($oldSeasonId, $newSeasonId)
    {
        $this->setSeasonId($oldSeasonId);
        $teachers = $this->getAll();
        $this->setSeasonId($newSeasonId);
        $lookup = array();
        foreach($teachers as $teacher) {
            $newId = $this->insert($teacher);
            $lookup[$teacher['id']] = $newId;
        }
        return $lookup;
    }

    public function getValidationRules()
    {
        return array(
            'id' => v::intType()->notEmpty(),
            'name' => v::notEmpty(),
            'short_name' => v::notEmpty(),
            'day1' => v::intType()->not(v::negative()),
            'day2' => v::intType()->not(v::negative()),
            'day3' => v::intType()->not(v::negative()),
            'day4' => v::intType()->not(v::negative()),
            'day5' => v::intType()->not(v::negative())
        );
    }

}
