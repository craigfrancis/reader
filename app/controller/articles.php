<?php

	class articles_controller extends controller {

		public function action_index($source = NULL) {

			$response = response_get();

			$article_id = request('id');

			if ($article_id !== NULL) {

				//--------------------------------------------------
				// Read state

					$article_read = request('read');
					if ($article_read == 'true' || $article_read == 'false') {
						$article_read = ($article_read == 'true');
					} else {
						$article_read = NULL;
					}

				//--------------------------------------------------
				// View unit

					$unit = unit_add('article_view', array(
							'source' => $source,
							'article' => $article_id,
							'read' => $article_read,
						));

				//--------------------------------------------------
				// Footer URLs

					$sibling_prev_id = $unit->sibling_id_get(-1);
					if ($sibling_prev_id) {
						$sibling_prev_url = url('/articles/:source/', array('source' => $source, 'id' => $sibling_prev_id));
					} else {
						$sibling_prev_url = NULL;
					}

					$sibling_next_id = $unit->sibling_id_get(+1);
					if ($sibling_next_id) {
						$sibling_next_url = url('/articles/:source/', array('source' => $source, 'id' => $sibling_next_id));
					} else {
						$sibling_next_url = NULL;
					}

					$footer_urls = array();
					$footer_urls[] = array('text' => 'Back',     'class' => 'back', 'href' => url('/articles/:source/', array('source' => $source)));
					$footer_urls[] = array('text' => 'Previous', 'class' => 'prev', 'href' => $sibling_prev_url);
					$footer_urls[] = array('text' => 'Next',     'class' => 'next', 'href' => $sibling_next_url);

					if ($unit->read_get()) {
						$footer_urls[] = array('text' => 'Read', 'class' => 'read', 'href' => url('/articles/:source/', array('source' => $source, 'id' => $article_id, 'read' => 'false')));
					} else {
						$footer_urls[] = array('text' => 'Unread', 'class' => 'unread', 'href' => url('/articles/:source/', array('source' => $source, 'id' => $article_id, 'read' => 'true')));
					}

				//--------------------------------------------------
				// Set

					$response->title_folder_set(1, $source);
					$response->title_folder_set(2, $unit->title_get());

					$response->set('footer_urls', $footer_urls);

			} else if ($source !== NULL) {

				//--------------------------------------------------
				// Listing unit

					$unit = unit_add('article_list', array(
							'source' => $source,
						));

					// if (count($unit->get('articles')) == 0) {
					// 	redirect(url('/articles/'));
					// }

					$response->title_folder_set(1, $source);

				//--------------------------------------------------
				// Footer URLs

					$response->set('footer_urls', array(
							array('text' => 'Back', 'class' => 'back', 'href' => url('/articles/')),
						));

			} else {

				//--------------------------------------------------
				// Index unit

					unit_add('article_index');

				//--------------------------------------------------
				// JavaScript

					$response->js_add('/a/js/reader.js');

				//--------------------------------------------------
				// Footer URLs

					$response->set('footer_urls', array(
							array('text' => 'Back', 'class' => 'back', 'href' => url('/')),
						));

			}

		}

	}

?>