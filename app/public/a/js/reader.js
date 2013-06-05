
(function(window, document, undefined) {

	//--------------------------------------------------
	// Variables

		var articles = null;
		var current_source = null;

		var reader_index_node = document.querySelector('#reader_index');

		if (!reader_index_node) {
			return;
		}

	//--------------------------------------------------
	// Nodes

		//--------------------------------------------------
		// List wrapper

			var reader_list_node = null;
			reader_list_node = document.createElement('ul');
			reader_list_node.setAttribute('id', 'reader_list');
			reader_list_node.style.display = 'none';

		//--------------------------------------------------
		// Viewer wrapper

			var reader_view_node = null;
			reader_view_node = document.createElement('div');
			reader_view_node.setAttribute('id', 'reader_view');
			reader_view_node.style.display = 'none';

		//--------------------------------------------------
		// Viewer

			var viewer_iframe_node = document.createElement('iframe');
//viewer_iframe_node.setAttribute('sandbox', '');

			var viewer_controls_node = document.createElement('div');
			viewer_controls_node.classList.add('controls');

			var viewer_prev_node = document.createElement('a');
			viewer_prev_node.classList.add('prev');
			viewer_prev_node.setAttribute('href', '#');
			viewer_prev_node.textContent = 'Previous';
			viewer_prev_node.onclick = view_article_click;

			var viewer_next_node = document.createElement('a');
			viewer_next_node.classList.add('next');
			viewer_next_node.setAttribute('href', '#');
			viewer_next_node.textContent = 'Next';
			viewer_next_node.onclick = view_article_click;

			var viewer_read_node = document.createElement('a');
			viewer_read_node.classList.add('read');
			viewer_read_node.setAttribute('href', '#');
			viewer_read_node.textContent = 'Read';
			viewer_read_node.onclick = read_article_click;

			viewer_controls_node.appendChild(viewer_prev_node);
			viewer_controls_node.appendChild(document.createTextNode(' '));
			viewer_controls_node.appendChild(viewer_next_node);
			viewer_controls_node.appendChild(document.createTextNode(' '));
			viewer_controls_node.appendChild(viewer_read_node);

			reader_view_node.appendChild(viewer_iframe_node);
			reader_view_node.appendChild(viewer_controls_node);

		//--------------------------------------------------
		// Add to DOM

			reader_index_node.parentNode.appendChild(reader_list_node);
			reader_index_node.parentNode.appendChild(reader_view_node);

	//--------------------------------------------------
	// Reset header

		var page_title_ref = document.querySelector('#page_title a');
		if (page_title_ref) {
			page_title_ref.onclick = function() {

					reader_index_node.style.display = 'block';
					reader_list_node.style.display = 'none';
					reader_view_node.style.display = 'none';

					return false;

				}
		}

	//--------------------------------------------------
	// Article count update

		function article_count_update() {

			if (!articles) {
				return;
			}

			var sources = document.querySelectorAll('#reader_index li');
			for (var k = (sources.length - 1); k >= 0; k--) {

				var source = sources[k].getAttribute('data-source');

				if (articles[source]) {

					sources[k].classList.remove('no_articles');

					var count_node = sources[k].querySelector('em');
					if (count_node) {
						count_node.textContent = articles[source].length;
					}

				} else {

					sources[k].classList.add('no_articles');

				}

			}

view_source('comics');
view_article(1);

		}

		article_count_update();

	//--------------------------------------------------
	// Article data update

		var req = new XMLHttpRequest();

		req.onreadystatechange = function() {
				if (req.readyState === 4 && req.status === 200){

					articles = JSON.parse(req.responseText);

					article_count_update();

				}
			};

		req.open('GET', '/a/api/articles/', true);
		req.send(null);

	//--------------------------------------------------
	// View source list

		function view_article(article_ref) {

			article_ref = parseInt(article_ref, 10);

			if (!articles) {

				return false; // Articles not ready

			} else if (article_ref >= 0 && articles[current_source][article_ref]) {

				var article_id = articles[current_source][article_ref].id;

				viewer_iframe_node.setAttribute('src', '/a/api/article/?id=' + encodeURIComponent(article_id));

				if (articles[current_source][article_ref - 1]) {
					viewer_prev_node.setAttribute('href', articles[current_source][article_ref - 1].url);
					viewer_prev_node.setAttribute('data-article-ref', (article_ref - 1));
				} else {
					viewer_prev_node.setAttribute('href', '#');
					viewer_prev_node.setAttribute('data-article-ref', -1);
				}

				if (articles[current_source][article_ref + 1]) {
					viewer_next_node.setAttribute('href', articles[current_source][article_ref + 1].url);
					viewer_next_node.setAttribute('data-article-ref', (article_ref + 1));
				} else {
					viewer_next_node.setAttribute('href', '#');
					viewer_next_node.setAttribute('data-article-ref', -1);
				}

				viewer_read_node.setAttribute('href', '#');
				viewer_read_node.setAttribute('data-article-ref', article_ref);

				reader_index_node.style.display = 'none';
				reader_list_node.style.display = 'none';
				reader_view_node.style.display = 'block';

			}

			return true;

		}

		function view_article_click() {
			return !view_article(this.getAttribute('data-article-ref'));
		}

	//--------------------------------------------------
	// Read article

		function read_article(article_ref) {

			article_ref = parseInt(article_ref, 10);

			if (!articles) {

				return false; // Articles not ready

			} else if (article_ref >= 0 && articles[current_source][article_ref]) {

				console.log(article_ref);

			}

			return true;

		}

		function read_article_click() {
			return !read_article(this.getAttribute('data-article-ref'));
		}

	//--------------------------------------------------
	// View source list

		function view_source(source) {

			if (!articles) {

				return false; // Articles not ready

			} else if (articles[source]) {

				current_source = source;

				while (reader_list_node.hasChildNodes()) {
				    reader_list_node.removeChild(reader_list_node.lastChild);
				}

				var source_articles = articles[source];
				for (var k = 0; k < source_articles.length; k++) {
					var item_node = document.createElement('li');
					var link_node = document.createElement('a');
					link_node.textContent = source_articles[k].title;
					link_node.setAttribute('href', source_articles[k].url);
					item_node.setAttribute('data-article-ref', k);
					item_node.appendChild(link_node);
					item_node.onclick = view_article_click;
					reader_list_node.appendChild(item_node);
				}

				reader_index_node.style.display = 'none';
				reader_list_node.style.display = 'block';
				reader_view_node.style.display = 'none';

				viewer_iframe_node.setAttribute('src', 'about:blank');

			}

			return true;

		}

		function view_source_click() {
			return !view_source(this.parentNode.getAttribute('data-source'));
		}

		var links = document.querySelectorAll('#reader_index li a');
		for (var k = (links.length - 1); k >= 0; k--) {
			links[k].onclick = view_source_click;
		}

})(window, document);
