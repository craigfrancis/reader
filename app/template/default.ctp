<?php

//--------------------------------------------------
// Resources

	$response->css_add('/a/css/global/core.css');

	$response->link_set('apple-touch-icon', '/a/img/global/favicon.png');

?>
<!DOCTYPE html>
<html lang="<?= html($response->lang_get()) ?>" xml:lang="<?= html($response->lang_get()) ?>" xmlns="http://www.w3.org/1999/xhtml">
<head>

	<?= $response->head_get_html(); ?>

	<meta name="viewport" content="width=device-width, initial-scale=1" />

</head>
<body id="<?= html($response->page_id_get()) ?>">

	<div id="page_wrapper">

		<header id="page_header" role="banner">
			<h1><a href="/">Reader</a></h1>
		</header>

		<main id="page_content" role="main">









<!-- END OF PAGE TOP -->

	<?php if (isset($title_html)) { ?>

		<div id="page_title">
			<?= $title_html . "\n" ?>
		</div>

	<?php } ?>

	<?= $response->message_get_html(); ?>

	<?= $response->view_get_html(); ?>

<!-- START OF PAGE BOTTOM -->









		</main>

		<?php if (isset($footer_urls)) { ?>

			<footer id="page_footer" role="contentinfo">
				<?php
					foreach ($footer_urls as $footer_url) {

						if (isset($footer_url['text'])) {
							$label_html = html($footer_url['text']);
						} else {
							$label_html = $footer_url['html'];
						}

						if ($footer_url['href']) {
							echo '
								<a href="' . html($footer_url['href']) . '" class="' . html($footer_url['class']) . '"' . (isset($footer_url['target']) ? ' target="' . html($footer_url['target']) . '"' : '') . '' . (isset($footer_url['label']) ? ' aria-label="' . html($footer_url['label']) . '"' : '') . '><span>' . $label_html . '</span></a>';
						} else {
							echo '
								<span class="' . html($footer_url['class']) . '"><span>' . $label_html . '</span></span>';
						}

					}
				?>
			</footer>

		<?php } ?>

	</div>

	<?= $response->foot_get_html(); ?>

</body>
</html>