(function($){
	Object.prototype.xiphe_merge = function() {
		var r;
		for (var i = this.length - 1; i >= 0; i--) {
			if (typeof r === 'undefined') {
				r = this[i]
			} else {
				r += this[i]
			}
		};
		return r;
	}
	$.xiphe_merge = function(obj) {
		return obj.xiphe_merge();
	}
})(jQuery);