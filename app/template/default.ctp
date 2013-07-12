<?php

//--------------------------------------------------
// Resources

	$response->css_add('/a/css/global/core.css');

?>
<!DOCTYPE html>
<html lang="<?= html($response->lang_get()) ?>" xml:lang="<?= html($response->lang_get()) ?>" xmlns="http://www.w3.org/1999/xhtml">
<head>

	<?= $response->head_get_html(); ?>

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

	<link rel="apple-touch-icon" href="/a/img/global/favicon.png" />

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

</head>
<body id="<?= html($response->page_id_get()) ?>">

	<div id="page_wrapper">

		<header id="page_title" role="banner">
			<h1><a href="/">Reader</a></h1>
		</header>

		<main id="page_content" role="main">









<!-- END OF PAGE TOP -->

	<?= $response->message_get_html(); ?>

	<?= $response->view_get_html(); ?>

<!-- START OF PAGE BOTTOM -->









		</main>

		<?php if (isset($footer_urls)) { ?>

			<footer id="page_footer" role="contentinfo">
				<?php
					foreach ($footer_urls as $footer_url) {
						if ($footer_url['href']) {
							echo '
								<a href="' . html($footer_url['href']) . '" class="' . html($footer_url['class']) . '"><span>' . html($footer_url['text']) . '</span></a>';
						} else {
							echo '
								<span class="' . html($footer_url['class']) . '"><span>' . html($footer_url['text']) . '</span></span>';
						}
					}
				?>
			</footer>

		<?php } ?>

	</div>

	<?= $response->foot_get_html(); ?>

</body>
</html>