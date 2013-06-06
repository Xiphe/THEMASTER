(function($){
	$.fn.widthAll = function() {
		var width = 0, thiz = this;
		for (var i = 0; i < thiz.length; i++) {
			width += $(thiz[i]).width();
		}
		return width;
	};

	$.fn.outerWidthAll = function(inclMargin) {
		var width = 0, thiz = this;
		for (var i = 0; i < thiz.length; i++) {
			width += $(thiz[i]).outerWidth(!!inclMargin);
		}
		return width;
	};
})(jQuery);