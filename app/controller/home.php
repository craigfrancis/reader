<?php

	class home_controller extends controller {

		public function action_index() {

			//--------------------------------------------------
			// Index unit

				unit_add('user_login', array(
						'helper' => config::get('user'),
						'dest_url' => url('/articles/'),
					));

			//--------------------------------------------------
			// Page title

				$response = response_get();

				$response->title_full_set('Reader');

		}

	}

?>