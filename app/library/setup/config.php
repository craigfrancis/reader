<?php

//--------------------------------------------------
// Encryption key

	define('ENCRYPTION_KEY', 'bUwLgw+Q8NA4mQ==');

//--------------------------------------------------
// Server specific

	if (preg_match('/^\/(Library|Volumes)\//i', ROOT)) {

		//--------------------------------------------------
		// Server

			define('SERVER', 'stage');

		//--------------------------------------------------
		// Database

			$config['db.host'] = 'localhost';
			$config['db.user'] = 'stage';
			$config['db.pass'] = 'st8ge';
			$config['db.name'] = 's-craig-reader';

			$config['db.prefix'] = 'rdr_';

		//--------------------------------------------------
		// Email

			$config['email.from_email'] = 'craig@craigfrancis.co.uk';
			$config['email.testing'] = 'craig@craigfrancis.co.uk';
			$config['email.check_domain'] = false;

		//--------------------------------------------------
		// Misc

			$config['gateway.maintenance'] = true;

	} else if (prefix_match('/www/demo/', ROOT)) {

		//--------------------------------------------------
		// Server

			define('SERVER', 'demo');

	} else {

		//--------------------------------------------------
		// Server

			define('SERVER', 'live');

		//--------------------------------------------------
		// Database

			$config['db.host'] = '[[db.host]]'; // Hide from source control
			$config['db.user'] = '[[db.user]]';
			$config['db.pass'] = '[[db.pass]]';
			$config['db.name'] = '[[db.name]]';

			$config['db.prefix'] = 'rdr_';

		//--------------------------------------------------
		// Email

			$config['email.from_email'] = '[[email.default]]';
			$config['email.testing'] = '[[email.default]]';
			$config['email.error'] = '[[email.default]]';

		//--------------------------------------------------
		// General

			$config['output.protocols'] = array('https');
			$config['output.domain'] = '[[output.domain]]';

	}

//--------------------------------------------------
// Output

	$config['output.site_name'] = 'Reader';
	$config['output.tracking'] = false; // Disable NewRelic

//--------------------------------------------------
// Content security policy

	$config['output.csp_enabled'] = true;
	$config['output.csp_enforced'] = true;

	$config['output.csp_directives'] = array(
			'default-src' => array(
					"'none'",
				),
			'img-src' => array(
					"*",
				),
			'script-src' => array(
					"'self'",
				),
			'style-src' => array(
					"'self'",
				),
		);

//--------------------------------------------------
// Upload

	$config['upload.demo.source'] = 'git';
	$config['upload.demo.location'] = 'fey:/www/demo/craig.reader';

	$config['upload.live.source'] = 'demo';
	$config['upload.live.location'] = 'fey:/www/live/craig.reader';

?>