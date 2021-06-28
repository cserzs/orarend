<?php
namespace App;

use Respect\Validation\Validator as v;

class Schoolclass
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
            'id' => (int)\App\Helper::get($data, 'class_id'),
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
        $stmt = $this->pdo->prepare('SELECT * FROM tt_schoolclasses WHERE season_id = ? AND id = ?;');
        $stmt->bindValue(1, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() < 1) return null;
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getAll($orderBy = '')
    {
        $stmt = $this->pdo->query('SELECT * FROM tt_schoolclasses WHERE season_id = ' . $this->season_id . ' ' . $orderBy . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }
    
    /**
     * Nincs kulcs a tombben, csak id es rovid nev.
     * @return type
     */
    public function getAllToJs()
    {
        $stmt = $this->pdo->query('SELECT id, short_name FROM tt_schoolclasses WHERE season_id = ' . $this->season_id . ' ORDER BY short_name;');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) {
            $result[] = array(
                'id' => $v['id'],
                'name' => $v['short_name']
            );
        }
        return json_encode($result);
        
    }
    
    public function toApi()
    {
        $stmt = $this->pdo->query('SELECT id, short_name FROM tt_schoolclasses WHERE season_id = ' . $this->season_id . ' ORDER BY short_name;');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) {
            $result[] = array(
                'id' => (int)$v['id'],
                'name' => $v['short_name']
            );
        }
        return $result;
    }
    
    
    public function save($class)
    {
        if (!isset($class['id'])) return;
        
        if ($class['id'] < 0)
        {
            return $this->insert($class);
        }
        else
        {
            return $this->update($class);
        }
        
    }
    
    public function insert($class)
    {
        $stmt = $this->pdo->prepare('INSERT INTO tt_schoolclasses(season_id, name, short_name, day1, day2, day3, day4, day5) VALUES(?, ?, ?, ?, ?, ?, ?, ?);');
        $stmt->execute(array($this->season_id, $class['name'], $class['short_name'], $class['day1'], $class['day2'], $class['day3'], $class['day4'], $class['day5']));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($class)
    {
        $stmt = $this->pdo->prepare('UPDATE tt_schoolclasses SET name = ?, short_name = ?, day1 = ?, day2 = ?, day3 = ?, day4 = ?, day5 = ? WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($class['name'], $class['short_name'], $class['day1'], $class['day2'], $class['day3'], $class['day4'], $class['day5'], $this->season_id, $class['id']));
        
        return $stmt->rowCount();
    }
    
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM tt_schoolclasses WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($this->season_id, $id));
        return $stmt->rowCount();
    }

    /**
     * Hany oraja van az osztalynak?
     * @param int $id
     */
    public function getLessonsCount($id)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(id) FROM tt_lessons WHERE season_id = ? AND class_id = ?;');
        $stmt->bindValue(1, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn(); 
    }
    
    public function cloneSeason($oldSeasonId, $newSeasonId)
    {
        $this->setSeasonId($oldSeasonId);
        $rows = $this->getAll();
        $this->setSeasonId($newSeasonId);
        $lookup = array();
        foreach($rows as $row) {
            $newId = $this->insert($row);
            $lookup[$row['id']] = $newId;
        }
        return $lookup;
    }

    public function getValidationRules()
    {
        return array(
            'id' => v::intType()->notEmpty(),
            'name' => v::notEmpty()->noWhitespace(),
            'short_name' => v::notEmpty()->noWhitespace(),
            'day1' => v::intType()->not(v::negative()),
            'day2' => v::intType()->not(v::negative()),
            'day3' => v::intType()->not(v::negative()),
            'day4' => v::intType()->not(v::negative()),
            'day5' => v::intType()->not(v::negative())
        );
    }
}
