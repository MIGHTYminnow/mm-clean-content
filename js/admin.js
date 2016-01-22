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
		
		var message = mm_clean_content_messages.confirm_post_type + ' "' + postTypeLabel + '".\n\r' + mm_clean_content_messages.confirm_elements + '\n\r' + allowedElements + '\n\r' + mm_clean_content_messages.confirm_attributes + '\n\r' + allowedAttributes + '\n\r' + mm_clean_content_messages.confirm_warning + '\n\r' + mm_clean_content_messages.confirm_final;
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
	    var $this = $( this );
	    
	    // Get the post title.
	    var postTitle = $this.closest( 'td.title' ).find( 'a.row-title' ).first().text();

	    $( '.mm-clean-content-loading-gif' ).show();

	    var optionsData = {
	        'action': 'mm_clean_content_get_options',
	    }

	    var options = $.post( ajax_object.ajax_url, optionsData );

	    options.done( function( response ) {

	    	var allowedElements = response.allowed_elements;
	    	var allowedAttributes = response.allowed_attributes;
		    var message = mm_clean_content_messages.confirm_post + ' "' + postTitle + '".\n\r' + mm_clean_content_messages.confirm_elements + '\n\r' + allowedElements + '\n\r' + mm_clean_content_messages.confirm_attributes + '\n\r' + allowedAttributes + '\n\r' + mm_clean_content_messages.confirm_warning + '\n\r' + mm_clean_content_messages.confirm_final;
			var confirmation  = confirm( message );

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
				$( '.mm-clean-content-loading-gif' ).hide();
			}			        
	    });
	});

});