<?php

	class article_list_source_unit extends unit {

		public function setup($config = array()) {

			//--------------------------------------------------
			// Config

				$config = array_merge(array(
						'source' => NULL,
						'read' => false,
					), $config);

			//--------------------------------------------------
			// Source

				$db = db_get();

				$sql = 'SELECT
							s.id,
							s.title
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							s.ref = "' . $db->escape($config['source']) . '" AND
							s.deleted = "0000-00-00 00:00:00"';

				if ($row = $db->fetch_row($sql)) {
					$source_id = $row['id'];
					$source_title = $row['title'];
					$source_ref = $config['source'];
				} else {
					error_send('page-not-found');
				}

			//--------------------------------------------------
			// Articles

				$where_sql = '
					sa.source_id = "' . $db->escape($source_id) . '"';

				if ($config['read'] === true) {

					$where_sql .= ' AND
						sar.article_id IS NOT NULL';

				} else if ($config['read'] === false) {

					$where_sql .= ' AND
						sar.article_id IS NULL';

				}

				$articles = array();

				$sql = 'SELECT
							sa.id,
							sa.title
						FROM
							' . DB_PREFIX . 'source_article AS sa
						LEFT JOIN
							' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
						WHERE
							' . $where_sql . '
						GROUP BY
							sa.id
						ORDER BY
							sa.published ASC
						LIMIT
							50';

				foreach ($db->fetch_all($sql) as $row) {

					$articles[] = array(
							'url' => url('/articles/:source/', array('source' => $source_ref, 'id' => $row['id'])),
							'name' => $row['title'],
						);

				}

			//--------------------------------------------------
			// Variables

				$this->set('source_title', $source_title);
				$this->set('articles', $articles);

		}

	}

?>