jQuery(document).ready(function($) {
	if ($('body').hasClass('wp-admin')) {
		return false;
	}

	var slideimgs = {},
		sTime = 5000;

	var responsize = function(elm) {
		if ($(elm).hasClass('tm-responsiveimage')) {
			innerresponsize.call(elm);
		} else {
			$.each($('.tm-responsiveimage'), function() {
				innerresponsize.call(this);
			});
		}
	},

	innerresponsize = function() {
		var rnd;
		if ($(this).width() < 200) {
			rnd = 50;
		} else if ($(this).width() < 1000) {
			rnd = 100;
		} else {
			rnd = 200;
		}
		var thiz = this,
			nW = Math.ceil($(this).width()/rnd)*rnd;

		if(parseInt($(this).attr('data-loaded'), 10) !== nW) {
			var	nH = Math.round(nW*$(this).attr('data-ratio')),
				url = $(this).attr('data-template').replace(':h', nH).replace(':w', nW),
				$img = $('<img />');

			$img.load(function() {
				setImg.call(thiz, url, nW);
			}).error(function() {
				$.get(ajaxurl, {
					action: 'tm_responsiveimageget',
					width: $(thiz).width(),
					image: $(thiz).attr('data-origin'),
					nonce: $(thiz).attr('data-nonce')
				}, function(r) {
					r = eval('('+r+')');
					if (r && r.status === 'ok') {
						$img.load(function() {
							setImg.call(thiz, r.uri, nW);
						})[0].src = r.uri;
					}
				});
			})[0].src = url;
		}

		var sldshw = $(this).attr('data-slideshow');
		if (typeof sldshw !== 'undefined' && sldshw !== false && sldshw !== '') {
			window.setTimeout(function() {
				slideshow.call(thiz);
			}, 0);
		}
	},

	setImg = function(url, size) {
		if ($(this).hasClass('tm-responsivebgimage')) {
			$(this).css({'background-image' : 'url('+url+')'});
		} else {
			$(this).attr('src', url);
		}
		$(this).attr('data-loaded', size);
	},

	slideshow = function() {
		var thiz = this,
			addit = false,
			$img = false;

		if (typeof slideimgs[$(this).attr('data-slideshow')] === 'undefined') {
			var args = {
					action:  'tm_responsivslideshowget',
					width:   'drct'+$(this).width(),
					image:   $(this).attr('data-slideshow'),
					nonce:   $(this).attr('data-slidenonce'),
					id:      $(this).attr('id'),
					'class': $(this).attr('class')
				};
			if ($(this).attr('data-fixalt')) {
				args.alt = $(this).attr('alt');
			}
			if ($(this).attr('data-fixtitle')) {
				args.title = $(this).attr('title');
			}

			$.get(ajaxurl, args, function(r) {
				r = eval("(" + r + ")");
				if(r && r.status === 'ok') {
					$(r.img).load(function() {
						slideimgs[args.image] = r.img;
						if (addit === true) {
							slideshowNext.call(thiz, $(this));
						} else {
							$img = $(this);
						}
					});
				}
			});		
		} else {
			$img = $(slideimgs[$(this).attr('data-slideshow')]);
		}
		
		window.setTimeout(function() {
			if ($img !== false) {
				slideshowNext.call(thiz, $img);
			} else {
				addit = true;
			}
		}, sTime);
	},

	slideshowNext = function($img) {
		var $old = $(this);
		$old = $old.wrap('<div />').parent().css({
			'position' : 'relative',
			'z-index' : 0
		});
		$img.wrap('<div />').parent().css({
			'height' : 0,
			'opacity' : 0,
			'position' : 'relative',
			'z-index' : 1
		});
		$old.before($img.parent());
		$img.parent().animate({'opacity' : '1'}, 1000, function() {
			$old.remove();
			$img.unwrap();
			responsize($img[0]);
		});
	},

	resize = function() {
		$.each($('.tm-responsiveimage'), function() {
			$(this).attr('height', Math.round($(this).width()*$(this).attr('data-ratio')));
		});
	};
	
	(function() {
		if(typeof tm_slideshowTime !== 'undefined') {
			sTime = parseInt(tm_slideshowTime);
		}
		resize();
		window.setTimeout(function() {
			responsize();
			$(window).resize(resize);
			$(window).resizeEnd(responsize);
		}, 10);
	})();
});