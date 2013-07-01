<?php

//--------------------------------------------------
// User

	$user = new user();
	$user->session_start();

	define('USER_ID', $user->id_get());
	define('USER_LOGGED_IN', (USER_ID > 0));

	if (USER_LOGGED_IN) {

		if (request_folder_get(0) === NULL) {
			redirect(url('/articles/'));
		}

		$user_details = $user->values_get(array('username'));

		define('USER_NAME', $user_details['username']);

		config::array_set('debug.values', 'User', USER_NAME . ' (' . USER_ID . ')');

	} else {

		if (request_folder_get(0) !== NULL) {
			redirect(url('/'));
		}

		define('USER_NAME', NULL);

	}

	config::set('user', $user);

?>