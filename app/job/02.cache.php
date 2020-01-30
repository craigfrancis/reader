<?php

	class cache_job extends job {

		public function should_run() {
			return true;
		}

		public function email_addresses_get() {
			return array(
					'stage' => array(
							'craig@craigfrancis.co.uk',
						),
					'demo' => array(
							'craig@craigfrancis.co.uk',
						),
					'live' => array(
							'craig@craigfrancis.co.uk',
						),
				);
		}

		public function run() {

			articles::local_cache();

			articles::image_cleanup();

		}

	}

?>