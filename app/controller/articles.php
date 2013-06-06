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

				$response->set('footer_urls', array(
						array('text' => 'Back',     'class' => 'back', 'href' => url('/articles/:source/', array('source' => $source))),
						array('text' => 'Previous', 'class' => 'prev', 'href' => url('/articles/:source/', array('source' => $source))),
						array('text' => 'Next',     'class' => 'next', 'href' => url('/articles/:source/', array('source' => $source))),
						array('text' => 'Read',     'class' => 'read', 'href' => url('/articles/:source/', array('source' => $source))),
					));

			} else if ($source !== NULL) {

				unit_add('article_list', array(
						'source' => $source,
					));

				$response->title_folder_set(1, $source);

				$response->set('footer_urls', array(
						array('text' => 'Back', 'class' => 'back', 'href' => url('/articles/')),
					));

			} else {

				unit_add('article_index');

				$response->js_add('/a/js/reader.js');

				$response->set('footer_urls', array(
						array('text' => 'Back', 'class' => 'back', 'href' => url('/')),
					));

			}

		}

	}

?>