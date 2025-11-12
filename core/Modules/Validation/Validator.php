<?php
namespace Nexus\Modules\Validation;
// app/Core/Validator.php
class Validator {
    private $data;
    private $rules;
    private $errors = [];
    
    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public function validate() {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            
            foreach ($rules as $rule) {
                $this->validateRule($field, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    private function validateRule($field, $rule) {
        $value = $this->data[$field] ?? null;
        
        if (strpos($rule, ':') !== false) {
            list($ruleName, $param) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'The ' . $field . ' field is required.');
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'The ' . $field . ' must be a valid email address.');
                }
                break;
            case 'min':
                if (strlen($value) < $param) {
                    $this->addError($field, 'The ' . $field . ' must be at least ' . $param . ' characters.');
                }
                break;
            case 'max':
                if (strlen($value) > $param) {
                    $this->addError($field, 'The ' . $field . ' may not be greater than ' . $param . ' characters.');
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, 'The ' . $field . ' must be a number.');
                }
                break;
        }
    }
    
    private function addError($field, $message) {
        $this->errors[$field][] = $message;
    }
    
    public function errors() {
        return $this->errors;
    }
    
    public function first($field) {
        return $this->errors[$field][0] ?? null;
    }
}