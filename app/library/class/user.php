<?php

	class user extends user_base {

		//--------------------------------------------------
		// Setup

			public function __construct() {

				$this->identification_type = 'username';

				$this->setup();

			}

	}

?>