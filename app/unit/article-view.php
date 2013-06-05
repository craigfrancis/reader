<?php

	class article_view_unit extends unit {

		public function setup($config = array()) {

			$config = array_merge(array(
					'source' => NULL,
					'article' => NULL,
				), $config);

		}

	}

?>
