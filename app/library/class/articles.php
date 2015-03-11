<?php

	class articles extends check {

		static function img_local_url($article_id, $img_url) {

			$local_path = self::img_local_path($article_id, $img_url);
			if ($local_path !== NULL && is_file($local_path)) {
				return FILE_URL . prefix_replace(FILE_ROOT, $local_path);
			} else {
				return NULL;
			}

		}

		static function img_local_path($article_id, $img_url) {

			if (preg_match('/\.(png|jpg|gif)$/', $img_url, $matches)) {
				return FILE_ROOT . '/article-images/original/' . intval($article_id) . '/' . safe_file_name(hash('sha256', $img_url)) . $matches[0];
			} else {
				return NULL;
			}

		}

		static function img_remote_url($source_url, $img_url) {

			if (substr($img_url, 0, 2) == '//') {

				$img_url = 'http:' . $img_url;

			} else if (substr($img_url, 0, 1) == '/') { // e.g. what-if.xkcd.com

				$img_url = preg_replace('/^(https?:\/\/[^\/]+).*/', '$1', $source_url) . $img_url;

			}

			// if (substr($img_url, -1) == '/') { // codinghorror.com "filename.png/"
			// 	$img_url = substr($img_url, 0, -1);
			// }

			if (prefix_match('http://feeds.feedburner.com/', $img_url)) {

				$img_url = 'https' . substr($img_url, 4);

			} else if (prefix_match('http://www.dzone.com/links/voteCountImage', $img_url)) {

				$img_url .= '&file=image.gif';

			}

			return $img_url;

		}

		static function local_cache() {

			//--------------------------------------------------
			// Config

				$db = db_get();

				$browser = new socket_browser();
				$browser->user_agent_set('Mozilla/4.0 (MSIE 6.0; Windows NT 5.0)');

				libxml_use_internal_errors(true);

			//--------------------------------------------------
			// Articles

				$sql = 'SELECT
							sa.id,
							sa.link_source,
							sa.description,
							s.url_http
						FROM
							' . DB_PREFIX . 'source_article AS sa
						LEFT JOIN
							' . DB_PREFIX . 'source AS s ON s.id = sa.source_id AND s.deleted = s.deleted
						WHERE
							sa.link_clean = ""
						ORDER BY
							sa.published
						LIMIT
							20';

				foreach ($db->fetch_all($sql) as $row) {

					//--------------------------------------------------
					// Details

						$article_id = $row['id'];
						$article_link_source = trim($row['link_source']);
						$article_html = trim($row['description']);
						$source_url = $row['url_http'];

					//--------------------------------------------------
					// Images

						if ($article_html != '') {

							$article_dom = new DomDocument();
							$article_dom->loadHTML('<?xml encoding="UTF-8">' . $article_html);

							$images = $article_dom->getElementsByTagName('img');
							for ($k = ($images->length - 1); $k >= 0; $k--) {

								$image = $images->item($k);

								$img_url = $image->getAttribute('src');
								if ($img_url) {

									$remote_url = self::img_remote_url($source_url, $img_url);
									$local_path = self::img_local_path($article_id, $remote_url);

									if ($local_path !== NULL && !is_file($local_path)) {

										// $remote_data = file_get_contents($remote_url);

										$browser->get($remote_url);

										$remote_code = $browser->response_code_get();
										$remote_data = $browser->response_data_get();

										if ($remote_code == 200 && $remote_data) {

											$local_dir = dirname($local_path);

											if (!is_dir($local_dir)) {
												@mkdir($local_dir, 0777, true);
												if (!is_dir($local_dir)) {
													exit_with_error('Cannot create folder: ' . $local_dir);
												} else {
													@chmod($local_dir, 0777);
												}
											}

											file_put_contents($local_path, $remote_data);
											chmod($local_path, 0666);

										}

									}

								}

							}

						}

					//--------------------------------------------------
					// Clean link

						$browser->get($article_link_source);

						$article_link_clean = trim($browser->url_get());

						if ($article_link_clean == '') {

							$article_link_clean = $article_link_source;

							report_add('Cannot return clean URL for :' . $article_link_source);

						}

						$db->query('UPDATE
										' . DB_PREFIX . 'source_article AS sa
									SET
										sa.link_clean = "' . $db->escape($article_link_clean) . '"
									WHERE
										sa.id = "' . $db->escape($article_id) . '"');

				}

		}

		static function update($condition = NULL) {

			//--------------------------------------------------
			// Config

				$db = db_get();

				libxml_use_internal_errors(true);

			//--------------------------------------------------
			// Condition

				if (is_numeric($condition)) { // A particular source

					$where_sql = '
						s.id = "' . $db->escape($condition) . '" AND
						s.deleted = "0000-00-00 00:00:00"';

				} else if ($condition === true) { // All sources

					$where_sql = '
						s.deleted = "0000-00-00 00:00:00"';

				} else { // Those not recently updated

					$where_sql = '
						s.updated    <= "' . $db->escape(date('Y-m-d H:i:s', strtotime('-10 minutes'))) . '" AND
						s.error_date <= "' . $db->escape(date('Y-m-d H:i:s', strtotime('-1 hour'))) . '" AND
						s.deleted = "0000-00-00 00:00:00"';

				}

			//--------------------------------------------------
			// For each source

				$sql = 'SELECT
							s.id,
							s.url_feed,
							s.article_count
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							' . $where_sql;

				foreach ($db->fetch_all($sql) as $row) {

					//--------------------------------------------------
					// Details

						$error = false;

						$source_id = $row['id'];
						$source_url = $row['url_feed'];
						$source_articles = array();

						$article_count = intval($row['article_count']);
						if ($article_count < 30) {
							$article_count = 30;
						}
						$article_count += 10; // Bit of tolerance

					//--------------------------------------------------
					// Delete old articles

							// Delete by "sar.read_date" (not "sa.published"), as websites
							// like Coding Horror like to change their GUID.

						$sql = 'SELECT
									sa.id
								FROM
									' . DB_PREFIX . 'source_article AS sa
								LEFT JOIN
									' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id
								WHERE
									sa.source_id = "' . $db->escape($source_id) . '" AND
									sar.read_date <= "' . $db->escape(date('Y-m-d H:i:s', strtotime('-2 weeks'))) . '" AND
									sar.read_date IS NOT NULL
								ORDER BY
									sar.read_date DESC
								LIMIT
									' . intval($article_count) . ', 100000';

						foreach ($db->fetch_all($sql) as $row) {

							$cache_dir = FILE_ROOT . '/article-images/original/' . intval($row['id']) . '/';
							if (is_dir($cache_dir)) {
								rrmdir($cache_dir);
							}

							$db->query('DELETE FROM
											' . DB_PREFIX . 'source_article
										WHERE
											id = "' . $db->escape($row['id']) . '"');

						}

					//--------------------------------------------------
					// Get XML ... don't do directly in simple xml as
					// FeedBurner has issues

						$headers = array(
								'User-Agent: RSS Reader',
								'Accept: application/rss+xml',
							);

						$context = stream_context_create(array(
								'http' => array(
										'method' => 'GET',
										'header' => implode("\r\n", $headers) . "\r\n",
									)
							));

						$rss_data = @file_get_contents($source_url, false, $context);

						if (trim($rss_data) == '') {
							$error = 'Cannot return feed';
						}

					//--------------------------------------------------
					// Parse XML

						if (!$error) {

							$rss_data = str_replace(' & ', ' &amp; ', $rss_data); // Try to cleanup bad XML (e.g. ampersand in <title>)

							$rss_xml = simplexml_load_string($rss_data);

							if ($rss_xml === false) {

								$error = 'Cannot parse feed';

								$xml_errors = libxml_get_errors();
								if (($xml_error = array_shift($xml_errors)) !== NULL) {
									$error .= ' - L' . $xml_error->line . '/C' . $xml_error->column . ': ' . trim($xml_error->message);
								}

								libxml_clear_errors();

							}

						}

					//--------------------------------------------------
					// Extract articles

						if (!$error) {

							if (isset($rss_xml->channel->item)) { // RSS

								foreach ($rss_xml->channel->item as $item) {

									$description = strval($item->children('content', true)); // Namespaced <content:encoded> tag
									if ($description == '') {
										$description = strval($item->description);
									}

									$published = strval($item->pubDate);
									if ($published == '') {
										$dc_node = $item->children('dc', true);
										if ($dc_node) {
											$published = strval($dc_node->date); // Namespaced <dc:date> tag
										}
									}

									$guid = strval($item->guid);
									if ($guid == '') {
										$guid = md5($item->link);
									}

									$source_articles[] = array(
											'guid'        => $guid,
											'title'       => strval($item->title),
											'link_source' => strval($item->link),
											'link_clean'  => '',
											'description' => $description,
											'published'   => $published,
										);

								}

							} else if (isset($rss_xml->entry)) { // Atom

								foreach ($rss_xml->entry as $entry) {

									if ($entry->content) {
										$description = strval($entry->content);
									} else {
										$description = strval($entry->summary);
									}

									$published = strval($entry->published);
									if ($published == '') {
										$published = strval($entry->updated);
									}

									$url = '';
									if (count($entry->link) > 1) { // ref "Chromium Blog"
										foreach ($entry->link as $link) {
											if ((!isset($link['type']) || $link['type'] != 'application/atom+xml') && (!isset($link['rel']) || $link['rel'] != 'replies')) {
												$url = strval($link['href']);
											}
										}
									} else {
										$url = strval($entry->link['href']);
									}

									$source_articles[] = array(
											'guid'        => strval($entry->id),
											'title'       => strval($entry->title),
											'link_source' => $url,
											'link_clean'  => '',
											'description' => $description,
											'published'   => $published,
										);

								}

							} else {

								$error = 'Unknown feed format';

							}

							if (!$error && count($source_articles) == 0) {

								$error = 'No articles found';

							}

						}

					//--------------------------------------------------
					// Add articles

						foreach ($source_articles as $article) {

							//--------------------------------------------------
							// Insert and update values

								$article['title'] = html_decode($article['title']);

								$values_update = $article;
								$values_update['source_id'] = $source_id;
								$values_update['updated'] = date('Y-m-d H:i:s');

								$values_insert = $values_update;
								$values_insert['created'] = date('Y-m-d H:i:s');

							//--------------------------------------------------
							// Published date

								$published = strtotime($article['published']);

								if ($published === false) {

									$values_insert['published'] = date('Y-m-d H:i:s');

									unset($values_update['published']);

								} else {

									$values_insert['published'] = date('Y-m-d H:i:s', $published);
									$values_update['published'] = date('Y-m-d H:i:s', $published);

								}

							//--------------------------------------------------
							// Store

								$db->insert(DB_PREFIX . 'source_article', $values_insert, $values_update);

						}

					//--------------------------------------------------
					// Record as updated

						if ($error) {
							$values = array(
									'error_text' => $error,
									'error_date' => date('Y-m-d H:i:s'),
								);
						} else {
							$values = array(
									'article_count' => count($source_articles),
									'updated' => date('Y-m-d H:i:s'),
								);
						}

						$where_sql = '
							id = "' . $db->escape($source_id) . '" AND
							deleted = "0000-00-00 00:00:00"';

						$db->update(DB_PREFIX . 'source', $values, $where_sql);

				}

		}

	}

?>