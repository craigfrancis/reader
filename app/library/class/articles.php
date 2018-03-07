<?php

	class articles extends check {

		private static $browser_user_agent = 'RSS Reader (github.com/craigfrancis/reader)';

		static function img_local_url($article_id, $img_url) {

			if (prefix_match('http://da.feedsportal.com/r/', $img_url) || prefix_match('http://pi.feedsportal.com/r/', $img_url) || prefix_match('http://dmp.adform.net/dmp/profile/', $img_url)) {
				 return '/a/img/global/blank.gif';
			}

			$local_path = self::img_local_path($article_id, $img_url);
			if ($local_path !== NULL && is_file($local_path)) {
				return FILE_URL . prefix_replace(FILE_ROOT, '', $local_path);
			} else {
				return NULL;
			}

		}

		static function img_local_path($article_id, $img_url) {

			$path = FILE_ROOT . '/article-images/original/' . intval($article_id) . '/' . safe_file_name(hash('sha256', $img_url));

			if (preg_match('/\.(png|jpg|jpeg|gif)($|\?)/', $img_url, $matches)) {
				$path .= '.' . $matches[1];
			}

			return $path;

		}

		static function img_remote_url($article_domain, $img_url) {

			if (substr($img_url, 0, 2) == '//') {

				$img_url = 'http:' . $img_url; // Most won't be https

			} else if (substr($img_url, 0, 1) == '/') { // e.g. what-if.xkcd.com

				$img_url = $article_domain . $img_url;

			}

			return $img_url;

		}

		static function local_cache($article_id = NULL, $debug = false) {

			//--------------------------------------------------
			// Config

				$db = db_get();

				$browser = new socket_browser();
				$browser->exit_on_error_set(false);
				$browser->user_agent_set(self::$browser_user_agent);

				libxml_use_internal_errors(true);

			//--------------------------------------------------
			// Articles

				$article_id = intval($article_id);

				$parameters = array();

				if ($article_id > 0) {
					$where_sql = 'sa.id = ?';
					$parameters[] = array('i', $article_id);
				} else {
					$where_sql = 'sa.link_clean = ""';
				}

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
							' . $where_sql . '
						ORDER BY
							sa.published
						LIMIT
							20';

				foreach ($db->fetch_all($sql, $parameters) as $row) {

					//--------------------------------------------------
					// Details

						$article_id = $row['id'];
						$article_link_source = trim($row['link_source']);
						$article_html = trim($row['description']);
						$source_url = $row['url_http'];

					//--------------------------------------------------
					// New browser (drop cookies, referrer, etc)

						$browser->reset();

					//--------------------------------------------------
					// Clean link

						$result = $browser->get($article_link_source);

						$article_link_code = $browser->code_get();
						$article_link_clean = trim($browser->url_get());

						$article_domain = $source_url;

						if ($article_link_code != 200) {

							$report  = 'Got a "' . $article_link_code . '" response when getting a clean URL';
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $article_link_source;
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $article_link_clean;
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $browser->request_full_get();
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $browser->response_headers_get();
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $browser->error_message_get();
							$report .= "\n\n--------------------------------------------------\n\n";
							$report .= $browser->error_details_get();
							$report .= "\n\n--------------------------------------------------\n\n";

							report_add($report);

							$article_link_clean = '-';

						} else if ($article_link_clean == '') {

							$article_link_clean = $article_link_source;

							report_add('Cannot return clean URL for: ' . $article_link_source);

						} else {

							$article_domain = $article_link_clean;

						}

						$sql = 'UPDATE
									' . DB_PREFIX . 'source_article AS sa
								SET
									sa.link_clean = ?
								WHERE
									sa.id = ?';

						$parameters = array();
						$parameters[] = array('s', $article_link_clean);
						$parameters[] = array('i', $article_id);

						$db->query($sql, $parameters);

					//--------------------------------------------------
					// Article domain

						$article_domain = preg_replace('/^(https?:\/\/[^\/]+).*/', '$1', $article_domain);

					//--------------------------------------------------
					// Images

						if ($article_html != '') {

							$article_dom = new DomDocument();
							$article_dom->loadHTML('<?xml encoding="UTF-8">' . $article_html);

							$images = $article_dom->getElementsByTagName('img');
							for ($k = ($images->length - 1); $k >= 0; $k--) {

								$image = $images->item($k);

								$img_url = trim($image->getAttribute('src'));
								if ($img_url) {

									$remote_url = self::img_remote_url($article_domain, $img_url);
									$local_path = self::img_local_path($article_id, $remote_url);

									if ($local_path !== NULL && !is_file($local_path)) {

										// $remote_data = file_get_contents($remote_url);

										$browser->get($remote_url);

										$remote_code = $browser->code_get();
										$remote_data = $browser->data_get();

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

											$image_info = @getimagesize($local_path);
											if ($image_info === false) {
												unlink($local_path);
											}

										}

									} else {

										$remote_code = NULL;
										$remote_data = NULL;
										$image_info = (is_file($local_path) ? @getimagesize($local_path) : NULL);

									}

									if ($debug) {

										debug(array(
												'article_domain' => $article_domain,
												'img_url' => $img_url,
												'remote_url' => $remote_url,
												'remote_code' => $remote_code,
												'remote_data' => ($remote_data === NULL ? NULL : strlen($remote_data) . 'b, sha1 = ' . sha1($remote_data)),
												'local_path' => $local_path,
												'image_info' => $image_info,
											));

									}

								}

							}

						}

				}

		}

		static function update($condition = NULL) {

			//--------------------------------------------------
			// Config

				$db = db_get();

				$now = new timestamp();

				$browser = new socket_browser();
				$browser->exit_on_error_set(false);

				libxml_use_internal_errors(true);

			//--------------------------------------------------
			// Condition

				$parameters = array();

				if (is_numeric($condition)) { // A particular source

					$where_sql = '
						s.id = ? AND
						s.deleted = "0000-00-00 00:00:00"';

					$parameters[] = array('i', $condition);

				} else if ($condition === true) { // All sources

					$where_sql = '
						s.deleted = s.deleted';

				} else { // Those not recently updated

					$updated_limit = new timestamp('-10 minutes');
					$error_limit = new timestamp('-1 hour');

					$where_sql = '
						s.updated    <= ? AND
						s.error_date <= ? AND
						s.deleted = "0000-00-00 00:00:00"';

					$parameters[] = array('s', $updated_limit);
					$parameters[] = array('s', $error_limit);

				}

			//--------------------------------------------------
			// For each source

				$sql = 'SELECT
							s.id,
							s.url_feed,
							s.article_count,
							s.deleted
						FROM
							' . DB_PREFIX . 'source AS s
						WHERE
							' . $where_sql;

				foreach ($db->fetch_all($sql, $parameters) as $row) {

					//--------------------------------------------------
					// Details

						$error = false;

						$source_id = $row['id'];
						$source_url = $row['url_feed'];
						$source_deleted = ($row['deleted'] != '0000-00-00 00:00:00');
						$source_articles = array();

						$article_count = intval($row['article_count']);
						if ($article_count < 30) {
							$article_count = 30;
						}
						$article_count += 10; // Bit of tolerance

					//--------------------------------------------------
					// Delete old articles

						if ($source_deleted) {

							$sql = 'SELECT
										sa.id
									FROM
										' . DB_PREFIX . 'source_article AS sa
									WHERE
										sa.source_id = ?';

							$parameters = array();
							$parameters[] = array('i', $source_id);

						} else {

								// Delete by "sar.read_date" (not "sa.published"), as websites
								// like Coding Horror like to change their GUID.

							$read_limit = new timestamp('-2 weeks');

							$sql = 'SELECT
										sa.id
									FROM
										' . DB_PREFIX . 'source_article AS sa
									LEFT JOIN
										' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id
									WHERE
										sa.source_id = ? AND
										sar.read_date <= ? AND
										sar.read_date IS NOT NULL
									ORDER BY
										sar.read_date DESC
									LIMIT
										' . intval($article_count) . ', 100000';

							$parameters = array();
							$parameters[] = array('i', $source_id);
							$parameters[] = array('i', $read_limit);

						}

						foreach ($db->fetch_all($sql, $parameters) as $row) {

							$cache_dir = FILE_ROOT . '/article-images/original/' . intval($row['id']) . '/';
							if (is_dir($cache_dir)) {
								rrmdir($cache_dir);
							}

							$sql = 'DELETE FROM
										' . DB_PREFIX . 'source_article
									WHERE
										id = ?';

							$parameters = array();
							$parameters[] = array('i', $row['id']);

							$db->query($sql, $parameters);

						}

					//--------------------------------------------------
					// Delete 'read' records for removed articles

						$db->query('DELETE sar FROM
										' . DB_PREFIX . 'source_article_read AS sar
									LEFT JOIN
										' . DB_PREFIX . 'source_article AS sa ON sa.id = sar.article_id
									WHERE
										sa.id IS NULL');

					//--------------------------------------------------
					// Return new articles

						if (!$source_deleted) {

							//--------------------------------------------------
							// Get XML ... don't do directly in simple xml as
							// FeedBurner has issues

									//--------------------------------------------------
									// Disabled as jakearchibald.com cannot return more
									// than 81701 bytes of data without GZip (nginx issue?).
									// It also means we can use GZip though :-)
									//
									// $headers = array(
									// 		'User-Agent: RSS Reader',
									// 		'Accept: application/rss+xml',
									// 	);
									//
									// $context = stream_context_create(array(
									// 		'http' => array(
									// 				'method' => 'GET',
									// 				'header' => implode("\r\n", $headers) . "\r\n",
									// 			)
									// 	));
									//
									// $rss_data = @file_get_contents($source_url, false, $context);
									//
									//--------------------------------------------------

								$browser->reset();
								$browser->encoding_accept_set('gzip', true);
								$browser->header_set('User-Agent', self::$browser_user_agent); // Not user_agent_set(), as we don't want the headers: accept, accept-language, cache-control, pragma.
								$browser->header_set('Accept', 'application/rss+xml');
								$browser->get($source_url);

								if ($browser->code_get() == 200) {

									$rss_data = $browser->data_get();

									if (trim($rss_data) == '') {
										$error = 'Cannot return feed';
									}

								} else {

									$error = 'Cannot return feed: ' . $browser->error_message_get() . "\n\n" . $browser->error_details_get();

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
										$values_update['updated'] = $now;

										$values_insert = $values_update;
										$values_insert['created'] = $now;

									//--------------------------------------------------
									// Published date

										try {

											$published = new timestamp($article['published']); // Not 'db' format, could be anything from RSS
											$published = $published->format('db');

											$values_insert['published'] = $published;
											$values_update['published'] = $published;

										} catch (Exception $e) {

											$values_insert['published'] = $now;

											unset($values_update['published']);

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
											'error_date' => $now,
										);
								} else {
									$values = array(
											'article_count' => count($source_articles),
											'updated' => $now,
										);
								}

								$where_sql = '
									id = "' . $db->escape($source_id) . '" AND
									deleted = "0000-00-00 00:00:00"';

								$db->update(DB_PREFIX . 'source', $values, $where_sql);

						}

				}

		}

	}

?>