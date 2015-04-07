<?php

	class article_index_unit extends unit {

		protected $config = array(
				'read_url' => NULL,
			);

		protected function authenticate($config) {
			return (USER_LOGGED_IN === true);
		}

		protected function setup($config) {

			//--------------------------------------------------
			// Config

				$db = db_get();

			//--------------------------------------------------
			// Source error

				$error_limit = new timestamp('-6 hours');

				$sql = 'SELECT
							1
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							s.error_date >= s.updated AND
							s.updated <= "' . $db->escape($error_limit) . '" AND
							s.deleted = "0000-00-00 00:00:00"
						LIMIT
							1';

				$source_error = ($db->num_rows($sql) > 0);

			//--------------------------------------------------
			// Sources

				$sources = array();

				$sql = 'SELECT
							s.ref,
							s.title,
							COUNT(sa.id) AS unread_count,
							sa.id AS article_id
						FROM
							' . DB_PREFIX . 'source AS s
						LEFT JOIN
							' . DB_PREFIX . 'source_article AS sa ON sa.source_id = s.id
						LEFT JOIN
							' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
						WHERE
							s.deleted = "0000-00-00 00:00:00" AND
							sa.id IS NOT NULL AND
							sar.article_id IS NULL
						GROUP BY
							s.id
						ORDER BY
							s.sort';

				foreach ($db->fetch_all($sql) as $row) {

					if ($row['unread_count'] == 1) {
						$url = url('/articles/:source/', array('source' => $row['ref'], 'id' => $row['article_id']));
					} else {
						$url = url('/articles/:source/', array('source' => $row['ref']));
					}

					$sources[] = array(
							'url' => $url,
							'ref' => $row['ref'],
							'name' => $row['title'],
							'count' => $row['unread_count'],
						);

				}

			//--------------------------------------------------
			// Variables

				$this->set('sources', $sources);
				$this->set('source_error', $source_error);
				$this->set('read_url', $config['read_url']);

		}

	}

?>