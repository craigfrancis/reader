<?php

//--------------------------------------------------
// Articles

	$db = db_get();

	$return = array();

	$sql = 'SELECT
				s.id,
				s.ref,
				s.title,
				sa.id AS article_id,
				sa.title AS article_title,
				sa.link AS article_link
			FROM
				' . DB_PREFIX . 'source AS s
			LEFT JOIN
				' . DB_PREFIX . 'source_article AS sa ON sa.source_id = s.id
			LEFT JOIN
				' . DB_PREFIX . 'source_article_read AS sar ON sar.article_id = sa.id AND sar.user_id = "' . $db->escape(USER_ID) . '"
			WHERE
				s.deleted = "0000-00-00 00:00:00" AND
				sar.article_id IS NULL
			ORDER BY
				s.sort,
				sa.published ASC';

	foreach ($db->fetch_all($sql) as $row) {

		if (!isset($return[$row['id']])) {

			$return[$row['id']] = array(
					'url' => strval(url('/articles/:source/', array('source' => $row['ref']))),
					'name' => $row['title'],
					'articles' => array(),
				);

		}

		$return[$row['id']]['articles'][] = array(
				'title' => $row['article_title'],
				'id' => $row['article_id'],
				'url' => strval(url('/articles/:source/', array('source' => $row['ref'], 'id' => $row['article_id']))),
				'link' => $row['article_link'],
			);

	}

//--------------------------------------------------
// Response

	$response = response_get('file');
	$response->mime_set('application/json');
	$response->inline_set(true);
	$response->content_set(json_encode($return));
	$response->send();

?>