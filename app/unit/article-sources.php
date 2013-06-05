<?php

	class article_sources_unit extends unit {

		public function setup($config = array()) {

			$sources = array();

			foreach (array('comics', 'alistapart', 'dzone') as $source) {

				$sources[] = array(
						'url' => url('/articles/:source/', array('source' => $source)),
						'ref' => $source,
						'name' => ucfirst($source),
						'count' => rand(5, 10),
					);

			}

			$this->set('sources', $sources);

		}

	}

?>