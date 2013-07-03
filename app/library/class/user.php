<?php

	class user extends user_base {

		//--------------------------------------------------
		// Setup

			public function __construct() {

				$this->identification_type = 'username';

				$this->setup();

				$this->session->use_cookies_set(true);
				$this->session->length_set(60*60*24*14);
				$this->session->allow_concurrent_set(true);

			}

	}

?>