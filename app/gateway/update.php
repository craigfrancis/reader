<?php

//--------------------------------------------------
// Update

	$source_id = request('source');

	articles::update($source_id === 'all' ? true : intval($source_id));

	articles::local_cache();

	articles::image_cleanup();

//--------------------------------------------------
// Redirect

	$dest = request('dest');

	if (substr($dest, 0, 1) == '/') { // Scheme-relative URL "//example.com" won't work, the domain is prefixed.
		redirect($dest);
	}

//--------------------------------------------------
// Done

	exit("Done\n");

?>