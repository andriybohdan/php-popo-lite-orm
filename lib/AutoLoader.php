<?php

class AutoLoader {
	
	private static $instance;
	public static function getInstance(){
		return isset(self::$instance) ? self::$instance : self::$instance = new self();
	}

	protected function __construct() {
	}
	
	private $folders = array();
	private $classesFolders = array();
	
	protected function findClasses($folder) {
		
	}
	
	public function register($folder,$classes = false) {
		if ($classes===false) { 
			$classes = $this->findClasses($folder);
		}
		$index =  count($this->folders);
		$this->folders[$index] = $folder; 
		foreach ($classes as $class) { 
			$this->classesFolders[$class] = $index;
		}
	}
	
	public function load($class) { 
		if (isset($this->classesFolders[$class])) {
			$folderIndex =  $this->classesFolders[$class];
			$folder = $this->folders[$folderIndex];
			if ($folder !== null && $folder !== "") {
				$folder .= '/';
			}
			include(dirname(__FILE__).'/'.$folder . $class.'.php');
		} else if (file_exists(dirname(__FILE__).'/models/'.$class.'.php')) { 
			include(dirname(__FILE__).'/models/'.$class.'.php');
		} else { 
			return null;
		}
	}	

}