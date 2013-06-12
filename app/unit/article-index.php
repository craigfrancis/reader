<?php

	class article_index_unit extends unit {

		public function setup($config = array()) {

			$sources = array();

			$db = db_get();

			$sql = 'SELECT
						s.ref,
						s.title,
						COUNT(sa.id) AS article_total,
						COUNT(sar.article_id) AS article_read
					FROM
						' . DB_PREFIX . 'source AS s
					LEFT JOIN
						' . DB_PREFIX . 'source_article AS sa ON sa.source_id = s.id
					LEFT JOIN
						' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
					WHERE
						s.deleted = "0000-00-00 00:00:00"
					GROUP BY
						s.id
					ORDER BY
						s.sort';

			foreach ($db->fetch_all($sql) as $row) {

				$sources[] = array(
						'url' => url('/articles/:source/', array('source' => $row['ref'])),
						'ref' => $row['ref'],
						'name' => $row['title'],
						'count' => ($row['article_total'] - $row['article_read']),
					);

			}

			$this->set('sources', $sources);

		}

	}

?>