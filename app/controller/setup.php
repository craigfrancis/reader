<?php

	class setup_controller extends controller {

		public function route() {

			//--------------------------------------------------
			// Require login

				if (!USER_LOGGED_IN) {
					redirect(url('/'));
				}

			//--------------------------------------------------
			// Footer URLs

				$response = response_get();

				$response->set('footer_urls', array(
						array('text' => 'Back',       'class' => 'back',    'href' => url('/articles/')),
						array('text' => 'Logout',     'class' => 'logout',  'href' => url('/logout/')),
					));

		}

		public function action_index() {

			$unit = unit_add('user_setup', array(
					'user' => config::get('user'),
					'dest_url' => url('/'),
					'sources_url' => url('/sources/'),
				));

		}

	}

?>