<?php

//--------------------------------------------------
// Resources

	$this->css_add('/a/css/global/core.css');

?>
<!DOCTYPE html>
<html lang="<?= html($this->lang_get()) ?>" xml:lang="<?= html($this->lang_get()) ?>" xmlns="http://www.w3.org/1999/xhtml">
<head>

	<?= $this->head_get_html(); ?>

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

</head>
<body id="<?= html($this->page_id_get()) ?>">

	<div id="page_wrapper">

		<header id="page_title" role="banner">
			<h1><a href="/">Reader</a></h1>
		</header>

		<main id="page_content" role="main">









<!-- END OF PAGE TOP -->

	<?= $this->message_get_html(); ?>

	<?= $this->view_get_html(); ?>

<!-- START OF PAGE BOTTOM -->









		</main>

	</div>

	<?= $this->foot_get_html(); ?>

</body>
</html>