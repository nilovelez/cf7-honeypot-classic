jQuery(document).on( 'click', '.choneypot4cf7-notice .notice-dismiss', function() {

	jQuery.ajax({
		url: ajaxurl,
		data: {
			action: 'choneypot4cf7_dismiss_notice'
		}
	})

})