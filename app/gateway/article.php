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

		$scripts = $article_dom->getElementsByTagName('script');
		foreach ($scripts as $script) {

$tmp_dom = new DOMDocument();
$tmp_dom->appendChild($tmp_dom->importNode($script, true));
echo $tmp_dom->saveHTML() . "\n\n";

			$src = $script->getAttribute('src');

			if ($src) {
				$replacement_node = $article_dom->createElement('a', '<script>');
				$replacement_node->setAttribute('href', $src);
			} else {
				$replacement_node = $article_dom->createElement('span', '<script>');
			}

			$replacement_node->setAttribute('class', 'script_tag');
			$replacement_node->setAttribute('title', $src);

			$script->parentNode->replaceChild($replacement_node, $script);

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

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

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