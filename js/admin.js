/**
 * Clean Content Admin JS
 */
jQuery( document ).ready( function( $ ) {

	// Click action for the Clean Post Type Content button.
	$( '#mm-clean-post-type-button' ).on( 'click', function() {

		// Clear any visible responses from previous cleaning and show the loading spinner.
		$( '#mm-clean-content-response-holder' ).empty();
		$( '#mm-clean-content-loading-gif' ).show();

		// Get the data we need to continue from the user input on the page.
		var postType          = $( '#mm-clean-post-types-select' ).val();
		var postTypeLabel     = $( '#mm-clean-post-types-select option:selected' ).text();
		var allowedElements   = $( '#mm-clean-content-options-allowed-elements').text();
		var allowedAttributes = $( '#mm-clean-content-options-allowed-attributes').text();

		// Build the confirm message content and trigger the confirm box.
		var messages = mm_clean_content_messages;
		var message = messages.confirm_post_type + ' "' + postTypeLabel + '".\n\r' + messages.confirm_elements + '\n\r' + allowedElements + '\n\r' + messages.confirm_attributes + '\n\r' + allowedAttributes + '\n\r' + messages.confirm_warning + '\n\r' + messages.confirm_final;
		var confirmation = confirm( message );

		// If the user clicks confirm, make the Ajax request to do the actual cleaning.
		if ( confirmation ) {

			var data = {
				'action'          : 'mm_clean_post_type_content',
				'post_type'       : postType,
				'post_type_label' : postTypeLabel
			};

			$.post( ajax_object.ajax_url, data, function( response ) {
				$( '#mm-clean-content-loading-gif' ).hide();
				$( '#mm-clean-content-response-holder' ).append( response );
			});
		} else {
			$( '#mm-clean-content-loading-gif' ).hide();
		}
	});

	// Click action for the Clean Content action links.
	$( '.mm-clean-content-link' ).on( 'click', function() {

		// Store the clicked element.
		var $this    = $( this );
		var $spinner = $this.next( '.mm-clean-content-loading-gif' );

		// Get the post title.
		var postTitle = $this.closest( 'td.title' ).find( 'a.row-title' ).first().text();

		// Show the spinner.
		$spinner.show();

		// Build the data for the first Ajax request.
		var optionsData = {
			'action': 'mm_clean_content_get_options',
		};

		// Make the first Ajax request and set up a deferred object.
		var options = $.post( ajax_object.ajax_url, optionsData );

		// When the Ajax request resolves, proceed to show our confirmation box.
		options.done( function( response ) {

			var allowedElements = response.allowed_elements;
			var allowedAttributes = response.allowed_attributes;
			var messages = mm_clean_content_messages;
			var message = messages.confirm_post + ' "' + postTitle + '".\n\r' + messages.confirm_elements + '\n\r' + allowedElements + '\n\r' + messages.confirm_attributes + '\n\r' + allowedAttributes + '\n\r' + messages.confirm_warning + '\n\r' + messages.confirm_final;
			var confirmation  = confirm( message );

			// If you user clicks confirm, make a second Ajax request to trigger the cleaning.
			if ( confirmation ) {
				ajax_object.post_id = $this.attr( 'data-post-id' );

				var data = {
					'action': 'mm_clean_content',
					'post_id': ajax_object.post_id
				};

				$.post( ajax_object.ajax_url, data, function( response ) {
					$( '.mm-clean-content-loading-gif' ).hide();
					$this.replaceWith( response );
				});
			} else {
				$spinner.hide();
			}
		});
	});

});