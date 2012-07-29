// HANNES DIERCKS 2012-02-01
(function($){
	$.fn.loadImg = function(src, to, callback) {
		var thiz = this;
		$(new Image()).load(function() {
			switch(to) {
				case 'html':
					thiz.html(this);
					break;
				case 'bg':
					thiz.css({
						'background-image' : 'url(\''+src+'\')'
					});
					break;
				default:
					thiz.attr('src', src);
					break;
			}
			if(typeof callback == 'function') {
				callback();
			}
		}).attr('src', src);
	}
})( jQuery );