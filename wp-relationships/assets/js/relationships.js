jQuery( document ).ready( function( $ ) {
    'use strict';

	var relationships = $( 'ul.new-relationship' ),
		add_row       = relationships.find( 'li.add-new-relationship' ),
		none_row      = relationships.find( 'li.no-relationships' );

	// UL rows are sortable
	relationships.sortable( {
		items:       'li.relationship',
		cursor:      'move',
		axis:        'y',
		cancel:      'a, input, select',
		tolerance:   'pointer',
		containment: 'parent',
		distance:    2,
		opacity:     0.9,
		scroll:      true
	} );

	// Delegate add/remove clicks
	$( 'ul.new-relationship' ).on( 'click', 'a', function( e ) {
		var row  = $( this ).parents( 'li' ),
			rows = relationships.children( 'li.relationship' ).length;

		// Add
		if ( $( this ).hasClass( 'add-relationship' ) ) {

			// Clone first row
			var new_row = add_row.clone();

			// Remove ID so datepicker can correctly target this new element
			new_row
				.hide()
					.removeClass( 'add-new-relationship' )
					.addClass( 'relationship' )
				.find( '.object-id' )
					.val( '' )
				.end()
				.find( 'input:text' )
					.val( '' )
				.end()
				.insertAfter( row )
				.slideDown( 400 );

			// Hide row if add
			if ( rows === 0 ) {
				row.slideUp( 400 );
			}

		// Remove
		} else if ( $( this ).hasClass( 'remove-relationship' ) ) {
			row.slideUp( 400, function() {
				row.remove();
			} );

			// Show none if last row
			if ( rows === 1 ) {
				none_row.slideDown( 400 );
			}
		}

		e.preventDefault();
	} );
} );
