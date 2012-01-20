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
					if (apr1($_SERVER{'PHP_AUTH_PW'},$pass) == $pass) {
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
	/* from USVN http://www.usvn.info */
        function apr1($plainpasswd, $salt = null) {
                if ($salt === null) {
                        $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
                } else {
                        if (substr($salt, 0, 6) == '$apr1$') {
                                $salt = substr($salt, 6, 8);
                        } else {
                                $salt = substr($salt, 0, 8);
                        }
                }
                $len = strlen($plainpasswd);
                $text = $plainpasswd.'$apr1$'.$salt;
                $bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
                for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
                for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
                $bin = pack("H32", md5($text));
                for($i = 0; $i < 1000; $i++) {
                        $new = ($i & 1) ? $plainpasswd : $bin;
                        if ($i % 3) $new .= $salt;
                        if ($i % 7) $new .= $plainpasswd;
                        $new .= ($i & 1) ? $bin : $plainpasswd;
                        $bin = pack("H32", md5($new));
                }
                $tmp = "";
                for ($i = 0; $i < 5; $i++) {
                        $k = $i + 6;
                        $j = $i + 12;
                        if ($j == 16) $j = 5;
                        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
                }
                $tmp = chr(0).chr(0).$bin[11].$tmp;
                $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
                "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
                "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
                return "$"."apr1"."$".$salt."$".$tmp;
        }	
?>