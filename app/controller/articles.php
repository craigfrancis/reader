<?php

	class articles_controller extends controller {

		public function route() {

			//--------------------------------------------------
			// Require login

				if (!USER_LOGGED_IN) {

					$debug = array();
					$debug['error'] = 'logged_out';

					if (isset($_SESSION)) {
						$debug['session'] = count($_SESSION);
					}

					$cookies = array();
					$prefix = config::get('cookie.prefix');
					$prefix_len = strlen($prefix);
					foreach ($_COOKIE as $name => $value) {
						if (substr($name, 0, $prefix_len) == $prefix) {
							$cookies[] = substr($name, $prefix_len) . '=' . $value;
						}
					}
					$debug['cookies'] = implode('|', $cookies);

					redirect(url('/', $debug));

				}

		}

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
				// Page title

					$response->title_full_set($unit->get('source_title') . ' | ' . $unit->get('article_title'));

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
					$footer_urls[] = array('text' => 'Back',     'class' => 'back', 'href' => url('/articles/'));
					$footer_urls[] = array('text' => 'Previous', 'class' => 'prev', 'href' => $sibling_prev_url);
					$footer_urls[] = array('text' => 'View',     'class' => 'view', 'href' => $unit->article_link_get(), 'target' => '_blank');
					$footer_urls[] = array('text' => 'Next',     'class' => 'next', 'href' => $sibling_next_url);

					if ($unit->read_get()) {
						$footer_urls[] = array('text' => 'Read', 'class' => 'read', 'href' => url('/articles/:source/', array('source' => $source, 'id' => $article_id, 'read' => 'false')));
					} else {
						$footer_urls[] = array('text' => 'Unread', 'class' => 'unread', 'href' => url('/articles/:source/', array('source' => $source, 'id' => $article_id, 'read' => 'true')));
					}

					$response->set('footer_urls', $footer_urls);

			} else if ($source !== NULL) {

				//--------------------------------------------------
				// Listing unit

					if ($source === 'read') {

						$unit = unit_add('article_list_read', array(
							));

						$response->title_full_set('Read articles');

					} else {

						$state = request('state');
						if ($state !== 'all' && $state !== 'read') {
							$state = 'unread';
						}

						$unit = unit_add('article_list_source', array(
								'source' => $source,
								'state' => $state,
							));

						// if (count($unit->get('articles')) == 0) {
						// 	redirect(url('/articles/'));
						// }

						$response->title_full_set($unit->get('source_title'));

					}

				//--------------------------------------------------
				// Footer URLs

					$response->set('footer_urls', array(
							array('text' => 'Back', 'class' => 'back', 'href' => url('/articles/')),
						));

			} else {

				//--------------------------------------------------
				// Index unit

					unit_add('article_index', array(
							'read_url' => url('/articles/:source/', array('source' => 'read')),
						));

				//--------------------------------------------------
				// JavaScript

					// $response->js_add('/a/js/reader.js');

				//--------------------------------------------------
				// Set

					$response->title_full_set('Reader');

					$response->set('footer_urls', array(
							array('text' => 'Sources', 'class' => 'sources', 'href' => url('/sources/')),
							array('text' => 'Logout', 'class' => 'logout', 'href' => url('/logout/')),
						));

			}

		}

	}

?>