<?php

	class articles_job extends job {

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

			//--------------------------------------------------
			// Update

				articles::update();

				articles::local_cache();

		}

	}

?>