if(typeof xiphe==='undefined'){var xiphe={};}xiphe=jQuery.extend(true,{},xiphe,{themaster:{responsiveimages:(function($){
	if ($('body').hasClass('wp-admin')) {
		return false;
	}

	var self = this,
		slideimgs = {},
		sTime = 5000,
		waitForTouches = false,
		touchIntervall = 10000,
		touched = {},
		$loader = $('<div />').css({
			'height' : '1px',
			'width' : '1px',
			'position' : 'fixed',
			'top' : '-1px',
			'left' : '-1px',
			'overflow' : 'hidden'
		}).data('attached', false);

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
		var rnd,
			iWidth = $(this).width();
		if (typeof window.devicePixelRatio !== 'undefined') {
			iWidth = iWidth*window.devicePixelRatio;
		}


		if (iWidth < 200) {
			rnd = 50;
		} else if (iWidth < 1000) {
			rnd = 100;
		} else {
			rnd = 200;
		}
		var thiz = this,
			nW = Math.ceil(iWidth/rnd)*rnd,
			maxWidth = parseInt($(this).attr('data-maxwidth'));

		if (nW > maxWidth) {
			nW = maxWidth;
		}

		
		if(parseInt($(this).attr('data-loaded'), 10) !== nW) {
			var	nH = Math.round(nW/$(this).attr('data-ratio')),
				url,
				originUrl,
				n;

			if ($(this).hasClass('tm-responsivebgimage')) {
				url = $(this).attr('style').match(/url\(([^)]+)\)/);

				if (!url || url.length < 2) {
					return false;
				}
				url = self.trimQuotes(url[1]);
			} else {
				url = $(this).attr('src');
			}

			/*
			 * save the original url.
			 */
			originUrl = url;

			/*
			 * Remove the extension.
			 */
			url = url.split('.');

			/*
			 * Get the size (& Quality)
			 */
			n = url[url.length-2].split('-');

			/*
			 * keep quality if set
			 */
			var q = '';
			var quality = 2;
			if (n[n.length-1].indexOf('q') !== -1) {
				quality = n[n.length-1].split('q')[1];
				q = 'q'+quality;
			}

			/*
			 * Build the new size.
			 */
			n[n.length-1] = nW+'x'+nH+q;

			/*
			 * inject it to the url.
			 */
			url[url.length-2] = n.join('-');

			/*
			 * rebuild the url.
			 */
			url = url.join('.');

			self.loadImg(
				url,
				function() {
					setImg.call(thiz, url, nW, nH);
					self.touch($(thiz).attr('data-origin'), nW, quality, $(thiz).attr('data-nonce'));
				},
				function() {
					$.get(ajaxurl, {
						action: 'tm_responsiveimageget',
						width: iWidth,
						image: $(thiz).attr('data-origin'),
						nonce: $(thiz).attr('data-nonce'),
						quality: quality
					}, function(r) {
						r = eval('('+r+')');
						if (r && r.status === 'ok') {
							self.loadImg(
								r.uri,
								function() {
									setImg.call(thiz, r.uri, nW, nH);
								}
							);
						}
					});
				}
			);
		}

		var sldshw = $(this).attr('data-slideshow');
		if (typeof sldshw !== 'undefined' && sldshw !== false && sldshw !== '') {
			window.setTimeout(function() {
				slideshow.call(thiz);
			}, 0);
		}
	},

	setImg = function(url, w, h) {
		if ($(this).hasClass('tm-responsivebgimage')) {
			// var r = window.devicePixelRatio;
			$(this).css('backgroundImage', 'url(\''+url+'\')');
			// if (r > 1) {
			// 	$(this).css('backgroundSize', w/r+'px '+h/r+'px');
			// }
		} else {
			this.src = url;
		}
		$(this).attr('data-loaded', w);
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

	_saveTouches = function() {
		if (waitForTouches === 1) {
			waitForTouches = false;
			$.post(ajaxurl, {
				action: 'tm_responsiveimagetouched',
				data: touched
			});
			touched = {};
		} else if (waitForTouches !== false) {
			waitForTouches = 1;
		}
	},

	resize = function() {
		$.each($('.tm-responsiveimage'), function() {
			$(this).attr('height', Math.round($(this).width()/$(this).attr('data-ratio')));
		});
	},

	_init = function() {
	},

	_ready = function() {
		window.setInterval(_saveTouches, touchIntervall/2);

		if ($.cookie('tmri_nojsfallback') === 'active') {
			$.removeCookie('tmri_nojsfallback');
		}
		if(typeof tm_slideshowTime !== 'undefined') {
			sTime = parseInt(tm_slideshowTime, 10);
		}
		resize();
		window.setTimeout(function() {
			responsize();
			$(window).resize(resize);
			$(window).resizeEnd(responsize);
		}, 10);
	};

	self.loadImg = function(src, loadCb, errorCb) {
		if ($loader.data('attached') === false) {
			$('body').append($loader).data('attached', true);
		}

		var finished = function() {
			$i.detach();
			if (!$loader.children().length) {
				$loader.detach().data('attached', false);
			}
		},
		$i = $('<img />');

		$i.load(function() {
				window.setTimeout(function() {
					finished();
					if (typeof loadCb === 'function') {
						loadCb.call($i);
					}
				}, 1000);
			})
			.error(function() {
				finished();
				if (typeof errorCb === 'function') {
					errorCb.call($i);
				}
			})[0].src = src;

		$i.appendTo($loader);
	};

	self.touch = function(img, width, quality, nonce) {
		if (typeof touched[img] === 'undefined') {
			touched[img] = {};
		}
		if (typeof touched[img][width] === 'undefined') {
			touched[img][width] = {};
		}
		if (typeof touched[img][width][quality] === 'undefined') {
			touched[img][width][quality] = nonce;
			waitForTouches = 2;
		}
	}

	self.trimQuotes = function(str) {
		var t = str.substring(0,1);
		if (t === '\'' || t === '"') {
			str = str.substring(1);
		}
		t = str.substring(str.length-1);
		if (t === '\'' || t === '"') {
			str = str.substring(0, str.length-1);
		}
		return str;
	};

	$.fn.responsize = function() {
		$.each(this, function() {
			responsize(this);
		});
		return this;
	};
;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});