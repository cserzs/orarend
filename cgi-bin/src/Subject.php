<?php
namespace App;

use Respect\Validation\Validator as v;

class Subject
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
            'short_name' => ''
        );
    }
    
    public function populateFromArray($data)
    {
        return array(
            'id' => (int)\App\Helper::get($data, 'id'),
            'name' => \App\Helper::get($data, 'name', ''),
            'short_name' => \App\Helper::get($data, 'short_name', '')
        );
    }
    
    public function get($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tt_subjects WHERE season_id = ? AND id = ?;');
        $stmt->bindValue(1, $this->season_id, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->columnCount() < 1) return null;
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getAll($order = '')
    {
        $stmt = $this->pdo->query('SELECT * FROM tt_subjects WHERE season_id = ' . $this->season_id . ' ' . $order . ';');
        
        $temp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        foreach($temp as $v) { $result[ $v['id'] ] = $v; }
        return $result;
    }
    
    public function save($subj)
    {
        if (!isset($subj['id'])) return;
        
        if ($subj['id'] < 0)
        {
            return $this->insert($subj);
        }
        else
        {
            return $this->update($subj);
        }
        
    }
    
    public function insert($subj)
    {
        $stmt = $this->pdo->prepare('INSERT INTO tt_subjects(season_id, name, short_name) VALUES(?, ?, ?);');
        $stmt->execute(array($this->season_id, $subj['name'], $subj['short_name']));
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($subj)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tt_subjects SET name = ?, short_name = ? WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($subj['name'], $subj['short_name'], $this->season_id, $subj['id']));
        
        return $subj['id'];
    }
    
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM tt_subjects WHERE season_id = ? AND id = ?;');
        $stmt->execute(array($this->season_id, $id));
        return $stmt->rowCount();
    }
    
    /**
     * Hany oranal szerepel a tantargy?
     * @param int $id tanargy id
     */
    public function getLessonsCount($id)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(id) FROM tt_lessons WHERE season_id = ? AND subject_id = ?;');
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
            'name' => v::notEmpty(),
            'short_name' => v::optional(v::notEmpty())
        );
    }
    
}
