
	<div class="source_list">
		<ol>
			<?php foreach ($sources as $source) { ?>

				<li><a href="<?= html($source['url']) ?>"><?= html($source['title']) ?></a><?= ($source['error'] ? ' <abbr class="error" title="Error">*</abbr>' : '') ?></li>

			<?php } ?>
		</ol>
	</div>

	<p><a href="<?= html($add_url) ?>">Add source</a></p>
