
(function(window, document, undefined) {

	//--------------------------------------------------
	// DOM nodes

		function dom_get(url, variable) {

			var req = new XMLHttpRequest();

			req.onreadystatechange = function() {
					if (req.readyState === 4 && req.status === 200){
						if (variable == 'article_list_dom') {
							article_list_dom = req.responseXML;
						} else if (variable == 'article_view_dom') {
							article_view_dom = req.responseXML;
						}
					}
				};

			req.open('GET', url, true);
			req.send(null);

		}

		var article_list_dom = null;
		dom_get('/articles/null/?output=js', 'article_list_dom');

		var article_view_dom = null;
		dom_get('/articles/null/null/?output=js', 'article_view_dom');

	//--------------------------------------------------
	// View sources article listing

		function view_source() {
console.log(article_list_dom);
console.log(article_view_dom);
return false;
			if (!article_list_dom) {
				return true; // Follow link, DOM not ready
			}

			var source = this.parentNode.getAttribute('data-source');

			console.log(source);

			return false;

		}

		var links = document.querySelectorAll('#reader_index a');
		for (k in links) {

			if (typeof links[k] == 'object') {
				links[k].onclick = view_source;
			}

		}

})(window, document);
