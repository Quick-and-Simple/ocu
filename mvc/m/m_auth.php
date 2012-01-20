<?php
	class M_AUTH {
		static function require_auth() {
		         header('WWW-Authenticate: Basic realm="Private"');
		         header('HTTP/1.0 401 Unauthorized');
		         echo 'Authorization Required.';
		         exit(0);		
		}
		static function validate() {
			if (!isset($_SERVER{'PHP_AUTH_USER'})) {
				M_AUTH::require_auth();
			}
			$d = @file_get_contents(USER_FILE);
			$lines = @explode("\n", $d);
			foreach ($lines as $l) {
				list($user,$pass) = explode(":",$line);
				if ($user == $_SERVER{'PHP_AUTH_USER'}) 
					if (crypt($_SERVER{'PHP_AUTH_PW'}, substr($pass, 0, 2)) != $pass)
						M_AUTH::require_auth();

			}			
		}
		static function must_have_access($where) {
			if (!M_AUTH::has_access($where)) {
				echo 'no access';
				exit(0);
			}
		}
		static function has_access($where) {
			global $ACCESS_LIST;
			return (@$ACCESS_LIST{$where}{$_SERVER{'PHP_AUTH_USER'} == 1);			
		}
		static function access_list() {
			$d = @file_get_contents(GROUP_FILE);
			$r = array();
			if ($d) {
		  		if (preg_match("/\[groups\](.*)\[/msU",$d,$m)) {
					$e = explode("\n",$m[1]);
					foreach ($e as $line) {
						if (!preg_match('/^(\s+)?\w/',$line))
							continue;
						list($g,$users) = preg_split('/(\s+)?=(\s+)?/',$line);
						foreach (preg_split('/(\s+)?,(\s+)?/',$users) as $u) {
							@$r{$g}{$u} = 1;
						}
					}
				}		
			}
			return $r;
		}
	}
?>