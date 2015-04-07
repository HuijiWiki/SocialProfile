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
				rs: 'wfUserSiteFollowsResponse',
				rsargs: [username, servername]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				if (res.success){
					jQuery( '#user-site-follow').html('取消关注');
					jQuery( '#user-site-follow').addClass('unfollow');
					var count = jQuery( '#site-follower-count').html();
					count = parseInt(count)+1;
					jQuery( '#site-follower-count').html(count.toString());					
				}else{
					alert(res.message);
				}
			}
		);
	} else {
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserSiteUnfollowsResponse',
				rsargs: [username, servername]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				if ( res.success ){
					jQuery( '#user-site-follow').html('<span class="glyphicon glyphicon-plus"></span>关注</a>');
					jQuery( '#user-site-follow').removeClass('unfollow');	
					var count = jQuery( '#site-follower-count').html();
					count = parseInt(count)-1;
					if (count >= 0){
						jQuery( '#site-follower-count').html(count.toString());	
					}else{
						jQuery( '#site-follower-count').html(0);	
					}		
				}else{
					alert(res.message);
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
		if (mw.config.get('wgUserName') == null){
			window.location.href = "/wiki/Special:Login";
		}
		requestResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgServer'),
			jQuery( '#user-site-follow' ).hasClass('unfollow')
		);
	} );

} );