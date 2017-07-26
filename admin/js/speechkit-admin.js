(function( $ ) {
	'use strict';

	$(document).ready(function($) {

		var postAction = function(action, data) {
			return $.post(ajaxurl, {action: action, data: data});
		};

		$('#sk_reload_all').click(function(event) {
			event.preventDefault();
			postAction('sk_reload_all').done(function(response) {
				location.reload();
			});
		});

	});
})( jQuery );
