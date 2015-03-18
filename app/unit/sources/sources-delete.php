<?php

	class sources_delete_unit extends unit {

		protected $config = array(
				'id'        => array('type' => 'int'),
				'index_url' => array('type' => 'url'),
				'edit_url'  => array('type' => 'url'),
			);

		// protected function authenticate($config) {
		// 	return false;
		// }

		protected function setup($config) {

			//--------------------------------------------------
			// Config

				$source_id = intval($config['id']);

				$db = db_get();

			//--------------------------------------------------
			// Details

				$record = record_get(DB_PREFIX . 'source', $source_id, array(
						'title',
					));

				if ($row = $record->values_get()) {

					$this->set('source_title', $row['title']);

				} else {

					exit_with_error('Cannot find source id "' . $source_id . '"');

				}

			//--------------------------------------------------
			// Form setup

				$form = new form();
				$form->form_class_set('delete_form');
				$form->form_button_set('Delete');

			//--------------------------------------------------
			// Form submitted

				if ($form->submitted()) {

					//--------------------------------------------------
					// Validation



					//--------------------------------------------------
					// Form valid

						if ($form->valid()) {

							//--------------------------------------------------
							// Delete

								$record->delete();

							//--------------------------------------------------
							// Next page

								redirect($config['index_url']);

						}

				}

			//--------------------------------------------------
			// Variables

				$this->set('form', $form);

				$this->set('edit_url', $config['edit_url']->get(array('id' => $source_id)));

		}

	}

?>