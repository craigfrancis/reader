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

			//--------------------------------------------------
			// Cleanup

				$db = db_get();

				$db->query('DELETE sar FROM
								' . DB_PREFIX . 'source_article_read AS sar
							LEFT JOIN
								' . DB_PREFIX . 'source_article AS sa ON sa.id = sar.article_id
							WHERE
								sa.id IS NULL');

		}

	}

?>