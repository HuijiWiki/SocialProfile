/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestResponse( username, servername, action ) {

	//TODO: add waiting message.
	//TODO: validate wgUserName.
	if (!action){
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserUserFollowsResponse',
				rsargs: [username, servername]
			},
			function( data ) {
				if (data !== 'fail'){
					jQuery( '#user-user-follow').html(data);
					jQuery( '#user-user-follow').addClass('unfollow');
					var count = jQuery( '#user-follower-count').html();
					count = parseInt(count)+1;
					jQuery( '#user-follower-count').html(count.toString());
				}
			}
		);
	} else {
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserUserUnfollowsResponse',
				rsargs: [username, servername]
			},
			function( data ) {
				if (data !== 'fail'){
					jQuery( '#user-user-follow').html(data);
					jQuery( '#user-user-follow').removeClass('unfollow');	
					var count = jQuery( '#user-follower-count').html();
					count = parseInt(count)-1;
					if (count >= 0){
						jQuery( '#user-follower-count').html(count.toString());	
					}else{
						jQuery( '#user-follower-count').html(0);	
					}		
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
	jQuery( '#user-user-follow' ).on( 'click', function() {
		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			window.location.href = "/wiki/Special:Login";
		}
		requestResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgTitle'),
			jQuery( '#user-user-follow' ).hasClass('unfollow')
		);
	} );

} );