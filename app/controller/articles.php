<?php

	class articles_controller extends controller {

		public function action_index($source = NULL) {

			$response = response_get();

			$article_id = request('id');

			if ($article_id !== NULL) {

				$unit = unit_add('article_view', array(
						'source' => $source,
						'article' => $article_id,
					));

				$response->title_folder_set(1, $source);
				$response->title_folder_set(2, $unit->title_get());

			} else if ($source !== NULL) {

				unit_add('article_list', array(
						'source' => $source,
					));

				$response->title_folder_set(1, $source);

			} else {

				unit_add('article_index');

				$response->js_add('/a/js/reader.js');

			}

		}

	}

?>