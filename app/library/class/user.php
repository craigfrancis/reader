<?php

	class user extends user_base {

		//--------------------------------------------------
		// Variables

			protected $identification_type = 'username';

		//--------------------------------------------------
		// Support functions

			public function require_login() {
				if ($this->user_id == 0) {
					$this->login_redirect(url('/'));
				}
			}

			public function login_redirect($url) {
				save_request_redirect($url, $this->last_login_get());
			}

	}

?>