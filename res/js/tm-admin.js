/*global tinyMCE, ajaxurl */
jQuery( document ).ready( function($) {
	$('a.tm-settings').click( function(e) { var
		$sw = $(this).closest('tr').next('tr');

		e.preventDefault();
		$sw.toggleClass('closed');
	});

	$('.tm-savewrap button').click( function(e) { var
		$sw = $(this).closest('.tm-settingswrap'),
		$btn = $(this),
		$ldng = $sw.find('.tm-loading'),
		$msg = $sw.find('.tm-message'),
		data = {};

		e.preventDefault();

		$msg.fadeOut( 500, function() { $msg.html(''); });
		$btn.attr( 'disabled', 'disabled' );
		$ldng.removeClass('hidden');

		$.each($sw.children('.tm-settingwrap'), function() {
			if($(this).find('.tmce-active').length) {
				var id = $(this).find('.tm-tinymceid').html();
				var c = tinyMCE.get(id).getContent();
				data[id] = c;
			} else {
				$(this).find('input,select,textarea').each(function() {
					var __data = $(this).serializeArray();
					if (__data.length) {
						data[__data[0].name] = __data[0].value;
					}
				});
			}
		});

		$.each($sw.children('.tm-savewrap').find('input,select,textarea').serializeArray(), function() {
			data[this.name] = this.value;
		});

		$.post(
			ajaxurl,
			data,
			function(r) {
				$btn.removeAttr( 'disabled' );
				$ldng.addClass('hidden');
				$('.ts-error').removeClass('ts-error');
				$('.ts-errormsg').remove();

				r = eval( '(' + r + ')' );
				if( r.status === 'validationError' &&
					typeof r.id !== 'undefined'
				) {
					$( '#' + r.id ).addClass('ts-error');
					if( typeof r.errorMsg !== 'undefined' ) {
						$( '#' + r.id ).after(
							$('<span />').addClass('ts-errormsg').html(r.errorMsg)
						);
					}
				}
				
				if( typeof r.msg !== 'undefined' ) {
					$msg.html( r.msg ).fadeIn(500).delay(3000).fadeOut();
				}
			}
		);
	});
});