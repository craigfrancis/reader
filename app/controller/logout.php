<?php

	class logout_controller extends controller {

		public function action_index() {

			$user = config::get('user');
			$user->logout();

			redirect(url('/'));

		}

	}

?>