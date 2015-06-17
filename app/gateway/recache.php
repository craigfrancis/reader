<?php

//--------------------------------------------------
// Update

	$article_id = request('article');

	articles::local_cache($article_id);

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