
	<ul id="reader_index">
		<?php foreach ($sources as $source) { ?>

			<li data-source="<?= html($source['ref']) ?>"><a href="<?= html($source['url']) ?>"><?= html($source['name']) ?></a> <em><?= html($source['count']) ?></em></li>

		<?php } ?>
	</ul>
