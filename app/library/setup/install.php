<?php

	if (defined('SERVER') && SERVER == 'live') {

		$values_path = PRIVATE_ROOT . '/config.ini';
		$config_path = APP_ROOT . '/library/setup/config.php';

		if (is_file($values_path)) {

			$config_contents = file_get_contents($config_path);

			foreach (parse_ini_file($values_path) as $key => $value) {
				$config_contents = str_replace('[[' . $key . ']]', $value, $config_contents);
			}

			file_put_contents($config_path, $config_contents);

		} else {

			echo 'Cannot find config ini file: ' . $values_path . "";

		}

	}

?>