<?php
class mysqlClass
{
    private $errors;
    private $obj;
    
    function __construct()
    {
        $this->errors = array();
        
        $this->obj = new mysqli("localhost", "catan", "catan", "catan");
        
        if ($this->obj->connect_errno)
        {
            $this->errors[] = 'new mysqli faild.';
        }
    }
    
    public function getErrors(&$errors)
    {
        $errors = $this->errors;
        return count($this->errors);
    }
    
    public function escape($str)
    {
        return $this->obj->escape_string($str);
    }
    
    public function query($sql)
    {
        $results = $this->obj->query($sql);
        if (!$results) {
            $this->errors[] = $this->obj->error;
        }
        return $results;
    }
}

$__connection = new mysqlClass();
