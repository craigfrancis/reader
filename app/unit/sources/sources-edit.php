<?php

	class sources_edit_unit extends unit {

		protected $config = array(
				'id'           => array('type' => 'int'),
				'index_url'    => array('type' => 'url'),
				'delete_url'   => array('type' => 'url'),
				'articles_url' => array('type' => 'url'),
			);

		protected function authenticate($config) {
			return (USER_LOGGED_IN === true);
		}

		protected function setup($config) {

			//--------------------------------------------------
			// Config

				$source_id = intval($config['id']);

				$db = db_get();

			//--------------------------------------------------
			// Details

				$source_update_url = NULL;
				$source_updated = NULL;
				$source_error = NULL;

				$action_edit = ($source_id != 0);

				$record = record_get(DB_PREFIX . 'source', $source_id, array(
						'ref',
						'title',
						'sort',
						'url_http',
						'url_feed',
						'updated',
						'error_date',
						'error_text',
					));

				if ($action_edit) {

					if ($row = $record->values_get()) {

						$source_ref = $row['ref'];
						$source_title = $row['title'];
						$source_sort = $row['sort'];
						$source_update_url = gateway_url('update', array('source' => $source_id, 'dest' => url()));
						$source_updated = new timestamp($row['updated'], 'db');
						$source_error = new timestamp($row['error_date'], 'db');

						if ($source_error->null() == false && ($source_updated->null() == true || $source_error >= $source_updated)) {
							$source_error = $row['error_text'] . ' (' . $source_error->format('D, g:ia') . ')';
						} else {
							$source_error = NULL;
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
				$form->db_record_set($record);

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
				$field_website->scheme_default_set('http');
				$field_website->scheme_allowed_set('The website URL has an invalid scheme.', array('http', 'https'));
				$field_website->format_error_set('The website URL does not appear to be correct.');
				$field_website->min_length_set('The website URL is required.');
				$field_website->max_length_set('The website URL cannot be longer than XXX characters.');

				$field_feed = new form_field_url($form, 'Feed URL');
				$field_feed->db_field_set('url_feed');
				$field_feed->scheme_default_set('http');
				$field_feed->scheme_allowed_set('The feed URL has an invalid scheme.', array('http', 'https'));
				$field_feed->format_error_set('The feed URL does not appear to be correct.');
				$field_feed->min_length_set('The feed URL is required.');
				$field_feed->max_length_set('The feed URL cannot be longer than XXX characters.');

				if ($action_edit) {

					$article_unread_url = $config['articles_url']->get(array('source' => $source_ref, 'back' => 'source'));
					$article_read_url   = $config['articles_url']->get(array('source' => $source_ref, 'back' => 'source', 'state' => 'read'));
					$article_all_url    = $config['articles_url']->get(array('source' => $source_ref, 'back' => 'source', 'state' => 'all'));

					$field_articles = new form_field_info($form, 'Articles');
					$field_articles->value_set_html('
						<a href="' . html($article_unread_url) . '">Unread</a> |
						<a href="' . html($article_read_url) . '">Read</a> |
						<a href="' . html($article_all_url) . '">All</a>');

				}

				if ($source_updated) {
					$field_updated = new form_field_info($form, 'Updated');
					$field_updated->value_set_html($source_updated->html('D jS M Y, g:i:sa', 'N/A') . ' (<a href="' . html($source_update_url) . '">update</a>)');
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

							$sql = 'UPDATE
										' . DB_PREFIX . 'source AS s
									SET
										s.sort = (s.sort + 1)
									WHERE
										s.sort >= ? AND
										s.deleted = "0000-00-00 00:00:00"';

							$parameters = array();
							$parameters[] = array('i', $sort_new);

							$db->query($sql, $parameters);

						//--------------------------------------------------
						// Ref

							$clean_ref = human_to_link($field_ref->value_get());

							$sql = 'SELECT
										s.id
									FROM
										' . DB_PREFIX . 'source AS s
									WHERE
										s.id != ? AND
										s.ref = ? AND
										s.deleted = "0000-00-00 00:00:00"
									LIMIT
										1';

							$parameters = array();
							$parameters[] = array('i', $source_id);
							$parameters[] = array('s', $clean_ref);

							if ($row = $db->fetch_row($sql, $parameters)) {
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
				$response->js_add('/a/js/item-link.js');

		}

	}

?>