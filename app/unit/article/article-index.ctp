
	<ul id="reader_index">
		<?php foreach ($sources as $source) { ?>

			<li><a href="<?= html($source['url']) ?>" data-source="<?= html($source['ref']) ?>"><?= html($source['name']) ?></a> <em><?= html($source['count']) ?></em></li>

		<?php } ?>
		<?php if (isset($read_url)) { ?>

			<li class="read"><a href="<?= html($read_url) ?>">Read articles</a></li>

		<?php } ?>
	</ul>
