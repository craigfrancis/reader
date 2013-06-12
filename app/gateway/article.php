<?php

//--------------------------------------------------
// Variables

	$article_id = request('id');

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
			$title = $image->getAttribute('title');
			if (!$title) {
				$title = $image->getAttribute('alt');
			}
			if ($title) {

				$title_node = $article_dom->createElement('p', $title);
				$title_node->setAttribute('class', 'img_title');

				$image->parentNode->insertBefore($title_node, $image->nextSibling);

			}
		}

		// $article_html = $article_dom->saveXML();

		$article_html = '';

		$body = $article_dom->documentElement->firstChild;
		if ($body->hasChildNodes()) {
			foreach ($body->childNodes as $node) {
				$article_html .= $article_dom->saveXML($node);
			}
		}

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

	<base target="_blank" />

</head>
<body id="p_articles">

	<div id="article_wrapper" class="<?= html($article_source) ?>">
		<h1><a href="<?= html($article_link) ?>"><?= html($article_title) ?></a></h1>
		<div>
			<?= $article_html . "\n" ?>
		</div>
		<p class="published"><?= html(date('l jS F Y, g:ia', strtotime($article_published))) ?></p>
	</div>

</body>
</html>