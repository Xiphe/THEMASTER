if(typeof xiphe==='undefined'){var xiphe={};}xiphe=jQuery.extend(true,{},xiphe,{themaster:{responsiveimages:(function($){
	if ($('body').hasClass('wp-admin')) {
		return false;
	}

	var slideimgs = {},
		sTime = 5000;

	var responsize = function(elm) {
		if (typeof elm !== 'undefined') {
			if ($(elm).hasClass('tm-responsiveimage')) {
				innerresponsize.call(elm);
			} else {
				$(elm).find('.tm-responsiveimage').each(function() {
					innerresponsize.call(this);
				});
			}
		} else {
			$.each($('.tm-responsiveimage'), function() {
				innerresponsize.call(this);
			});
		}
		return this;
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
			nW = Math.ceil($(this).width()/rnd)*rnd,
			maxWidth = parseInt($(this).attr('data-maxwidth'));

		if (nW > maxWidth) {
			nW = maxWidth;
		}

		if(parseInt($(this).attr('data-loaded'), 10) !== nW) {
			var	nH = Math.round(nW/$(this).attr('data-ratio')),
				url,
				$img = $('<img />'),
				n;

			if ($(this).hasClass('tm-responsivebgimage')) {
				url = $(this).attr('style').match(/url\((["|']+)([^\1]+)\1\)/);
				if (!url || url.length <= 2) {
					return false;
				}
				url = url[2];
			} else {
				url = $(this).attr('src')
			}

			url = url.split('.');
			n = url[url.length-2].split('-');
			n[n.length-1] = nW+'x'+nH;
			url[url.length-2] = n.join('-');
			url = url.join('.');

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
		$(this).removeClass('tm-loading').addClass('tm-done').trigger('tm-responsiveimage_loaded');
	},

	slideshow = function() {
		var thiz = this,
			addit = false,
			$img = false;

		if (typeof slideimgs[$(this).attr('data-slideshow')] === 'undefined') {
			var args = {
					action:  'tm_responsiveslideshowget',
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
			$(this).attr('height', Math.round($(this).width()/$(this).attr('data-ratio')));
		});
	},

	_init = function() {
	},

	_ready = function() {
		if ($.cookie('tmri_nojsfallback') === 'active') {
			$.removeCookie('tmri_nojsfallback');
		}
		if(typeof tm_slideshowTime !== 'undefined') {
			sTime = parseInt(tm_slideshowTime);
		}
		resize();
		window.setTimeout(function() {
			responsize();
			$(window).resize(resize);
			$(window).resizeEnd(responsize);
		}, 10);
	};

	$.fn.responsize = function() {
		responsize(this);
		return this;
	};
;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});