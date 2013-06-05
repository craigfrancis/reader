<?php

//--------------------------------------------------
// Articles

	$articles = array();

	$articles['comics'][] = array(
			'title' => 'Comic 1',
			'id' => 2,
			'url' => strval(url('/articles/:source/', array('source' => 'comics', 'id' => 2))),
		);

	$articles['comics'][] = array(
			'title' => 'Comic 2',
			'id' => 3,
			'url' => strval(url('/articles/:source/', array('source' => 'comics', 'id' => 3))),
		);

	$articles['comics'][] = array(
			'title' => 'Comic 3',
			'id' => 4,
			'url' => strval(url('/articles/:source/', array('source' => 'comics', 'id' => 4))),
		);

	$articles['alistapart'][] = array(
			'title' => 'My article',
			'id' => 9,
			'url' => strval(url('/articles/:source/', array('source' => 'alistapart', 'id' => 9))),
		);

//--------------------------------------------------
// Response

	$response = response_get('file');
	$response->mime_set('application/json');
	$response->inline_set(true);
	$response->content_set(json_encode($articles));
	$response->send();

?>