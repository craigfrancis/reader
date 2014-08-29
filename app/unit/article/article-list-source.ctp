
	<ul id="reader_list">
		<?php foreach ($articles as $article) { ?>

			<li><?= ($article['new'] ? 'New: ' : '') ?><a href="<?= html($article['url']) ?>"><?= html($article['title']) ?></a></li>

		<?php } ?>
		<?php if (count($articles) == 0) { ?>

			<li class="no_articles">No articles found</li>

		<?php } ?>
	</ul>
