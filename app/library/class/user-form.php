<?php

	class user_form extends user_form_base {

		protected function field_delay_get() {
			$field_delay = new form_field_radios($this, 'Delay');
			$field_delay->db_field_set('delay');
			$field_delay->required_error_set('The update delay is required.');
			return $field_delay;
		}

	}

?>