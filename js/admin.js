/**
 * Clean Content Admin JS
 */
jQuery( document ).ready( function( $ ) {

	// Click action for the Clean Post Type Content button.
	$( '#mm-clean-post-type-button' ).on( 'click', function() {

		$( '#mm-clean-content-response-holder' ).empty();
		$( '#mm-clean-content-loading-gif' ).show();

		var postType          = $( '#mm-clean-post-types-select' ).val();
		var postTypeLabel     = $( '#mm-clean-post-types-select option:selected' ).text();
		var allowedElements   = $( '#mm-clean-content-options-allowed-elements').text();
		var allowedAttributes = $( '#mm-clean-content-options-allowed-attributes').text();
		
		var message = 'You are about to clean the content for the post type: "' + postTypeLabel + '".\n\rOnly these elements will be allowed to remain in the content:\n\r"' + allowedElements + '".\n\rOnly these attributes will be allowed to remain:\n\r"' + allowedAttributes + '".\n\rThis action cannot be undone, and it is HIGHLY recommended that you have a backup of the data you are about to clean.\n\rAre you sure you want to proceed?';

		var confirmation  = confirm( message );
			
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

	// Click action for the Clean Content link.
	$( '.mm-clean-content-link' ).on( 'click', function() {

		// Store the clicked element
		$this = $( this );

		$( '.mm-clean-content-loading-gif' ).show();

		ajax_object.post_id = $this.attr( 'data-post-id' );

		var data = {
			'action': 'mm_clean_content',
			'post_id': ajax_object.post_id
		};

		$.post( ajax_object.ajax_url, data, function( response ) {
			$( '.mm-clean-content-loading-gif' ).hide();
			$this.replaceWith( response );
		});
	});

});