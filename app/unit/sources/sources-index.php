<?php

	class sources_index_unit extends unit {

		protected $config = array(
				'add_url'    => array('type' => 'url'),
				'edit_url'   => array('type' => 'url'),
			);

		protected function authenticate($config) {
			return (USER_LOGGED_IN === true);
		}

		protected function setup($config) {

			//--------------------------------------------------
			// Config

				$db = db_get();

			//--------------------------------------------------
			// Query

				$sources = array();

				$sql = 'SELECT
							s.id,
							s.title,
							s.updated,
							s.error_date
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							s.deleted = "0000-00-00 00:00:00"
						ORDER BY
							s.sort';

				foreach ($db->fetch_all($sql) as $row) {

					$source_updated = new timestamp($row['updated'], 'db');
					$source_error = new timestamp($row['error_date'], 'db');

					$error = false;
					if ($source_updated->null()) {
						$error = true;
					} else if ($source_error->null() == false) {
						$error = ($source_error >= $source_updated);
					}

					$sources[] = array(
							'url' => $config['edit_url']->get(array('id' => $row['id'])),
							'title' => $row['title'],
							'error' => $error,
						);

				}

			//--------------------------------------------------
			// Variables

				$this->set('sources', $sources);
				$this->set('add_url', $config['add_url']);

		}

	}

?>