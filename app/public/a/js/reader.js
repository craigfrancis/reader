
(function(window, document, undefined) {
return;
	//--------------------------------------------------
	// Variables

		var source_data = null;
		var current_source = null;
		var current_article = null;

		var reader_index_node = document.querySelector('#reader_index');
		var reader_footer_node = document.querySelector('#page_footer');

		if (!reader_index_node || !reader_footer_node) {
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

			reader_index_node.parentNode.appendChild(reader_list_node);

		//--------------------------------------------------
		// Viewer wrapper

			var reader_view_node = null;
			reader_view_node = document.createElement('div');
			reader_view_node.setAttribute('id', 'reader_view');
			reader_view_node.style.display = 'none';

			reader_index_node.parentNode.appendChild(reader_view_node);

		//--------------------------------------------------
		// Viewer

			var viewer_iframe_node = document.createElement('iframe');
//viewer_iframe_node.setAttribute('sandbox', '');

			reader_view_node.appendChild(viewer_iframe_node);

		//--------------------------------------------------
		// Footer links

			while (reader_footer_node.hasChildNodes()) {
			    reader_footer_node.removeChild(reader_footer_node.lastChild);
			}

			var viewer_back_node = document.createElement('a');
			viewer_back_node.classList.add('back');
			viewer_back_node.setAttribute('href', '#');
			viewer_back_node.textContent = 'Back';
			viewer_back_node.addEventListener('click', back_link_click, false);

			var viewer_prev_node = document.createElement('a');
			viewer_prev_node.classList.add('prev');
			viewer_prev_node.style.display = 'none';
			viewer_prev_node.setAttribute('href', '#');
			viewer_prev_node.textContent = 'Previous';
			viewer_prev_node.addEventListener('click', view_article_click, false);

			var viewer_next_node = document.createElement('a');
			viewer_next_node.classList.add('next');
			viewer_next_node.style.display = 'none';
			viewer_next_node.setAttribute('href', '#');
			viewer_next_node.textContent = 'Next';
			viewer_next_node.addEventListener('click', view_article_click, false);

			var viewer_read_node = document.createElement('a');
			viewer_read_node.classList.add('read');
			viewer_read_node.style.display = 'none';
			viewer_read_node.setAttribute('href', '#');
			viewer_read_node.textContent = 'Read';
			viewer_read_node.addEventListener('click', read_article_click, false);

			reader_footer_node.appendChild(viewer_back_node);
			reader_footer_node.appendChild(document.createTextNode(' '));
			reader_footer_node.appendChild(viewer_prev_node);
			reader_footer_node.appendChild(document.createTextNode(' '));
			reader_footer_node.appendChild(viewer_next_node);
			reader_footer_node.appendChild(document.createTextNode(' '));
			reader_footer_node.appendChild(viewer_read_node);

	//--------------------------------------------------
	// Article data update

		function article_data_update() {

			if (!source_data) {
				return;
			}

			while (reader_index_node.hasChildNodes()) {
			    reader_index_node.removeChild(reader_index_node.lastChild);
			}

			for (var k in source_data) {
				if (source_data.hasOwnProperty(k) && source_data[k]['articles'].length > 0) {
					var item_node = document.createElement('li');
					var link_node = document.createElement('a');
					var count_node = document.createElement('em');
					count_node.textContent = source_data[k]['articles'].length;
					link_node.textContent = source_data[k].name;
					link_node.setAttribute('data-source', k);
					link_node.setAttribute('href', source_data[k].url);
					link_node.addEventListener('click', view_source_click, false);
					item_node.appendChild(link_node);
					item_node.appendChild(count_node);
					reader_index_node.appendChild(item_node);
				}
			}

// view_source('garfield');
// view_article(1);

		}

		article_data_update();

		var req = new XMLHttpRequest();

		req.onreadystatechange = function() {
				if (req.readyState === 4 && req.status === 200){

					source_data = JSON.parse(req.responseText);

					article_data_update();

				}
			};

		req.open('GET', '/a/api/articles/', true);
		req.send(null);

	//--------------------------------------------------
	// View source index

		function view_index() {

			current_source = null;
			current_article = null;

			reader_index_node.style.display = 'block';
			reader_list_node.style.display = 'none';
			reader_view_node.style.display = 'none';
			reader_footer_node.style.display = 'none';

			viewer_prev_node.style.display = 'none';
			viewer_next_node.style.display = 'none';
			viewer_read_node.style.display = 'none';

			return true;

		}

	//--------------------------------------------------
	// View source list

		function view_source(source) {

			if (!source_data) {

				return false; // Articles not ready

			} else if (source_data[source]) {

				current_source = source;
				current_article = null;

				while (reader_list_node.hasChildNodes()) {
				    reader_list_node.removeChild(reader_list_node.lastChild);
				}

var inline = true; // Use media match query?

				var source_articles = source_data[source]['articles'];
				for (var k = 0; k < source_articles.length; k++) {

					var item_node = document.createElement('li');
					var link_node = document.createElement('a');
					link_node.textContent = source_articles[k].title;

					if (inline) {

						link_node.setAttribute('href', source_articles[k].link);
						link_node.setAttribute('target', '_blank');
						item_node.appendChild(link_node);

						var link_iframe = document.createElement('iframe');
						link_iframe.setAttribute('src', '/a/api/article/?id=' + encodeURIComponent(source_articles[k].id) + '&inline=true');
						item_node.appendChild(link_iframe);

					} else {

						link_node.addEventListener('click', view_article_click, false);
						link_node.setAttribute('href', source_articles[k].url);
						link_node.setAttribute('data-article-ref', k);
						item_node.appendChild(link_node);

					}

					reader_list_node.appendChild(item_node);

				}

				reader_index_node.style.display = 'none';
				reader_list_node.style.display = 'block';
				reader_view_node.style.display = 'none';
				reader_footer_node.style.display = 'block';

				viewer_prev_node.style.display = 'none';
				viewer_next_node.style.display = 'none';
				viewer_read_node.style.display = 'none';

				viewer_iframe_node.setAttribute('src', 'about:blank');

			}

			return true;

		}

		function view_source_click(e) {
			if (view_source(this.getAttribute('data-source'))) {
				e.preventDefault();
			}
		}

	//--------------------------------------------------
	// View article

		function view_article(article_ref) {

			article_ref = parseInt(article_ref, 10);

			if (!source_data) {

				return false; // Articles not ready

			} else if (article_ref >= 0 && source_data[current_source]['articles'][article_ref]) {

				current_article = article_ref;

				var source_articles = source_data[current_source]['articles'];
				var article_id = source_articles[article_ref].id;

				viewer_iframe_node.setAttribute('src', '/a/api/article/?id=' + encodeURIComponent(article_id));

				viewer_back_node.setAttribute('data-source', current_source);
				viewer_back_node.setAttribute('href', source_data[current_source].url);

				if (source_articles[article_ref - 1]) {
					viewer_prev_node.setAttribute('href', source_articles[article_ref - 1].url);
					viewer_prev_node.setAttribute('data-article-ref', (article_ref - 1));
				} else {
					viewer_prev_node.setAttribute('href', '#');
					viewer_prev_node.setAttribute('data-article-ref', -1);
				}

				if (source_articles[article_ref + 1]) {
					viewer_next_node.setAttribute('href', source_articles[article_ref + 1].url);
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
				reader_footer_node.style.display = 'block';

				viewer_prev_node.style.display = 'inline';
				viewer_next_node.style.display = 'inline';
				viewer_read_node.style.display = 'inline';

			}

			return true;

		}

		function view_article_click(e) {
			if (view_article(this.getAttribute('data-article-ref'))) {
				e.preventDefault();
			}
		}

	//--------------------------------------------------
	// Read article

		function read_article(article_ref) {

			article_ref = parseInt(article_ref, 10);

			if (!source_data) {

				return false; // Articles not ready

			} else if (article_ref >= 0 && source_data[current_source]['articles'][article_ref]) {

				var source_articles = source_data[current_source]['articles'];

				console.log(article_ref);

			}

			return true;

		}

		function read_article_click(e) {
			if (read_article(this.getAttribute('data-article-ref'))) {
				e.preventDefault();
			}
		}

	//--------------------------------------------------
	// Back link

		function back_link_click(e) {
			// if (current_source !== null && current_article !== null) {
			// 	var success = view_source(current_source);
			// } else {
				var success = view_index();
			// }
			if (success) {
				e.preventDefault();
			}
		}

})(window, document);
