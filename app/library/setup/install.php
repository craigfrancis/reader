<?php

	if (defined('PRIVATE_ROOT') && defined('SERVER') && SERVER == 'live') {
		if (is_file(PRIVATE_ROOT . '/database.txt')) {

			$database_password = file_get_contents(PRIVATE_ROOT . '/database.txt');

			echo 'Private: ' . $database_password . "\n";

		}
	}

?>