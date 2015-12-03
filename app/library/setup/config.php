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

			$config['db.host'] = 'PRIVATE'; // Hide from source control, see /private/config/live.ini
			$config['db.user'] = 'PRIVATE';
			$config['db.pass'] = 'PRIVATE';
			$config['db.name'] = 'PRIVATE';

			$config['db.prefix'] = 'rdr_';

		//--------------------------------------------------
		// Email

			$config['email.from_email'] = 'PRIVATE';
			$config['email.testing'] = 'PRIVATE';
			$config['email.error'] = 'PRIVATE';

		//--------------------------------------------------
		// General

			$config['output.protocols'] = array('https');
			$config['output.domain'] = 'PRIVATE';

	}

//--------------------------------------------------
// Output

	$config['output.site_name'] = 'Reader';
	$config['output.js_min'] = (SERVER != 'stage');
	$config['output.css_min'] = (SERVER != 'stage');
	$config['output.timestamp_url'] = true;
	$config['output.tracking'] = false; // Disable NewRelic

//--------------------------------------------------
// Security

	$config['output.framing'] = 'DENY'; // or SAMEORIGIN
	$config['output.xss_reflected'] = 'block';

	$config['output.csp_enabled'] = true;
	$config['output.csp_enforced'] = true;
	$config['output.csp_directives'] = array(
			'default-src'  => array("'none'"),
			'form-action'  => array("'self'"),
			'style-src'    => array("'self'"),
			'img-src'      => array("*"),
			'media-src'    => array("*"),
			'script-src'   => array("'self'", 'https://www.google-analytics.com'),
			'connect-src'  => array(
					'www.devcf.com', // For to-do list
				),
		);

	$config['socket.insecure_domains'] = 'all'; // Too many issues with other websites.

//--------------------------------------------------
// Upload

	$config['upload.demo.source'] = 'git';
	$config['upload.demo.location'] = 'fey:/www/demo/craig.reader';

	$config['upload.live.source'] = 'demo';
	$config['upload.live.location'] = 'fey:/www/live/craig.reader';

?>