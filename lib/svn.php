<?php
	define('SVN_BINARY',"svn");
	define('SVN_RC_SUCCESS',0);
	class SVN extends REPOSITORY {
		function __construct($url, $dir, $user = false, $pass = false) {
			parent::__construct($url,$dir,$user,$pass);
			$this->mkdir_if_needed();
			# WARNING: insecure since user and password are given as argument!
			$this->_argv = array('--non-interactive','--trust-server-cert','--no-auth-cache','--username',$user,'--password',$pass);
		}
		# throws exception on fail
		# returns hash with keys:
		#	'files' => array of files which are modified
		#	'raw'   => raw stdout output of the diff command (currently unparsed)
		function diff() {
			$rc = $this->srun('diff','--old',$this->_dir,'--new',$this->_url);
			// $rc = $this->srun('diff','--new',$this->_dir,'--old',$this->_url);
			
			$files = array();
			
			foreach (explode("\n",$this->_raw->so) as $line) {
				if (preg_match('/^Index: (.*)?/',$line,$matches)) 
					array_push($files,$matches[1]);
			}
			return array('files' => $files, 'raw' => $this->_raw->so);
		}
		
		# update() : takes array of paths to be updated as argument.
		# throws exception on fail
		# returns array of updated files on success
		function update($what = array()) {
			$updated = array();
			foreach ($what as $d) {
				$this->srun('update',$d);
				array_push($updated,$d);
			}
			return $updated;
		}

		# checkout() : takes void argument (checkouts _dir);
		# throws exception on fail
		# returns trus on success
		function checkout() {
			$this->srun('checkout',$this->_url,$this->_dir);
			return true;
		}
		
		# remote_ls() : takes void arguments (lists _dir);
		# throws exception on fail
		# returns array of files on success
		# important: this is recursive list
		function remote_ls() {
			$rc = $this->srun('ls','--depth','infinity',$this->_url);
			return explode("\n",$this->_raw->so);
		}
		
		# srun() - takes variable arguent count
		# all of the arguments are combined in array and merged with _argv, then passed to _raw->run()
		# returns nothing
		# important: throws exception on fail
		function srun() {
			if ($this->_raw->run(SVN_BINARY,array_merge($this->_argv,func_get_args()),$this->_dir) != SVN_RC_SUCCESS)
				throw new Exception('failed to get data');
		}
	}
?>