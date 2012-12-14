<?
class ErrorHandler { 
	
	private static $instance;
	public static function getInstance(){
		return isset(self::$instance) ? self::$instance : self::$instance = new self();
	}
	
	public static function logInfo($str) { 
		if (isDev()) 
			debug($str);
	}  

	public static function logDebug($str) { 
		if (isDev()) 
			debug($str);
	}  

	public static function logError($str) { 
		debug($str);
	}  


}

?>