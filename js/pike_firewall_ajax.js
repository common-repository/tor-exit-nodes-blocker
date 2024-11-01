jQuery(document).ready( function() {
	jQuery.ajax({
		type: 'post',
		url: pikefirewallAjax.ajaxurl,
		data: { action: 'pike_firewall_ajax' },
		success: function(response) {
//			console.log(response);
//			if ( response.indexOf('pike-firewall-notice') != -1 ) {
//				jQuery('#wpbody > #wpbody-content > .wrap > h1').after(response);
//			}
		}
	})   
})