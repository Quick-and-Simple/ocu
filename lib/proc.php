<?php
#	if (!defined('ROOT'))  exit(0);
	class RAW_DATA {
		var $si = false;
		var $so = false;
		var $se = false;
		var $rc = 0;
		var $executed_commands = array();
		function run($cmd,$args = array(),$cwd = NULL,$stdin = false) {
			$cmd = escapeshellcmd($cmd) . " " . implode(array_map("escapeshellarg",$args)," ");
			array_push($this->executed_commands,$cmd);
			$descspec = array(
				0 => array('pipe', 'r'),
				1 => array('pipe', 'w'),
				2 => array('pipe', 'w'),
			);
			$pipes = array();
			$res = proc_open($cmd, $descspec, $pipes,$cwd);
			if (!$res)
				throw new Exception('failed to create process');
	
			if ($stdin) 
				fwrite($pipes[0],$stdin);
			
			$this->so = stream_get_contents($pipes[1]);
			$this->se = stream_get_contents($pipes[2]);
			$this->si = $stdin;
			foreach ($pipes as $pipe) 
				fclose($pipe);
			
			return proc_close($res);
		}
		function test() {
			$test_command = (substr(PHP_OS, 0, 3) == 'WIN' ? 'dir' : 'ls');
			if ($this->run($test_command) != 0) # seems that we cant execute
				return false;
			return true;
		}
	}
?>
