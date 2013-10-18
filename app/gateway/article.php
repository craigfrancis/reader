<?php

//--------------------------------------------------
// Variables

	$article_id = request('id');

//--------------------------------------------------
// Disable NewRelic

	if (extension_loaded('newrelic')) {
		newrelic_disable_autorum();
	}

//--------------------------------------------------
// Require login

	if (!USER_LOGGED_IN) {
		redirect(url('/'));
	}

//--------------------------------------------------
// Details

	$db = db_get();

	$sql = 'SELECT
				sa.title,
				sa.link,
				sa.published,
				sa.description,
				s.ref AS source_ref
			FROM
				' . DB_PREFIX . 'source_article AS sa
			LEFT JOIN
				' . DB_PREFIX . 'source AS s ON s.id = sa.source_id
			WHERE
				sa.id = "' . $db->escape($article_id) . '" AND
				s.deleted = "0000-00-00 00:00:00"';

	if ($row = $db->fetch($sql)) {

		$article_title = $row['title'];
		$article_link = $row['link'];
		$article_published = $row['published'];
		$article_html = $row['description'];
		$article_source = $row['source_ref'];

	} else {

		exit_with_error('Cannot find article "' . $article_id . '"');

	}

//--------------------------------------------------
// Read or unread

	$article_read = request('read');

	if ($article_read == 'true') {

		$values = array(
				'article_id' => $article_id,
				'user_id' => USER_ID,
				'read_date' => date('Y-m-d H:i:s'),
			);

		$db->insert(DB_PREFIX . 'source_article_read', $values, $values);

	} else if ($article_read == 'false') {

		$db->query('DELETE FROM
						' . DB_PREFIX . 'source_article_read
					WHERE
						article_id = "' . $db->escape($article_id) . '" AND
						user_id = "' . $db->escape(USER_ID) . '"');

	}

//--------------------------------------------------
// Expose image title attributes as paragraphs

	$article_html = trim($article_html);

	if ($article_html != '') {

		libxml_use_internal_errors(true);

		$article_dom = new DomDocument();
		$article_dom->loadHTML('<?xml encoding="UTF-8">' . $article_html);

		$images = $article_dom->getElementsByTagName('img');
		foreach ($images as $image) {

			$wrapper_node = $article_dom->createElement('span');
			$wrapper_node->setAttribute('class', 'image_wrapper');

			$image->parentNode->replaceChild($wrapper_node, $image);

			$wrapper_node->appendChild($image);

			$title = $image->getAttribute('title');
			if (!$title) {
				$title = $image->getAttribute('alt');
			}

			if ($title) {
				$title_node = $article_dom->createElement('em', $title);
				$wrapper_node->appendChild($title_node);
			}

		}

		foreach (array('script', 'link') as $tag) {

			$nodes = $article_dom->getElementsByTagName($tag);

			for ($k = ($nodes->length - 1); $k >= 0; $k--) { // For each will skip nodes

				$node = $nodes->item($k);

				$src = $node->getAttribute('src');
				if (!$src) {
					$src = $node->getAttribute('href');
				}

				if ($src) {
					$replacement_node = $article_dom->createElement('a', '<' . $tag . '>');
					$replacement_node->setAttribute('href', $src);
				} else {
					$replacement_node = $article_dom->createElement('span', '<' . $tag . '>');
				}

				$replacement_node->setAttribute('class', $tag . '_tag');
				$replacement_node->setAttribute('title', $src);

				$node->parentNode->replaceChild($replacement_node, $node);

			}

		}

		foreach (array('iframe') as $tag) {

			$nodes = $article_dom->getElementsByTagName($tag);

			for ($k = ($nodes->length - 1); $k >= 0; $k--) { // For each will skip nodes

				$node = $nodes->item($k);

				$node->appendChild($article_dom->createTextNode('')); // Convert <tag /> to <tag></tag>

			}

		}

		$xpath = new DOMXPath($article_dom);
		foreach ($xpath->query('//[style]') as $element) {
			// $element->removeAttributeNode('style');
			debug($element->nodeValue);
		}

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
// Body class

	$body_class = $article_source;

	if (request('inline') == 'true') {
		$body_class .= ' inline';
	}

//--------------------------------------------------
// Output

?>
<!DOCTYPE html>
<html lang="en-GB" xml:lang="en-GB" xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta charset="UTF-8" />

	<title>Article</title>

	<link rel="shortcut icon" type="image/x-icon" href="/a/img/global/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?= html(version_path('/a/css/global/article.css')) ?>" media="all" />

	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> -->
	<meta name="viewport" content="initial-scale=1" />

	<base target="_blank" />

</head>
<body id="p_articles" class="<?= html($body_class) ?>">

	<div id="article_wrapper" class="<?= html($article_source) ?>">
		<h1><a href="<?= html($article_link) ?>"><?= html($article_title) ?></a></h1>
		<div>
			<?= $article_html . "\n" ?>
		</div>
		<p class="article_info">
			<a href="<?= html($article_link) ?>">View</a> |
			<span><?= html(date('l jS F Y, g:ia', strtotime($article_published))) ?></span>
		</p>
	</div>

</body>
</html>