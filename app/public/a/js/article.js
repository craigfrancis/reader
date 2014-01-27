
(function(window, document, undefined) {

	//--------------------------------------------------
	// Open link in background

		function pop_under(e) {
			var new_window = window.open('about:blank', this.target);
			new_window.blur();
			window.focus();
			new_window.location.href = this.href;
			e.preventDefault();
		}

		var article_link = document.querySelector('#page_aside p.article_link a');

		if (article_link) {
			article_link.addEventListener('click', pop_under, false);
		}

})(window, document);
