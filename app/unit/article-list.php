<?php

	class article_list_unit extends unit {

		public function setup($config = array()) {

			$config = array_merge(array(
					'source' => NULL,
				), $config);

			$articles = array();

			if ($config['source'] === NULL) {

				$articles[] = array(
						'url' => '/articles/:source/:id/',
						'name' => 'Article',
					);

			} else {

				foreach (array(1, 3, 5, 10, 15) as $id) {

					$articles[] = array(
							'url' => url('/articles/:source/:id/', array('source' => $config['source'], 'id' => $id)),
							'name' => 'Article ' . ucfirst($id),
						);

				}

			}

			$this->set('articles', $articles);

		}

	}

?>