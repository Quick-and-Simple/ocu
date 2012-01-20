<?php
	if (!defined('ROOT'))  exit(0);
	define('NO_VIEW','no_view');
	function not_foundx($e = false) {
		header('HTTP/1.1 404 Not Found');
		if (defined('DEBUG') && DEBUG) {
			echo '<pre>';
			if ($e) 
				var_dump($e);			
			
			debug_print_backtrace();
			echo '</pre>';
		}
		echo '<center>not found</center>';
		exit(0);
	}
	function redirect($where) {
		header("Location: $where");
		exit(0);
	}
	

	class RESTY {
		var $controller;
		function sanitize($m) {
			return preg_replace('/[^A-Za-z0-9-_]/','',$m);
		}
		function __construct($url) {
			if (!isset($_SERVER{'REQUEST_METHOD'}) || !preg_match('/^(POST|GET|PUT|DELETE)$/',$_SERVER{'REQUEST_METHOD'}))
				not_foundx();
			
			$exploded = explode('/',$url);
			$c = @$this->sanitize(array_shift($exploded));
			$m = @$this->sanitize(array_shift($exploded));
			$id = @implode("/",$exploded);
			$controller_name = strtoupper((empty($c) ? DEFAULT_CONTROLLER : $c));
			$m = (empty($m) ? 'default' : strtolower($m));
			$method = strtoupper($_SERVER{'REQUEST_METHOD'}) . '_' . $m;
			if (!class_exists($controller_name))
				not_foundx();
			$this->controller = new $controller_name($_SERVER{'REQUEST_URI'});
			if (!is_callable(array($this->controller,$method)))
				not_foundx();
			#default calls:
			#	GET_default
			#	POST_default
			$this->controller->_id = $id;
			$this->view = $this->controller->$method($id);
			if (empty($this->view)) 
				$this->view = $method;	
		}
		function render() {
			if ($this->view == NO_VIEW)
				return;
				
			try {
				$t = new TS($this->view,$this->controller,(defined('DEBUG') ? DEBUG : false));
				$t->render();
			} catch (Exception $e) {
				not_foundx($e);
			}
		}
	}
?>