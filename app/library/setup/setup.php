<?php

//--------------------------------------------------
// User

	$user = new user();
	$user->session_start();

	define('USER_ID', $user->id_get());
	define('USER_LOGGED_IN', (USER_ID > 0));

	if (USER_LOGGED_IN) {

		$user_details = $user->values_get(array('username', 'delay'));

		if ($user_details['delay'] == '1-day') {
			$user_delay = new timestamp('00:00:00');
		} else {
			$user_delay = new timestamp();
		}

		define('USER_NAME', $user_details['username']);
		define('USER_DELAY', $user_delay->format('db'));

		config::array_set('debug.values', 'User', USER_NAME . ' (' . USER_ID . ')');

	} else {

		define('USER_NAME', NULL);
		define('USER_DELAY', NULL);

	}

	config::set('user', $user);

//--------------------------------------------------
// Source error

	function source_error() {

		$db = db_get();

		$error_limit = new timestamp('-6 hours');

		$sql = 'SELECT
					1
				FROM
					' . DB_PREFIX . 'source AS s
				WHERE
					s.error_date >= s.updated AND
					s.updated <= ? AND
					s.deleted = "0000-00-00 00:00:00"
				LIMIT
					1';

		$parameters = array();
		$parameters[] = array('s', $error_limit);

		return ($db->num_rows($sql, $parameters) > 0);

	}

?>