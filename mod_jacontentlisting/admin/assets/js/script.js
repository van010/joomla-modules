(function($){
	$(document).ready(function () {
		var article = $('.article-lead');
		var articleTitle = article.find('div.article-title > h1');
		var titleText = articleTitle.text().toLowerCase().trim();
		var jaclTitle = $('div.jacl-item');
		if (jaclTitle.length) {
			var links = [];
			var elements = [];
			$.each(jaclTitle, function (idx, el) {
				var el = $(el);
				var title = el.find('div.jacl-item__body > .jacl-item__title');
				if (title.length && titleText === title.text().toLowerCase().trim()) {
					el.parent().hide();
				}
				// if more than 1 mod-ja-cl in 1 page, filter duplicate articles
				var article_link = $(jaclTitle[idx]).find('.jacl-item__title').children()[0].href;
				links.push(article_link);
				elements.push(el);
			})
		}
		
		Array.prototype.unique = function (unique = true) {
			let unique_arr = [];
			var duplicate_idx = [];
			for (let i = 0; i < this.length; i++) {
				if (!unique_arr.includes(this[i])) {
					unique_arr.push(this[i]);
				} else {
					duplicate_idx.push(i);
				}
			}
			if (unique) return unique_arr;
			return duplicate_idx;
		}
		
		const duplicateIdx = links.unique(false);
		const unique_links = links.unique();
		if (duplicateIdx.length > 0 && elements.length > 0){
			for (let i=0; i<duplicateIdx.length; i++){
				elements[duplicateIdx[i]].parent().remove();
			}
		}
	})
})(jQuery)