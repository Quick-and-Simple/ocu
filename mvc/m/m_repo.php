<?php
	class M_REPO {
		function get($id) {
			$list = M_REPO::repository_list();
			global $SVN_PATH;			
			foreach ($list as $d) {
				list($root,$entry) = $d;
				if ($entry == $id) {
					return new SVN("file://$root/$entry",WORKING_DIRECTORY . DIRECTORY_SEPARATOR . $entry);
				}
			}
			return false;
		}
		static function repository_list() {
			global $SVN_PATH;
			$r = array();
			foreach ($SVN_PATH as $s) {
				if ($handle = @opendir($s)) {
					while (false !== ($entry = readdir($handle))) {
						$f = $s . DIRECTORY_SEPARATOR . $entry;
						$db = $f . DIRECTORY_SEPARATOR . "db";
						if (is_dir($f) && is_dir($db) && !preg_match('/^\./',$entry) && M_AUTH::has_access($entry)) {
							array_push($r,array($s,$entry));
						}
					}
					@closedir($handle);
				}                               
			}
			sort($r);
			return $r;
		}
	}

?>