<?php
	function l($w) {
		global $_LANGUAGE;
		return @$_LANGUAGE{$w};
	}

	class CONTROLLER {
		var $_url;
		var $_list;
		function __construct($url) {
			global $LIST;
			$this->_url = $url;
			$this->_list = $LIST;
		}
		function s($w) {
			return @$_SESSION{$w};
		}
		function ss($w,$v) {
			$_SESSION{$w} = $v;
		}
		function gen_url($controller,$method,$id) {
			if (!preg_match('/^c_/i',$controller))
				$controller = "c_$controller";
			$controller = strtoupper($controller);
			return "?c=$controller&m=$method&id=$id";
		}
		function self_url($method,$id) {
			return $this->gen_url(get_class($this),$method,$id);
		}
		function self_id_url($method) {
			return $this->self_url($method,$this->_id);
		}
		function next_url($method,$id) {
			redirect($this->self_url($method,$id));
		}
		function next_controller($controller,$method,$id) {
			redirect($this->gen_url($controller,$method,$id));
		}
		function flash_exists($level) {
			return (isset($_SESSION{"flash_$level"}));
		}
		function flash($what,$level = 'global') {
			if (!$this->flash_exists($level)) {
				$_SESSION{"flash_$level"} = array();
			}
			array_push($_SESSION{"flash_$level"},$what);
		}
		function flash_l($what,$level = 'global') {
			$this->flash($this->_lang[$what],$level);			
		}
		function local_flash($what) {
			$this->flash($what,get_class($this));
		}
		function local_flash_l($what) {
			$this->local_flash($this->_lang[$what]);
		}
		function get_local_flash_and_clear() {
			return $this->get_flash_and_clear(get_class($this));
		}
		function get_flash_and_clear($level = 'global') {
			$a = array();
			if($this->flash_exists($level)) {
				$a = $_SESSION{"flash_$level"};
				unset($_SESSION{"flash_$level"});
			}
			return $a;
		}
		function has_flash($level = 'global') {
			return isset($_SESSION{"flash_$level"});
		}
	}

?>
