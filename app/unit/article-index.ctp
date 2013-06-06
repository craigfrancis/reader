
	<ul id="reader_index">
		<?php foreach ($sources as $source) { ?>

			<li><a href="<?= html($source['url']) ?>" data-source="<?= html($source['ref']) ?>"><?= html($source['name']) ?></a> <em><?= html($source['count']) ?></em></li>

		<?php } ?>
	</ul>
