<?php
	define('ROOT', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR));
	define('ROOT_LANG',ROOT . DIRECTORY_SEPARATOR  . 'lang' . DIRECTORY_SEPARATOR);	
	define('ROOT_MVC',ROOT . DIRECTORY_SEPARATOR  . 'mvc' . DIRECTORY_SEPARATOR);
	define('ROOT_MODEL', ROOT_MVC . DIRECTORY_SEPARATOR  . 'm' . DIRECTORY_SEPARATOR);	
	define('ROOT_VIEW', ROOT_MVC . DIRECTORY_SEPARATOR  . 'v' . DIRECTORY_SEPARATOR);
	define('ROOT_CONTROLLER', ROOT_MVC . DIRECTORY_SEPARATOR  . 'c' . DIRECTORY_SEPARATOR);
	define('ROOT_LIB',ROOT . DIRECTORY_SEPARATOR  . 'lib' . DIRECTORY_SEPARATOR);
	define('CONFIG_FILE',ROOT . DIRECTORY_SEPARATOR . 'config.php');
	define('DEBUG',true);
	require_once(ROOT_LIB . 'ts.php');
	require_once(ROOT_LIB . 'proc.php');
	require_once(ROOT_LIB . 'repository.php');
	require_once(ROOT_LIB . 'svn.php');
	require_once(ROOT_MVC . 'resty.php');
	require_once(ROOT_MODEL .'m_repo.php');		
	require_once(ROOT_CONTROLLER . 'controller.php');	
	require_once(ROOT_CONTROLLER . 'c_diff.php');
	require_once(ROOT_LANG . 'bg.php');
	$DEFAULT_CONTROLLER = 'C_DIFF';
	$DEFAULT_METHOD = 'default';


	define('WORKING_DIRECTORY','/var/www-projects');
	$SVN_PATH = array('/SVN','/NSVN/svn');
	$c = @$_GET{'c'} ? @$_GET{'c'} : $DEFAULT_CONTROLLER;
	$m = @$_GET{'m'} ? @$_GET{'m'} : $DEFAULT_METHOD;
	$id = @$_GET{'id'} ? @$_GET{'id'} : 0;

	#so we dont have to require from the user to put .htaccess file
	$url = "$c/$m/$id";
	$r = new RESTY($url);
	$r->render();
?>