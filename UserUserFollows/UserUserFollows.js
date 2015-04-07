/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestResponse( follower, followee, action ) {

	//TODO: add waiting message.
	//TODO: validate wgUserName.
	if (!action){
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserUserFollowsResponse',
				rsargs: [follower, followee]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				if (res.success){
					jQuery( '#user-user-follow').html('<span class="glyphicon glyphicon-plus"></span>关注</a>');
					jQuery( '#user-user-follow').addClass('unfollow');
					var count = jQuery( '#user-following-count').html();
					count = parseInt(count)+1;
					jQuery( '#user-following-count').html(count.toString());
				}else{
					alert(res.message);
				}
			}
		);
	} else {
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserUserUnfollowsResponse',
				rsargs: [follower, followee]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				if (res.success){
					jQuery( '#user-user-follow').html('取消关注');
					jQuery( '#user-user-follow').removeClass('unfollow');	
					var count = jQuery( '#user-following-count').html();
					count = parseInt(count)-1;
					if (count >= 0){
						jQuery( '#user-following-count').html(count.toString());	
					}else{
						jQuery( '#user-following-count').html(0);	
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
	jQuery( 'li#user-user-follow' ).on( 'click', function() {
		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			window.location.href = "/wiki/Special:Login";
			return;
		}
		requestResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgTitle'),
			jQuery( '#user-user-follow' ).hasClass('unfollow')
		);
	} );

} );