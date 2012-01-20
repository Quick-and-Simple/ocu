<?php
	define('EXTEND_ME','need to extend this, cant be called within this context');
	# parent for all reporitory types (svn,git,hg.. etc)
	class REPOSITORY {
		var $_raw = false; 	# RAW_DATA() object (stdin,stdout,stderr, return_code)
		var $_argv = array();	# predefined arguments used by each revision type
		var $_url = false;	# revision url
		var $_dir = false; 	# local directory
		var $_user = false;	# revision remote user
		var $_pass = false;	# revision remote pass
		
		function __construct($url, $dir, $user = false, $pass = false) {
			if (!$url || empty($url) || !$dir || empty($dir))
				throw new Exception("argument error: " . implode(',',array_map(function($a) {return gettype($a) . "='$a'";},func_get_args())));
			$this->_raw = new RAW_DATA();
			$this->_url = $url;			
			$this->_dir = $dir;
			$this->_user = $user;
			$this->_pass = $pass;
		}
		function mkdir_if_needed() {
			if (!file_exists($this->_dir) && !@mkdir($this->_dir))
				throw new Exception("can not create non existing {$this->_dir}");
		}		
		
		
		#################################################################
		# just make sure we are extended for the use of those functions #
		#################################################################
		function diff() {
			throw new Exception(EXTEND_ME);
		}
		function update() {
			throw new Exception(EXTEND_ME);
		}
		function checkout() {
			throw new Exception(EXTEND_ME);
		}
		function remote_ls() {
			throw new Exception(EXTEND_ME);
		}
	}
?>