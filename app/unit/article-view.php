<?php

	class article_view_unit extends unit {

		private $article_id = NULL;
		private $article_title = NULL;
		private $article_source_id = NULL;
		private $article_source_ref = NULL;
		private $article_published = NULL;

		public function setup($config = array()) {

			//--------------------------------------------------
			// Config

				$config = array_merge(array(
						'source' => NULL,
						'article' => NULL,
					), $config);

			//--------------------------------------------------
			// Source

				$db = db_get();

				$sql = 'SELECT
							s.id
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							s.ref = "' . $db->escape($config['source']) . '" AND
							s.deleted = "0000-00-00 00:00:00"';

				if ($row = $db->fetch($sql)) {
					$source_id = $row['id'];
					$source_ref = $config['source'];
				} else {
					error_send('page-not-found');
				}

			//--------------------------------------------------
			// Articles

				$sql = 'SELECT
							sa.id,
							sa.title,
							sa.published
						FROM
							' . DB_PREFIX . 'source_article AS sa
						LEFT JOIN
							' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
						WHERE
							sa.id = "' . $db->escape($config['article']) . '" AND
							sa.source_id = "' . $db->escape($source_id) . '"
						GROUP BY
							sa.id';

				if ($row = $db->fetch($sql)) {

					$this->article_id = $row['id'];
					$this->article_title = $row['title'];
					$this->article_source_id = $source_id;
					$this->article_source_ref = $source_ref;
					$this->article_published = $row['published'];

					$this->set('article_title', $row['title']);
					$this->set('article_url', gateway_url('article', array('id' => $row['id'])));

				} else {

					exit_with_error('Cannot find article "' . $config['article'] . '"');

				}

		}

		public function title_get() {
			return $this->article_title;
		}

		public function sibling_url($rel) {

			$db = db_get();

			$where_sql = '
				sa.source_id = "' . $db->escape($this->article_source_id) . '"';

			if ($rel > 0) {

				$where_sql .= ' AND
					(
						sa.published > "' . $db->escape($this->article_published) . '" OR
						(
							sa.published = "' . $db->escape($this->article_published) . '" AND
							sa.id < "' . $db->escape($this->article_id) . '"
						)
					)';

				$order_sql = '
					sa.published ASC';

			} else {

				$where_sql .= ' AND
					(
						sa.published < "' . $db->escape($this->article_published) . '" OR
						(
							sa.published = "' . $db->escape($this->article_published) . '" AND
							sa.id > "' . $db->escape($this->article_id) . '"
						)
					)';

				$order_sql = '
					sa.published DESC';

			}

			$sql = 'SELECT
						sa.id
					FROM
						' . DB_PREFIX . 'source_article AS sa
					WHERE
						' . $where_sql . '
					ORDER BY
						' . $order_sql .'
					LIMIT
						1';

			if ($row = $db->fetch($sql)) {
				return url('/articles/:source/', array('source' => $this->article_source_ref, 'id' => $row['id']));
			} else {
				return NULL;
			}

		}

	}

?>
