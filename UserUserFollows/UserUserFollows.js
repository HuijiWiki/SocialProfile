/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestUserUserFollowsResponse( follower, followee, action ) {

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
					jQuery( '#user-user-follow').html('<i class="fa fa-minus-square-o">取关');
					jQuery( '#user-user-follow').addClass('unfollow');
					var count = jQuery( '#user-following-count').html();
					count = parseInt(count)+1;
					jQuery( '#user-following-count').html(count.toString());
				}else{
					alert(res.message);
					jQuery( '#user-user-follow').html('<i class="fa fa-plus-square-o"></i>关注');
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
					jQuery( '#user-user-follow').html('<i class="fa fa-plus-square-o"></i>关注');
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
					jQuery( '#user-user-follow').html('<i class="fa fa-minus-square-o">取关');
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
		jQuery( '#user-user-follow').html('<i class="fa fa-spinner fa-pulse"></i>');
		requestUserUserFollowsResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgTitle'),
			jQuery( '#user-user-follow' ).hasClass('unfollow')
		);
	} );

} );