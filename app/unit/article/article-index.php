<?php

	class article_index_unit extends unit {

		public function setup($config = array()) {

			//--------------------------------------------------
			// Config

				$config = array_merge(array(
						'read_url' => NULL,
					), $config);

			//--------------------------------------------------
			// Sources

				$sources = array();

				$db = db_get();

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
				$this->set('read_url', $config['read_url']);

		}

	}

?>