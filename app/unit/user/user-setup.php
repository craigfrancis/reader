<?php

	class user_setup_unit extends unit {

		protected $config = array(
				'user'        => NULL,
				'dest_url'    => array('type' => 'url'),
				'sources_url' => array('type' => 'url'),
			);

		protected function authenticate($config) {
			return (USER_LOGGED_IN === true);
		}

		protected function setup($config) {

			//--------------------------------------------------
			// Config

				$user = $config['user'];

			//--------------------------------------------------
			// Form setup

				//--------------------------------------------------
				// Start

					$form = $user->form_get();
					$form->form_button_set('Save');
					$form->form_class_set('basic_form');

				//--------------------------------------------------
				// Details

					$form->print_group_start('Details');

					// $form->field_get('identification_new');
					$field_username = new form_field_info($form, 'Username');
					$field_username->value_set(USER_NAME);

					$form->field_get('delay');

					$field_links = new form_field_info($form, 'Extra');
					$field_links->value_set_html('<a href="' . html($config['sources_url']) . '" class="sources"><span>Sources</span>' . (source_error() ? '<abbr class="error">*</abbr>' : '') . '</a>');

				//--------------------------------------------------
				// Change password

					$form->print_group_start('Change password');
					$form->field_get('password_new');
					$form->field_get('password_repeat');

			//--------------------------------------------------
			// Form submitted

				if ($form->submitted()) {

					$result = $user->save();

					if ($result) {
						$form->dest_redirect($config['dest_url']);
					}

				}

			//--------------------------------------------------
			// Form defaults

				if ($form->initial()) {

					$user->populate_details();

				}

			//--------------------------------------------------
			// Variables

				$this->set('form', $form);

		}

	}

?>