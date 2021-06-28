<?php
namespace App;

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    public $errors = array();

    public function validateArray($data, array $rules)
    {
        $this->errors = array();
        
        foreach ($rules as $field => $rule)
        {
            if (!isset($data[$field]))
            {
                $this->errors[$field][] = "Required field: " . $field;
                continue;
            }
            
            try
            {
                $rule->setName($field)->assert($data[$field]);
            } catch (NestedValidationException $e)
            {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this->errors;
    }

    public function hasError($field = null)
    {
        if ($field == null) return !empty($this->errors);
        return isset($this->errors[$field]);
    }
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getErrorsFor($field)
    {
        if ( !isset($this->errors[$field])) return array();
        return $this->errors[$field];
    }
}