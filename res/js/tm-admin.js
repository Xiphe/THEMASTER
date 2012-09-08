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
		rqst = [];

		e.preventDefault();

		$msg.fadeOut( 500, function() { $msg.html(''); });
		$btn.attr( 'disabled', 'disabled' );
		$ldng.removeClass('hidden');

		$.each($('.tm-settingwrap'), function() {
			if($(this).children('.tm-tinymcewrap').length) {
				var id = $(this).find('.tm-tinymceid').html();
				var c = tinyMCE.get(id).getContent();
				rqst.push(encodeURI(id)+'='+encodeURI(c));
			} else {
				rqst.push($(this).find('input,select,textarea').serialize());
			}
		});

		$.get(ajaxurl + '?' + rqst.join('&'),
			function( r ) {
				$btn.removeAttr( 'disabled' );
				$ldng.addClass('hidden');

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
				} else {
					$sw.find('.ts-error').removeClass('ts-error');
					$sw.find('.ts-errormsg').remove();
				}
				
				if( typeof r.msg !== 'undefined' ) {
					$msg.html( r.msg ).fadeIn(500).delay(3000).fadeOut();
				}
			}
		);
	});
});