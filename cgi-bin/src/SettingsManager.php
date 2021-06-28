<?php
namespace App;

class SettingsManager {
    private static $instance = null;

    private $pdo;

    public static function getInstance($pdo) {
        if (self::$instance == null) {
            self::$instance = new SettingsManager($pdo);
        }
        return self::$instance;
    }

    private function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function get($key) {
        $stmt = $this->pdo->prepare('SELECT id, value FROM tt_settings WHERE id = ?;');
        $stmt->bindValue(1, $key, \PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->columnCount() < 1) return null;
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return unserialize($row['value']);
    }

    public function getMultiple($arrayOfKeys) {
        $result = array();
        for($i = 0; $i < count($arrayOfKeys); $i++) {
            $result[$arrayOfKeys[$i]] = $this->get($arrayOfKeys[$i]);
        }
        return $result;
    }

    public function save($key, $value) {
        $v = $this->get($key);
        $data = serialize($value);
        if ($v === null) {
            $stmt = $this->pdo->prepare('INSERT INTO tt_settings(id, value) VALUES(?, ?);');
            $stmt->execute(array($key, $data));
        }
        else {
            $stmt = $this->pdo->prepare('UPDATE tt_settings SET value = ? WHERE id = ?;');
            $stmt->execute(array($data, $key));
        }
    }

    /*
        array:
            key => value
    */
    public function saveMultiple($arr) {
        foreach($arr as $key => $value) {
            $this->save($key, $value);
        }
    }

    public function remove($key) {
        $stmt = $this->pdo->prepare('DELETE FROM tt_settings WHERE id = ?;');
        $stmt->execute(array($key));
    }

}