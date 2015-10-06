<?php

//--------------------------------------------------
// Update

	$article_id = request('article');
	$debug = (request('debug') == 'true');

	articles::local_cache($article_id, $debug);

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