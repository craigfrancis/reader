<?php

	class articles_job extends job {

		public function should_run() {
			return true;
		}

		public function email_addresses_get() {
			return array(
					'stage' => array(
							'craig@craigfrancis.co.uk',
						),
					'demo' => array(
							'craig@craigfrancis.co.uk',
						),
					'live' => array(
							'craig@craigfrancis.co.uk',
						),
				);
		}

		public function run() {

			//--------------------------------------------------
			// Setup

				$db = db_get();

			//--------------------------------------------------
			// New articles

				$source_id = NULL;

				if ($source_id !== NULL) {

					$where_sql = '
						s.id = "' . $db->escape($source_id) . '" AND
						s.deleted = "0000-00-00 00:00:00"';

				} else {

					$where_sql = '
						s.updated    <= "' . $db->escape(date('Y-m-d H:i:s', strtotime('-10 minutes'))) . '" AND
						s.error_date <= "' . $db->escape(date('Y-m-d H:i:s', strtotime('-1 day'))) . '" AND
						s.deleted = "0000-00-00 00:00:00"';

				}

				$sql = 'SELECT
							s.id,
							s.url_feed
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

					//--------------------------------------------------
					// Delete old articles

						$db->query('DELETE FROM
										' . DB_PREFIX . 'source_article
									WHERE
										id IN (
												SELECT
													*
												FROM (
														SELECT
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
															sa.published DESC
														LIMIT
															30, 100000
													) AS x
											)'); // Extra sub query required due to lack of support for "LIMIT" with "IN" (feature to be added to MySQL later)

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

							$rss_xml = @simplexml_load_string($rss_data);

							if ($rss_xml === false) {
								$error = 'Cannot parse feed';
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
											'link'        => strval($item->link),
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

									$source_articles[] = array(
											'guid'        => strval($entry->id),
											'title'       => strval($entry->title),
											'link'        => strval($entry->link['href']),
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
									'updated' => date('Y-m-d H:i:s'),
								);
						}

						$where_sql = '
							id = "' . $db->escape($source_id) . '" AND
							deleted = "0000-00-00 00:00:00"';

						$db->update(DB_PREFIX . 'source', $values, $where_sql);

				}

			//--------------------------------------------------
			// Cleanup

				$db->query('DELETE sar FROM
								' . DB_PREFIX . 'source_article_read AS sar
							LEFT JOIN
								' . DB_PREFIX . 'source_article AS sa ON sa.id = sar.article_id
							WHERE
								sa.id IS NULL');

		}

	}

?>