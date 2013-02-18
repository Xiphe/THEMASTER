/**
 * Responsive image Plugin
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @author  Hannes Diercks <info@xiphe.net>
 * @license GPLv2
 */
/*global ajaxurl */
var xiphe=xiphe||{};xiphe=jQuery.extend(true,{},xiphe,{themaster:{responsiveimages:(function($){var

    /* PRIVATE VARS */
	self = this,
	slideimgs = {},
	waitForTouches = false,
	touchIntervall = 10000,
	touched = {},
	touchArchive = {},
	$loader = $('<div />').css({
		'height' : '1px',
		'width' : '1px',
		'position' : 'fixed',
		'top' : '-1px',
		'left' : '-1px',
		'overflow' : 'hidden'
	}).data('attached', false)

	/* PUBLIC VARS */;

	self.slideshowChangeIntervall = 5000;
	self.slideshowAnimationDuration = 1000;

    /* PRIVATE METHODS */ var

    _init = function() {
	},

	_ready = function() {
		if ($('body').hasClass('wp-admin')) {
			return false;
		}

		_initPlugin();

		window.setInterval(_saveTouches, touchIntervall/2);

		if ($.cookie('tmri_nojsfallback') === 'active') {
			$.removeCookie('tmri_nojsfallback');
		}
		if(typeof xiphe.themaster.responsive_slideshowTime !== 'undefined') {
			self.slideshowChangeIntervall = parseInt(xiphe.themaster.responsive_slideshowTime, 10);
		}
		_resize();
		window.setTimeout(function() {
			_responsizePlugin();
			$(window).resize(_resize);
			$(window).resizeEnd(_responsizePlugin);
		}, 10);
	},

	_initPlugin = function() {
		$.fn.responsize = function() {
			$.each(this, function() {
				_responsizePlugin(this);
			});
			return this;
		};
	},

	_responsizePlugin = function(elm) {
		if (typeof elm !== 'undefined') {
			if ($(elm).hasClass('tm-responsiveimage')) {
				_innerresponsize.call(elm);
			} else {
				$(elm).find('.tm-responsiveimage').each(function() {
					_innerresponsize.call(this);
				});
			}
		} else {
			$.each($('.tm-responsiveimage'), function() {
				_innerresponsize.call(this);
			});
		}
		return this;
	},

	_innerresponsize = function() {
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
			maxWidth = parseInt($(this).attr('data-maxwidth'), 10);

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

			self.loadImg.call(
				this,
				url,
				function() {
					_setImg.call(thiz, url, nW, nH);
					self.touch($(thiz).attr('data-origin'), nW, quality, $(thiz).attr('data-nonce'));
				},
				function() {
					$.getJSON(ajaxurl, {
						action: 'tm_responsiveimageget',
						width: iWidth,
						image: $(thiz).attr('data-origin'),
						nonce: $(thiz).attr('data-nonce'),
						quality: quality
					}, function(r) {
						if (r && r.status === 'ok') {
							/* Add the current times to prevent false errors through cache */
							r.uri += '?ts='+new Date().getTime();
							self.loadImg.call(
								this,
								r.uri,
								function() {
									_setImg.call(thiz, r.uri, nW, nH);
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
				_slideshow.call(thiz);
			}, 0);
		}
	},

	_setImg = function(url, w) {
		if ($(this).hasClass('tm-responsivebgimage')) {
			$(this).css('backgroundImage', 'url(\''+url+'\')');
		} else {
			this.src = url;
		}
		$(this).attr('data-loaded', w);
		$(this).removeClass('tm-loading').addClass('tm-done').trigger('tm-responsiveimage_loaded');
	},

	_slideshow = function() {
		var thiz = this,
			addit = false,
			$img = false;

		if (typeof slideimgs[$(this).attr('data-slideshow')] === 'undefined') {
			if ($(this).hasClass('tm-loading')) {
				window.setTimeout(function() {
					_slideshow.call(thiz);
				}, 1000);
				return false;
			}
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

			$.getJSON(ajaxurl, args, function(r) {
				if(r && r.status === 'ok') {
					self.loadImg($(r.img).attr('src'), function() {
						slideimgs[args.image] = r.img;
						if (addit === true) {
							_slideshowNext.call(thiz, $(r.img));
						} else {
							$img = $(r.img);
						}
					});
				}
			});
		} else {
			$img = $(slideimgs[$(this).attr('data-slideshow')]);
		}
		
		window.setTimeout(function() {
			if ($img !== false) {
				_slideshowNext.call(thiz, $img);
			} else {
				addit = true;
			}
		}, self.slideshowChangeIntervall);
	},

	_slideshowNext = function($img) {
		var $old = $(this);
		$old.trigger('tm-responsiveimage_slideshowchange', {$next: $img, $current: $old});
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
		$img.parent().animate({'opacity' : '1'}, self.slideshowAnimationDuration, function() {
			$old.remove();
			$img.unwrap();
			_responsizePlugin($img[0]);
		});
	},

	_saveTouches = function() {
		if (waitForTouches === 1) {
			waitForTouches = false;
			$.post(ajaxurl, {
				action: 'tm_responsiveimagetouched',
				data: touched
			});
			
			/*
			 * Save the touches into an archive to prevent double touching.
			 */
			$.extend(touchArchive, touched);
			touched = {};
		} else if (waitForTouches !== false) {
			waitForTouches = 1;
		}
	},

	_resize = function() {
		$.each($('.tm-responsiveimage'), function() {
			$(this).attr('height', Math.round($(this).width()/$(this).attr('data-ratio')));
		});
	}


	/* PUBLIC METHODS */;

	self.loadImg = function(src, loadCb, errorCb) {
		if ($(this).hasClass('tm-responsiveimage')) {
			$(this).addClass('tm-loading');
		}
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
				}, 0);
			})
			.error(function() {
				finished();
				if (typeof errorCb === 'function') {
					errorCb.call($i);
				}
			}).attr('src', src);

		$i.appendTo($loader);
	};

	self.touch = function(img, width, quality, nonce) {
		/*
		 * Check if images has been touched previously.
		 */
		if (typeof touchArchive[img] !== 'undefined' &&
			typeof touchArchive[img][width] !== 'undefined' &&
			typeof touchArchive[img][width][quality] !== 'undefined'
		) {
			return;
		}

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
	};

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

// INITIATION
;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});