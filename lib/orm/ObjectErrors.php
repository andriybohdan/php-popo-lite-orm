<?php

class ObjectErrors {

	const GL_ERRORS = '__GLOBAL__';
	
	private $errors = array();
	private $object = null;
	
	public function __construct($object) { 
		$this->object = $object;
		if ($this->object)
			$this->object->objectErrors = $this;
	}
	
	public function isOK() { 
		$this->compact();
		return empty($this->errors);
	}
	
	public function isError() { 
		$this->compact();
		return !empty($this->errors);
	}
	
	public function valid($field = false) { 
		return !$this->invalid($field);
	}
	
	public function invalid($field = false) { 
		if ($field==false) 
			$field = self::GL_ERRORS; 
		return isset($this->errors[$field]) && !empty($this->errors[$field]);
	}
	
	public function get($field = false) { 
		if ($field==false) 
			$field = self::GL_ERRORS; 
		if (isset($this->errors[$field]) && !empty($this->errors[$field])) 
			return $this->errors[$field];
		else 
			return false;
	}
	
	public function add($message,$field = false) {
		if ($field==false) 
			$field = self::GL_ERRORS; 
				if (!isset($this->errors[$field]))
			$this->errors[$field] = array();
		 $this->errors[$field][]=$message;
	}
	
	public function getAll() {
		$all = array(); 
		if (!empty($this->errors)) { 
			foreach ($this->errors as $key => $errors) { 
				if (!empty($errors)) 
					$all = array_merge($all,$errors);
			}
		}
		return $all;
	}
	
	public function getFieldErrors() { 
		$all = array(); 
		if (!empty($this->errors)) { 
			foreach ($this->errors as $key => $errors) { 
				if ($key!=self::GL_ERRORS && !empty($errors)) 
					$all[$key] = $errors;
			}
		}
		return $all;
	}
		
	protected function compact() { 
		if (!empty($this->errors)) {
			$unsets = array(); 
			foreach ($this->errors as $key=>$errors) { 
				if (empty($errors)) { 
					$unsets[]=$key;
				}
			}
			if (!empty($unsets)) foreach ($unsets as $unset) { 
				unset($this->errors[$unset]);
			}
		}
	}	
	
}