<?php

//--------------------------------------------------
// Sources

	$db = db_get();

	$return = array();

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

		$return[$row['ref']] = array(
				'url' => strval(url('/articles/:source/', array('source' => $row['ref']))),
				'name' => $row['title'],
				'articles' => array(),
			);

	}

//--------------------------------------------------
// Articles

	$return['garfield']['articles'][] = array(
			'title' => 'Comic 1',
			'id' => 2,
			'url' => strval(url('/articles/:source/', array('source' => 'garfield', 'id' => 2))),
		);

	$return['garfield']['articles'][] = array(
			'title' => 'Comic 2',
			'id' => 3,
			'url' => strval(url('/articles/:source/', array('source' => 'garfield', 'id' => 3))),
		);

	$return['garfield']['articles'][] = array(
			'title' => 'Comic 3',
			'id' => 4,
			'url' => strval(url('/articles/:source/', array('source' => 'garfield', 'id' => 4))),
		);

	$return['dzone']['articles'][] = array(
			'title' => 'My article',
			'id' => 9,
			'url' => strval(url('/articles/:source/', array('source' => 'dzone', 'id' => 9))),
		);

//--------------------------------------------------
// Response

	$response = response_get('file');
	$response->mime_set('application/json');
	$response->inline_set(true);
	$response->content_set(json_encode($return));
	$response->send();

?>