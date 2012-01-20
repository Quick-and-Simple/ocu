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
				@list($user,$pass) = @explode(":",$l);
				if ($user == $_SERVER{'PHP_AUTH_USER'}) {
					if (apr1($_SERVER{'PHP_AUTH_PW'},preg_replace('/\$apr1\$(........).*/',"\\1",$pass)) == $pass) {
						return true;
					}
				}
			}		
			M_AUTH::require_auth();
		}
		static function must_have_access($where) {
			if (!M_AUTH::has_access($where)) {
				echo 'no access';
				exit(0);
			}
		}
		static function has_access($where) {
			global $ACCESS_LIST;
			return (@$ACCESS_LIST{$where}{$_SERVER{'PHP_AUTH_USER'}} == 1);			
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
	function apr1($plain,$salt) {
	      $length = strlen($plain);
	      $context = $plain . '$apr1$' . $salt;
	      $binary = pack('H32', md5($plain . $salt . $plain));
	      for($i = $length; $i > 0; $i -= 16) {
	        $context .= substr($binary, 0, min(16, $i));
	      }
	      for($i = $length; $i > 0; $i >>= 1) {
	        $context .= ($i & 1) ? chr(0) : $plain{0};
	      }
	      $binary = pack('H32', md5($context));
	      for($i = 0; $i < 1000; $i++) {
	        $new = ($i & 1) ? $plain : $binary;
	        if ($i % 3) $new .= $salt;
	        if ($i % 7) $new .= $plain;
	        $new .= ($i & 1) ? $binary : $plain;
	        $binary = pack('H32', md5($new));
	      }
	      $q = '';
	      for ($i = 0; $i < 5; $i++) {
	        $k = $i + 6;
	        $j = $i + 12;
	        if ($j == 16) $j = 5;
	        $q = $binary{$i} . $binary{$k} . $binary{$j} . $q;
	      }
	     $q = chr(0) . chr(0) . $binary{11} . $q;
	     $q = strtr(strrev(substr(base64_encode($q), 2)),
	                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
	                './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
	     return "\$apr1\$$salt\$$q";
	}
