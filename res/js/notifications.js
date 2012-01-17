(function($){
	$.fn.notification = function(msg, cls, time) {
		if(typeof cls == 'undefined') {
			cls = 'msg';
		}
		cls = 'notification '+cls;
		if(typeof time == 'undefined') {
			time = 10000;
		}
		var id = 'note_'+new Date().getTime()+Math.ceil(Math.random()*100);
		this.prepend('<div id="'+id+'" class="'+cls+'"><p>'+msg+'</p></div>');
		$('#'+id).hover(function() {
			$(this).addClass('inspect');
		}, function() {
			$(this).removeClass('inspect');
			if($(this).hasClass('remove')) {
				$(this).fadeOut(600, function() {
					$(this).remove();
				});
			}
		});
		window.setTimeout(function() {
			if(!$('#'+id).hasClass('inspect')) {
				$('#'+id).fadeOut(600, function() {
					$(this).remove();
				});
			} else {
				$('#'+id).addClass('remove');
			}
		}, time);
	}
})( jQuery );