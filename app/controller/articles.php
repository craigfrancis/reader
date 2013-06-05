<?php

	class articles_controller extends controller {

		public function action_index($source = NULL, $article = NULL) {

			$output_js = (request('output') == 'js');

			if ($article !== NULL) {

				$unit = unit_get('article_view', array(
						'source' => ($output_js ? NULL : $source),
						'article' => $article,
					));

				if (!$output_js) {
					$response = response_get();
					$response->title_folder_set(1, $source);
					$response->title_folder_set(2, 'Title');
				}

			} else if ($source !== NULL) {

				$unit = unit_get('article_list', array(
						'source' => ($output_js ? NULL : $source),
					));

				if (!$output_js) {
					$response = response_get();
					$response->title_folder_set(1, $source);
				}

			} else {

				$unit = unit_get('article_index');

				if (!$output_js) {
					$response = response_get();
					$response->js_add('/a/js/reader.js');
				}

			}

			if ($output_js) {
				$response = response_get('file');
				$response->mime_set('text/xml');
				$response->inline_set(true);
				$response->content_set(trim($unit->html()));
			} else {
				$response->unit_add($unit);
			}

		}

	}

?>