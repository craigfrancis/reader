/*******************************************************************************************
 * itemLink
 * Written by Craig Francis
 * Automatically generate the item link
 *******************************************************************************************/

	var itemLink = new function() {

		//--------------------------------------------------
		// Old browsers

			if (!document.getElementById || !document.getElementsByTagName) {
				return;
			}

		//--------------------------------------------------
		// Initialisation

			this.init = function() {

				//--------------------------------------------------
				// Debug

					//console.log('itemLink.js: Initialisation');

				//--------------------------------------------------
				// Get the references

					itemLink.fldName = null;
					itemLink.fldLink = null;

					$('input[data-js-item-link-src]').each(function() {
							itemLink.fldLink = this;
							itemLink.fldName = $('#' + $(this).data('js-item-link-src')).get(0);
						});

					if (!itemLink.fldName) {
						console.log('itemLink.js: Could not find the "name" field');
						return;
					}

					if (!itemLink.fldLink) {
						console.log('itemLink.js: Could not find the "url" field');
						return;
					}

				//--------------------------------------------------
				// Set the generatedLink

					itemLink.updateGeneratedLink();

				//--------------------------------------------------
				// Determine if the link is editable

					itemLink.editable = true;

					itemLink.linkChange();

					itemLink.fldLink.onkeyup = itemLink.linkChange;

				//--------------------------------------------------
				// Update the link field

					itemLink.fldName.onkeyup = itemLink.nameChange;

					itemLink.fldLink.onblur = itemLink.nameChange; // When clearing the link, auto-re-fill.

					itemLink.nameChange();

			}

		//--------------------------------------------------
		// Generate link

			this.updateGeneratedLink = function() {

				var text = itemLink.fldName.value;
				text = text.toLowerCase();
				text = text.replace('\'', '');
				text = text.replace(/[^a-z0-9]/gi, '-');
				text = text.replace(/--+/, '-');
				text = text.replace(/-+$/, '');
				text = text.replace(/^-+/, '');
				text = text.substr(0, itemLink.fldLink.maxLength);

				itemLink.generatedLink =  text;

			}

		//--------------------------------------------------
		// Name changed - try to update link

			this.nameChange = function() {

				if (itemLink.editable) {

					itemLink.updateGeneratedLink();

					itemLink.fldLink.value = itemLink.generatedLink;

					//console.log('itemLink.js: Updated link to "' + itemLink.generatedLink + '"');

				} else {

					//console.log('itemLink.js: Did not update link');

				}

			}

		//--------------------------------------------------
		// Link changed - see if it is now user set

			this.linkChange = function() {

				var old = itemLink.editable;

				itemLink.editable = ((itemLink.fldLink.value == '') || (itemLink.editable && itemLink.fldLink.value == itemLink.generatedLink));

				if (old != itemLink.editable) {

					//console.log('itemLink.js: Changed editable state to "' + (itemLink.editable ? 'true' : 'false') + '"');

				}

			}

		//--------------------------------------------------
		// On page load

			$(this.init);

	};
