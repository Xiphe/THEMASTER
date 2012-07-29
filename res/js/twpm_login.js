jQuery(document).ready(function($) {
	if(twpm_rederect && window.location.hash != '' && window.location.hash != '#') {
		$.post(ajaxurl, {
			action: 'twpm_hashRederect',
			hash: window.location.hash
		});
		$('#twpm_hash').html(window.location.hash);
	}
	
});