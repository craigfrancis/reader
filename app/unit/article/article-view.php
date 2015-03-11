<?php

	class article_view_unit extends unit {

		protected $config = array(
				'source' => NULL,
				'article' => NULL,
				'read' => NULL,
			);

		private $article_id = NULL;
		private $article_source_id = NULL;
		private $article_published = NULL;
		private $article_link = NULL;
		private $article_read = NULL;

		protected function setup($config) {

			//--------------------------------------------------
			// Source

				$db = db_get();

				$sql = 'SELECT
							s.id,
							s.title,
							s.url_http
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							s.ref = "' . $db->escape($config['source']) . '" AND
							s.deleted = "0000-00-00 00:00:00"';

				if ($row = $db->fetch_row($sql)) {
					$source_id = $row['id'];
					$source_title = $row['title'];
					$source_url = $row['url_http'];
					$source_ref = $config['source'];
				} else {
					error_send('page-not-found');
				}

			//--------------------------------------------------
			// Article

				$sql = 'SELECT
							sa.id,
							sa.title,
							sa.link_source,
							sa.link_clean,
							sa.published,
							sa.description,
							IF(sar.article_id IS NOT NULL, 1, 0) AS article_read
						FROM
							' . DB_PREFIX . 'source_article AS sa
						LEFT JOIN
							' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
						WHERE
							sa.id = "' . $db->escape($config['article']) . '" AND
							sa.source_id = "' . $db->escape($source_id) . '"
						GROUP BY
							sa.id';

				if ($row = $db->fetch_row($sql)) {

					$article_id = $row['id'];
					$article_title = $row['title'];
					$article_published = $row['published'];
					$article_html = $row['description'];
					$article_read = ($row['article_read'] == 1);

					if ($row['link_clean'] != '' && $row['link_clean'] != '-') {
						$article_link = $row['link_clean'];
					} else {
						$article_link = $row['link_source'];
					}

				} else {

					exit_with_error('Cannot find article "' . $config['article'] . '"');

				}

			//--------------------------------------------------
			// Article read

				if (($config['read'] === true) || ($config['read'] === NULL && $article_read === false)) {

					if (!$article_read) {

						$values = array(
								'article_id' => $article_id,
								'user_id' => USER_ID,
								'read_date' => date('Y-m-d H:i:s'),
							);

						$db->insert(DB_PREFIX . 'source_article_read', $values, $values);

					}

					$article_read = true;

				} else if ($config['read'] === false) {

					if ($article_read) {

						$db->query('DELETE FROM
										' . DB_PREFIX . 'source_article_read
									WHERE
										article_id = "' . $db->escape($article_id) . '" AND
										user_id = "' . $db->escape(USER_ID) . '"');

					}

					$article_read = false;

				}

			//--------------------------------------------------
			// Article HTML

				$article_html = trim($article_html);

				if ($article_html != '') {

					//--------------------------------------------------
					// Parse

						libxml_use_internal_errors(true);

						$article_dom = new DomDocument();
						$article_dom->loadHTML('<?xml encoding="UTF-8">' . $article_html);

					//--------------------------------------------------
					// Images

						$images = $article_dom->getElementsByTagName('img');
						for ($k = ($images->length - 1); $k >= 0; $k--) { // For each will skip nodes

							$image = $images->item($k);

							$wrapper_node = $article_dom->createElement('span');
							$wrapper_node->setAttribute('class', 'image_wrapper');

							$image->parentNode->replaceChild($wrapper_node, $image);

							$wrapper_node->appendChild($image);

							$title = $image->getAttribute('title');
							if (!$title) {
								$title = $image->getAttribute('alt');
							}

							$src_old = $image->getAttribute('src');
							if ($src_old) {
								$src_remote = articles::img_remote_url($source_url, $src_old);
								$src_local = articles::img_local_url($article_id, $src_remote);
								if ($src_local !== NULL) {
									$image->setAttribute('src', $src_local);
								} else if ($src_old != $src_remote) {
									$image->setAttribute('src', $src_remote);
								}
							}

							if ($title) {
								$title_node = $article_dom->createElement('em', $title);
								$wrapper_node->appendChild($title_node);
							}

						}

					//--------------------------------------------------
					// Remove odd anchor links

						if ($source_ref == 'the-daily-wtf') {

							$links = $article_dom->getElementsByTagName('a');
							for ($k = ($links->length - 1); $k >= 0; $k--) { // For each will skip nodes

								$link = $links->item($k);

								if (substr($link->getAttribute('href'), 0, 1) == '#') {
									$link->removeAttribute('href');
								}

							}

						}

					//--------------------------------------------------
					// Remove bad tags

						foreach (array('script', 'link', 'iframe') as $tag) {

							$nodes = $article_dom->getElementsByTagName($tag);

							for ($k = ($nodes->length - 1); $k >= 0; $k--) { // For each will skip nodes

								$node = $nodes->item($k);

								$src = trim($node->getAttribute('src'));
								if ($src) {
									$text = '<' . $tag . ' src="' . $src . '">';
								} else {
									$src = trim($node->getAttribute('href'));
									$text = '<' . $tag . ' href="' . $src . '">';
								}

								if ($src) {
									$replacement_node = $article_dom->createElement('a', $text);
									$replacement_node->setAttribute('href', $src);
								} else {
									$replacement_node = $article_dom->createElement('span', $text);
								}

								$replacement_node->setAttribute('class', $tag . '_tag');
								$replacement_node->setAttribute('title', $src);

								$node->parentNode->replaceChild($replacement_node, $node);

							}

						}

					//--------------------------------------------------
					// Remove bad attributes

						$xpath = new DOMXPath($article_dom);

						foreach (array('style', 'onclick') as $attribute) {
							foreach ($xpath->query('//*[@' . $attribute . ']') as $element) {
								$element->removeAttributeNode($element->getAttributeNode($attribute));
							}
						}

					//--------------------------------------------------
					// Convert <tag /> to <tag></tag>

						// foreach (array('iframe') as $tag) {
						//
						// 	$nodes = $article_dom->getElementsByTagName($tag);
						//
						// 	for ($k = ($nodes->length - 1); $k >= 0; $k--) { // For each will skip nodes
						//
						// 		$node = $nodes->item($k);
						//
						// 		$node->appendChild($article_dom->createTextNode(''));
						//
						// 	}
						//
						// }

					//--------------------------------------------------
					// Back to a string

						// $article_html = $article_dom->saveXML();

						$article_html = '';

						$body = $article_dom->documentElement->firstChild;
						if ($body->hasChildNodes()) {
							foreach ($body->childNodes as $node) {
								$article_html .= $article_dom->saveXML($node); // Not saveXML due to <script> tag - see article 1310
							}
						}

				}

				$article_html = '<!DOCTYPE html>
					<html lang="en-GB" xml:lang="en-GB" xmlns="http://www.w3.org/1999/xhtml">
					<head>

						<meta charset="UTF-8" />
						<meta name="viewport" content="initial-scale=1" />

						<title>Article</title>

						<link rel="shortcut icon" type="image/x-icon" href="/a/img/global/favicon.ico" />
						<link rel="stylesheet" type="text/css" href="' . html(timestamp_url('/a/css/global/article.css')) . '" media="all" />

						<base target="_blank" />

					</head>
					<body id="p_articles" class="' . html($source_ref) . '">

						<div id="article_wrapper" class="' . html($source_ref) . '">
							<h1>' . html($article_title) . '</h1>
							<div>
								' . $article_html . "\n" . '
							</div>
							<p class="article_info">
								<span class="published">' . html(date('l jS F Y, g:ia', strtotime($article_published))) . '</span>
							</p>
						</div>

					</body>
					</html>';

				$this->set('article_html', $article_html);

			//--------------------------------------------------
			// Variables

				$this->article_id = $article_id;
				$this->article_source_id = $source_id;
				$this->article_published = $article_published;
				$this->article_link = $article_link;
				$this->article_read = $article_read;

				$this->set('source_title', $source_title);
				$this->set('article_title', $article_title);

			//--------------------------------------------------
			// JavaScript

				// $response = response_get();
				// $response->js_add('/a/js/article.js');

		}

		public function read_get() {
			return $this->article_read;
		}

		public function article_link_get() {
			return $this->article_link;
		}

		public function sibling_id_get($rel) {

			$db = db_get();

			$where_sql = '
				sa.source_id = "' . $db->escape($this->article_source_id) . '"';

			if ($rel > 0) {

				$where_sql .= ' AND
					(
						sa.published > "' . $db->escape($this->article_published) . '" OR
						(
							sa.published = "' . $db->escape($this->article_published) . '" AND
							sa.id > "' . $db->escape($this->article_id) . '"
						)
					)';

					// [2013-09-13 07:43:34] ... id=5650 ... (2013-09-11 15:54:32)
					// [XXXX-XX-XX XX:XX:XX] ... id=5649 ... (2013-09-11 15:54:32)
					// [2013-09-13 07:43:36] ... id=5648 ... (2013-09-11 15:54:34)
					// [2013-09-13 07:43:37] ... id=5647 ... (2013-09-11 15:54:36)
					//
					// Published date the same for 5650 and 5649, the sub condition
					// for matching published dates is correct (sa.id > X), but the
					// order was wrong, 5649 should have been seen first (was not
					// specified at the time).

				$order_sql = '
					sa.published ASC,
					sa.id ASC';

			} else {

				$where_sql .= ' AND
					(
						sa.published < "' . $db->escape($this->article_published) . '" OR
						(
							sa.published = "' . $db->escape($this->article_published) . '" AND
							sa.id < "' . $db->escape($this->article_id) . '"
						)
					)';

				$order_sql = '
					sa.published DESC,
					sa.id DESC';

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

			if ($row = $db->fetch_row($sql)) {
				return $row['id'];
			} else {
				return NULL;
			}

		}

	}

?>