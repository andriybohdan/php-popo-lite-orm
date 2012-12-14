<?
define('CHARSET','UTF-8');
mb_regex_encoding(CHARSET);
mb_internal_encoding(CHARSET);

include(dirname(__FILE__).'/AutoLoader.php');

AutoLoader::getInstance()->register('orm',array(
	'BehaviorEvent','ObjectBehavior','ObjectORM','ObjectErrors',
	'ValidateException','TimestampBehavior','UpdateEvent',
	'AutoIncrementBehavior','Pager','NextPrevPager','ErrorHandler',
	'DBException'
	));


function __autoload($class) {
	AutoLoader::getInstance()->load($class);
}

function isDev() { 
	return isset($GLOBALS['dev_mode']) && $GLOBALS['dev_mode'] == 1;
}

function isCLI () {
	return (php_sapi_name() == 'cli');
}

function debug() { 
	
	if (!isDev())
		return;
	if (!isCLI()) {
		echo "<pre>";
		for ($i = 0;$i < func_num_args();$i++) {
		    echo htmlentities(print_r(func_get_arg($i),true),ENT_COMPAT,CHARSET);
			if ($i<func_num_args()-1)
				echo "\n";
		}
		echo "</pre>";	
	} else { 
		for ($i = 0;$i < func_num_args();$i++) {
		    echo print_r(func_get_arg($i),true);
			echo "\n";
		}
	}
}
function info() { 
	if (!isCLI()) {
		echo "<pre>";
		for ($i = 0;$i < func_num_args();$i++) {
		    echo htmlentities(print_r(func_get_arg($i),true),ENT_COMPAT,CHARSET);
			if ($i<func_num_args()-1)
				echo "\n";
		}
		echo "</pre>";	
	} else { 
		for ($i = 0;$i < func_num_args();$i++) {
		    echo print_r(func_get_arg($i),true);
			echo "\n";
		}
	}
}

function init_DB_Conn() {
	global $db_handle;
	if ( ! $db_handle ) {
		global $mysql_conf;
        $db_handle = mysql_connect($mysql_conf['host'],$mysql_conf['user'],$mysql_conf['password']);
        mysql_query("SET NAMES 'utf8'",$db_handle);
		mysql_select_db($mysql_conf['database'],$db_handle);
    }
    return $db_handle;
}


?>
