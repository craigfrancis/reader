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
			// Sources

				$sources = array();

				$sql = 'SELECT
							s.ref,
							s.title,
							COUNT(sa.id) AS unread_count,
							MIN(sa.id) AS article_id
						FROM
							' . DB_PREFIX . 'source AS s
						LEFT JOIN
							' . DB_PREFIX . 'source_article AS sa ON sa.source_id = s.id
						LEFT JOIN
							' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = ?
						WHERE
							s.deleted = "0000-00-00 00:00:00" AND
							sa.id IS NOT NULL AND
							sa.created < ? AND
							sar.article_id IS NULL
						GROUP BY
							s.id
						ORDER BY
							s.sort';

				$parameters = array();
				$parameters[] = array('i', USER_ID);
				$parameters[] = array('s', USER_DELAY);

				foreach ($db->fetch_all($sql, $parameters) as $row) {

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
				$this->set('read_url', $config['read_url']);

		}

	}

?>