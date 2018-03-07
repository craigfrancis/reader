
;(function(document, window, undefined) {

	'use strict';

	if (!document.addEventListener || !document.querySelector) {
		return;
	}

	function value_clean(text, max_length) {

		text = text.toLowerCase();
		text = text.replace('\'', '');
		text = text.replace(/[^a-z0-9]/gi, '-');
		text = text.replace(/--+/, '-');
		text = text.replace(/-+$/, '');
		text = text.replace(/^-+/, '');

		max_length = parseInt(max_length, 10);
		if (max_length > 0) {
			text = text.substr(0, max_length);
		}

		return text;

	}

	function change_name(e) {

		var link_ref = this.itemLinkRef,
			link_value = value_clean(this.value, link_ref.getAttribute('maxlength'));

		if (link_ref.getAttribute('itemLinkEditable') == 1) {

			link_ref.setAttribute('itemLinkGenerated', link_value);

			link_ref.value = link_value;

			// console.log('itemLink.js: Updated link to "' + link_value + '"');

		} else {

			// console.log('itemLink.js: Did not update link');

		}

	}

	function change_link(e) {

		var name_ref = this.itemLinkRef,
			old_editable = (this.getAttribute('itemLinkEditable') == 1),
			new_editable = ((this.value.trim() == '') || (old_editable && this.value == this.getAttribute('itemLinkGenerated')));

		this.setAttribute('itemLinkEditable', (new_editable ? 1 : 0));

		// if (old_editable != new_editable) {
		// 	console.log('itemLink.js: Changed editable state to "' + (new_editable ? 'true' : 'false') + '"');
		// }

	}

	function init() {

		var link_inputs = document.querySelectorAll('input[data-js-item-link-src]'),
			name_input = null;

		for (var k = (link_inputs.length - 1); k >= 0; k--) {

			name_input = document.getElementById(link_inputs[k].getAttribute('data-js-item-link-src'));

			if (name_input) {

				link_inputs[k].setAttribute('itemLinkEditable', (link_inputs[k].value.trim() == '' ? 1 : 0));
				link_inputs[k].setAttribute('itemLinkGenerated', value_clean(name_input.value, link_inputs[k].getAttribute('maxlength')));

				link_inputs[k].itemLinkRef = name_input;
				name_input.itemLinkRef = link_inputs[k];

				link_inputs[k].addEventListener('keyup', change_link);
				name_input.addEventListener('keyup', change_name);
				name_input.addEventListener('blur', change_name);

			}

		}

	}

	if (document.readyState !== 'loading') {
		window.setTimeout(init); // Handle asynchronously
	} else {
		document.addEventListener('DOMContentLoaded', init);
	}

})(document, window);
