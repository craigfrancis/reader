
	<?php if ($action_edit) { ?>

		<p>Use the form below to edit the <strong><?= html($source_title) ?></strong> source (<a href="<?= html($delete_url) ?>">delete</a>).</p>

	<?php } else { ?>

		<p>Use the form below to create a new source.</p>

	<?php } ?>

	<?= $form->html(); ?>
