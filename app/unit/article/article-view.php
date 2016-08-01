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

		protected function authenticate($config) {
			return (USER_LOGGED_IN === true);
		}

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
							sa.source_id = "' . $db->escape($source_id) . '" AND
							sa.created < "' . $db->escape(USER_DELAY) . '"
						GROUP BY
							sa.id';

				if ($row = $db->fetch_row($sql)) {

					$article_id = $row['id'];
					$article_title = $row['title'];
					$article_published = new timestamp($row['published'], 'db');
					$article_html = $row['description'];
					$article_read = ($row['article_read'] == 1);
					$article_recache_url = gateway_url('recache', array('article' => $article_id, 'dest' => url()));
					$article_link_clean = $row['link_clean'];

					if ($row['link_clean'] != '' && $row['link_clean'] != '-') {
						$article_link = $row['link_clean'];
						$article_domain = $row['link_clean'];
					} else {
						$article_link = $row['link_source'];
						$article_domain = $source_url;
					}

					$article_domain = preg_replace('/^(https?:\/\/[^\/]+).*/', '$1', $article_link);

				} else {

					exit_with_error('Cannot find article "' . $config['article'] . '"');

				}

			//--------------------------------------------------
			// Article read

				if (($config['read'] === true) || ($config['read'] === NULL && $article_read === false)) {

					if (!$article_read) {

						$now = new timestamp();

						$values = array(
								'article_id' => $article_id,
								'user_id' => USER_ID,
								'read_date' => $now,
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
			// Article HTML (content)

				$article_html = trim($article_html);
				$article_html = str_replace('< ', '&lt; ', $article_html); // Bad HTML encoding, e.g. "<code> if (count < 0) {"

				$k = 0;
				while (($k++ < 3) && (preg_match('/&amp;[a-z]+;/', $article_html))) { // Includes double HTML encoding.
					$article_html = html_decode($article_html);
				}

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

							$src_old = trim($image->getAttribute('src'));
							if ($src_old) {
								$src_remote = articles::img_remote_url($article_domain, $src_old);
								$src_local = articles::img_local_url($article_id, $src_remote);
								if ($src_local !== NULL) {
									$image->setAttribute('src', $src_local);
									$image->setAttribute('data-src', $src_old);
								} else if ($src_old != $src_remote) {
									$image->setAttribute('src', $src_remote);
									$image->setAttribute('data-src', $src_old);
								}
							}

							if ($image->getAttribute('srcset') != '') $image->removeAttribute('srcset');
							if ($image->getAttribute('sizes') != '')  $image->removeAttribute('sizes');

							if ($title) {
								$title_node = $article_dom->createElement('em', $title);
								$wrapper_node->appendChild($title_node);
							}

						}

					//--------------------------------------------------
					// Remove odd anchor links

						$links = $article_dom->getElementsByTagName('a');
						for ($k = ($links->length - 1); $k >= 0; $k--) {

							$link = $links->item($k);

							if (substr($link->getAttribute('href'), 0, 1) == '#') { // e.g. the-daily-wtf
								$link->removeAttribute('href');
							}

						}

					//--------------------------------------------------
					// Remove bad tags

						foreach (array('script', 'link', 'iframe', 'style') as $tag) {

							$nodes = $article_dom->getElementsByTagName($tag);

							for ($k = ($nodes->length - 1); $k >= 0; $k--) {

								$node = $nodes->item($k);

								$src = trim($node->getAttribute('src'));
								if ($src) {
									$text = '<' . $tag . ' src="' . $src . '">';
								} else {
									$src = trim($node->getAttribute('href'));
									if ($src) {
										$text = '<' . $tag . ' href="' . $src . '">';
									} else if ($tag == 'script') {
										$text = '<' . $tag . '></' . $tag . '>';
									} else {
										$text = '<' . $tag . ' />';
									}
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

						foreach (array('video', 'a') as $tag) { // Used to fix "<a name='more'></a>", was also used for 'iframe' (but now that is treated as a bad tag).

							$nodes = $article_dom->getElementsByTagName($tag);

							for ($k = ($nodes->length - 1); $k >= 0; $k--) {

								$node = $nodes->item($k);

								$node->appendChild($article_dom->createTextNode(''));

							}

						}

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

			//--------------------------------------------------
			// Exceptions

				if ($source_ref == 'macrumors') {

					$article_link .= '#comments';

				} else if ($source_ref == 'the-daily-wtf') {

					$article_link = preg_replace('/(\/\/thedailywtf.com\/articles)\/([^\/]+$)/', '$1/comments/$2', $article_link);

				}

			//--------------------------------------------------
			// Article HTML (page)

				$article_html = '<!DOCTYPE html>
					<html lang="en-GB" xml:lang="en-GB" xmlns="http://www.w3.org/1999/xhtml">
					<head>

						<meta charset="UTF-8" />
						<meta name="viewport" content="width=device-width, initial-scale=1" />

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
								<span class="recache"><a href="' . html($article_recache_url) . '" title="Refresh">↻</a></span>
								<span class="published">' . $article_published->html('l jS F Y, g:ia') . '</span>
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

		public function sibling_id_get($rel, $config = array()) {

			$db = db_get();

			$config = array_merge(array(
					'state' => 'unread',
				), $config);

			$where_sql = '
				sa.created < "' . $db->escape(USER_DELAY) . '" AND
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

			if ($config['state'] === 'read') {

				$where_sql .= ' AND
					sar.article_id IS NOT NULL';

			} else if ($config['state'] === 'unread') {

				$where_sql .= ' AND
					sar.article_id IS NULL';

			}

			$sql = 'SELECT
						sa.id
					FROM
						' . DB_PREFIX . 'source_article AS sa
					LEFT JOIN
						' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
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