(function( $ ) {
	'use strict';

	$(document).ready(function($) {

		var post_id = $('#post_ID').val();

		var postAction = function(action, data) {
			return $.post(ajaxurl, {post_id: post_id, action: action, data: data});
		};

		$('#sk_toggle_action').click(function(event) {
			event.preventDefault();
			$(this).prop('disabled', true);
			postAction('sk_toggle').done(function(response) {
				location.reload();
			});
		});
		$('#sk_regenerate_action').click(function(event) {
			event.preventDefault();
			$(this).prop('disabled', true);
			postAction('sk_regenerate').done(function(response) {
				location.reload();
			});
		});
		$('#sk_reload_action').click(function(event) {
			event.preventDefault();
			postAction('sk_reload').done(function(response) {
				location.reload();
			});
		});

	});
})( jQuery );
