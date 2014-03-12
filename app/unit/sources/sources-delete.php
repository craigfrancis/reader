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

				$table_sql = DB_PREFIX . 'source';

				$where_sql = '
					id = "' . $db->escape($source_id) . '" AND
					deleted = "0000-00-00 00:00:00"';

				$db->select($table_sql, array('title'), $where_sql);

				if ($row = $db->fetch_row()) {

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

								$values = array(
										'deleted' => date('Y-m-d H:i:s'),
									);

								$db->update($table_sql, $values, $where_sql);

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