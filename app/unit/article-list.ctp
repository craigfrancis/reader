
	<ul id="reader_list">
		<?php foreach ($articles as $article) { ?>

			<li><a href="<?= html($article['url']) ?>"><?= html($article['name']) ?></a></li>

		<?php } ?>
	</ul>
