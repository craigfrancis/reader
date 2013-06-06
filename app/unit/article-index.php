<?php

	class article_index_unit extends unit {

		public function setup($config = array()) {

			$sources = array();

			$db = db_get();

			$sql = 'SELECT
						s.ref,
						s.title
					FROM
						' . DB_PREFIX . 'source AS s
					WHERE
						s.deleted = "0000-00-00 00:00:00"
					ORDER BY
						s.sort';

			foreach ($db->fetch_all($sql) as $row) {

				$sources[] = array(
						'url' => url('/articles/:source/', array('source' => $row['ref'])),
						'ref' => $row['ref'],
						'name' => $row['title'],
						'count' => rand(5, 10),
					);

			}

			$this->set('sources', $sources);

		}

	}

?>