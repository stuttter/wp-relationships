jQuery( document ).ready( function( $ ) {
    'use strict';

	// UL rows are sortable
	$( 'ul.new-relationship' ).sortable( {
		items:     'li.add-new-relationship',
		cursor:    'move',
		axis:      'y',
		cancel:    'a, input, select',
		distance:  2,
		opacity:   0.9,
		tolerance: 'pointer',
		scroll:    true
	} );

	// Delegate add/remove clicks
	$( 'ul.new-relationship' ).on( 'click', 'a', function( e ) {
		var row  = $( this ).parents( 'li' ),
			rows = $( this ).parents( 'ul.new-relationship' ).children().length - 1;

		// Add
		if ( $( this ).hasClass( 'add-relationship' ) ) {

			// Clone first row
			var new_row = row.clone();

			// Remove ID so datepicker can correctly target this new element
			new_row
				.hide()
				.find( '.object-id' )
					.val( '' )
				.end()
				.find( 'input:text' )
					.val( '' )
				.end()
				.insertAfter( row )
				.fadeIn( 400 );

		// Remove (if not last row)
		} else if ( $( this ).hasClass( 'remove-relationship' ) ) {
			if ( rows > 1 ) {
				row.slideUp( 200, function() {
					row.remove();
				} );
			}
		}

		e.preventDefault();
	} );
} );
