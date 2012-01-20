<?php
	class C_DIFF extends CONTROLLER {
		var $repo = false;
		var $must_checkout = false;
		function repository($id) {
			$this->repo =  M_REPO::get($id);
			if (!$this->repo) {
				throw new Exception("cant get $id");
			}
		}
		function GET_logout() {
			
		}
		function GET_default() {
			$first = array_shift(M_REPO::repository_list());
			$this->next_url('diff',$first[1]);
		}		
		function GET_diff($id) {
			if (!$id)
				not_foundx();
			M_AUTH::must_have_access($id);							
			$this->diff_result = false;
			try {
				$this->repository($id);				
				$this->diff_result = $this->repo->diff();
			} catch (Exception $e) {
				$error = $this->flash_error($e);
				if (preg_match('/not a working/',$error)) {
					$this->must_checkout = true;
				}
			}			
		}
		function POST_update($id) {
			M_AUTH::must_have_access($id);
			$path = @$_POST{'path'};
			if (empty($path)) {
				$path = array();
			}
			try {
				$this->repository($id);
				$this->repo->update($path);
			} catch (Exception $e) {
				$this->flash_error();
			}
			$this->next_url('diff',$id);			
		}
		function GET_checkout($id) {
			M_AUTH::must_have_access($id);			
			try {
				$this->repository($id);		
				$this->repo->checkout();
			} catch (Exception $e) {
				$this->flash_error($e);
			}
			$this->next_url('diff',$id);
		}
		function repo_error_matches($s) {
			return preg_match("/".$s."/",$this->repo->_raw->se);
		}
		function flash_error($e) {
			$error = "";
			if ($e) {
				$this->flash("exception: " . $e->getMessage());				
			}
			if ($this->repo) {
				$error = $this->repo->_raw->se;
				$this->flash("command error: $error");
			}
			return $error;
		}
	}
?>