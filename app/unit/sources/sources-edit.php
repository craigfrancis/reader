<?php

	class sources_edit_unit extends unit {

		protected $config = array(
				'id'         => array('type' => 'int'),
				'index_url'  => array('type' => 'url'),
				'delete_url' => array('type' => 'url'),
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
				$where_sql = NULL;

				$source_update_url = NULL;
				$source_updated = NULL;
				$source_error = NULL;

				$action_edit = ($source_id > 0);

				if ($action_edit) {

					$where_sql = '
						id = "' . $db->escape($source_id) . '" AND
						deleted = "0000-00-00 00:00:00"';

					$db->select($table_sql, array('title', 'sort', 'updated', 'error_date', 'error_text'), $where_sql);

					if ($row = $db->fetch_row()) {

						$source_title = $row['title'];
						$source_sort = $row['sort'];
						$source_update_url = gateway_url('update', array('source' => $source_id, 'dest' => url()));

						if ($row['updated'] != '0000-00-00 00:00:00') {
							$source_updated = date('D jS M Y, g:i:sa', strtotime($row['updated']));
						} else {
							$source_updated = 'N/A';
						}

						if ($row['error_date'] != '0000-00-00 00:00:00') {
							$source_error = strtotime($row['error_date']);
							if ($source_error > strtotime('-2 days')) {
								$source_error = $row['error_text'] . ' (' . date('D, g:ia', $source_error) . ')';
							} else {
								$source_error = NULL;
							}
						}

						$this->set('source_title', $source_title);

					} else {

						exit_with_error('Cannot find source id "' . $source_id . '"');

					}

				}

			//--------------------------------------------------
			// Form setup

				$form = new form();
				$form->form_class_set('basic_form');
				$form->db_table_set_sql($table_sql);
				$form->db_where_set_sql($where_sql);

				$field_title = new form_field_text($form, 'Title');
				$field_title->db_field_set('title');
				$field_title->min_length_set('The source title is required.');
				$field_title->max_length_set('The source title cannot be longer than XXX characters.');

				$field_ref = new form_field_text($form, 'Ref');
				$field_ref->db_field_set('ref');
				$field_ref->min_length_set('The source ref is required.');
				$field_ref->max_length_set('The source ref cannot be longer than XXX characters.');
				$field_ref->input_data_set('js-item-link-src', $field_title->input_id_get());

				$field_sort = new form_field_number($form, 'Sort');
				$field_sort->db_field_set('sort');
				$field_sort->format_error_set('The source sorting value does not appear to be a number.');
				$field_sort->min_value_set('The source sorting value must be more than or equal to XXX.', 0);
				$field_sort->max_value_set('The source sorting value must be less than or equal to XXX.', 9999);
				$field_sort->step_value_set('The source sorting value must be a whole number.');

				$field_website = new form_field_url($form, 'Website URL');
				$field_website->db_field_set('url_http');
				$field_website->format_error_set('The website URL does not appear to be correct.');
				$field_website->allowed_schemes_set('The website URL has an invalid scheme.', array('http', 'https'));
				$field_website->min_length_set('The website URL is required.');
				$field_website->max_length_set('The website URL cannot be longer than XXX characters.');

				$field_feed = new form_field_url($form, 'Feed URL');
				$field_feed->db_field_set('url_feed');
				$field_feed->format_error_set('The feed URL does not appear to be correct.');
				$field_feed->allowed_schemes_set('The feed URL has an invalid scheme.', array('http', 'https'));
				$field_feed->min_length_set('The feed URL is required.');
				$field_feed->max_length_set('The feed URL cannot be longer than XXX characters.');

				if ($source_updated) {
					$field_updated = new form_field_info($form, 'Updated');
					$field_updated->value_set_html(html($source_updated) . ' (<a href="' . html($source_update_url) . '">update</a>)');
				}

				if ($source_error) {
					$field_error = new form_field_info($form, 'Error');
					$field_error->value_set($source_error);
				}

			//--------------------------------------------------
			// Form submitted

				if ($form->submitted()) {

					//--------------------------------------------------
					// Validation

						//--------------------------------------------------
						// Sort

							$sort_new = intval($field_sort->value_get());

							if ($sort_new == 0) {

								$sql = 'SELECT
											MAX(s.sort) AS sort
										FROM
											' . DB_PREFIX . 'source AS s
										WHERE
											s.deleted = "0000-00-00 00:00:00"';

								if ($row = $db->fetch_row($sql)) {
									$sort_new = (intval($row['sort']) + 1);
								}

							}

							if ($action_edit && $source_sort < $sort_new) {
								$sort_new++; // Going down a level, means this one will vacate a space.
							}

							$field_sort->value_set($sort_new);

							$db->query('UPDATE
											' . DB_PREFIX . 'source AS s
										SET
											s.sort = (s.sort + 1)
										WHERE
											s.sort >= "' . $db->escape($sort_new) . '" AND
											s.deleted = "0000-00-00 00:00:00"');

						//--------------------------------------------------
						// Ref

							$clean_ref = human_to_link($field_ref->value_get());

							$sql = 'SELECT
										s.id
									FROM
										' . DB_PREFIX . 'source AS s
									WHERE
										s.id != "' . $db->escape($source_id) . '" AND
										s.ref = "' . $db->escape($clean_ref) . '" AND
										s.deleted = "0000-00-00 00:00:00"
									LIMIT
										1';

							if ($row = $db->fetch_row($sql)) {
								$field_ref->error_add('This URL is already in use');
							} else {
								$field_ref->value_set($clean_ref);
							}

					//--------------------------------------------------
					// Form valid

						if ($form->valid()) {

							//--------------------------------------------------
							// Save

								if ($action_edit) {
									$form->db_save();
								} else {
									$source_id = $form->db_insert();
								}

							//--------------------------------------------------
							// Sort cleanup

								$db->query('SET @sort=0');
								$db->query('UPDATE ' . DB_PREFIX . 'source SET sort=(@sort:=@sort+1) WHERE deleted = "0000-00-00 00:00:00" ORDER BY sort ASC');

							//--------------------------------------------------
							// Next page

								$form->dest_redirect($config['index_url']);

						}

				}

			//--------------------------------------------------
			// Form default

				if ($form->initial()) {

					if ($action_edit) {
					}

				}

			//--------------------------------------------------
			// Variables

				$this->set('action_edit', $action_edit);
				$this->set('form', $form);

				if ($action_edit) {
					$this->set('delete_url', $config['delete_url']->get(array('id' => $source_id)));
				}

			//--------------------------------------------------
			// JavaScript

				$response = response_get();

				$response->js_add('/a/js/jquery/jquery-1.11.0.js');
				$response->js_add('/a/js/item-link.js');

		}

	}

?>