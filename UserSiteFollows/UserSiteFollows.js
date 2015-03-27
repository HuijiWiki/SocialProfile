/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestResponse( response, username, servername, action ) {

	//TODO: add waiting message.
	//TODO: validate wgUserName.
	if (!action){
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserSiteFollowsResponse',
				rsargs: [response, username, servername]
			},
			function( data ) {
				if (data !== 'fail'){
					jQuery( '#user-site-follow').innerHTML = data;
					jQuery( '#user-site-follow').addClass('unfollow');
				}
			}
		);
	} else {
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserSiteUnfollowsResponse',
				rsargs: [response, username, servername]
			},
			function( data ) {
				if (data !== 'fail'){
					jQuery( '#user-site-follow').innerHTML = data;
					jQuery( '#user-site-follow').removeClass('unfollow');				
				}
			}
		);		
	}
}

jQuery( document ).ready( function() {
	// These works should be done in skin beforehand:
	//TODO: $out->addModules( 'ext.socialprofile.userrelationship.js' ); (put this on skin)
	//TODO: Check if user is logged in.
	//TODO: if user is logged in, check if user has followed site.
	jQuery( '#user-site-follow' ).on( 'click', function() {
		//TODO: Check if user is logged in, if not prompt login form.

		requestResponse(
			jQuery( this ).data( 'response' ),
			mw.config.get('wgUserName'),
			mw.config.get('wgServer'),
			jQuery( '#user-site-follow' ).hasClass('unfollow')
		);
	} );

} );