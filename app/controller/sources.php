<?php

	class sources_controller extends controller {

		public function route() {

			//--------------------------------------------------
			// Require login

				if (!USER_LOGGED_IN) {
					redirect(url('/'));
				}

			//--------------------------------------------------
			// Footer URLs

				$response = response_get();

				if (request_folder_get(1) == NULL) {

					$response->set('footer_urls', array(
							array('text' => 'Back', 'class' => 'back', 'href' => url('/setup/')),
						));

				} else {

					$response->set('footer_urls', array(
							array('text' => 'Back', 'class' => 'back', 'href' => url('/sources/')),
						));

				}

		}

		public function action_index() {

			$unit = unit_add('sources_index', array(
					'add_url' => url('/sources/edit/'),
					'edit_url' => url('/sources/edit/'),
				));

		}

		public function action_edit() {

			$id = request('id');

			$unit = unit_add('sources_edit', array(
					'id' => $id,
					'index_url' => url('/sources/'),
					'delete_url' => url('/sources/delete/'),
					'articles_url' => url('/articles/:source/'),
				));

		}

		public function action_delete() {

			$id = request('id');

			$unit = unit_add('sources_delete', array(
					'id' => $id,
					'index_url' => url('/sources/'),
					'edit_url' => url('/sources/edit/'),
				));

		}

	}

?>