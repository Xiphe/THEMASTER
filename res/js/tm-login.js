jQuery(document).ready(function($) {
	if(twpm_rederect && window.location.hash != '' && window.location.hash != '#') {
		$.post(ajaxurl, {
			action: 'twpm_hashRederect',
			hash: window.location.hash
		});
		$('#tm_loginhash').html(window.location.hash);
		$('#tm_loginlink').attr('href', $('#tm_loginlink').attr('href')+window.location.hash);
	}
	
});