/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestUserSiteFollowsResponse( username, servername, action ) {

	//TODO: add waiting message.
	//TODO: validate wgUserName.
    var alreturn = $('.alert-return');
    var alertp = $('.alert-return p');
    function alertime(){
        alreturn.show();
        setTimeout(function(){
            alreturn.hide()
        },1000);
    }
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
                    alertime();
                    alertp.text(res.message);
				}
				alreadySubmittedUserSiteFollow = false;
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
                    alertime();
                    alertp.text(res.message);
				}
				alreadySubmittedUserSiteFollow = false;
			}

		);		
	}
}
var alreadySubmittedUserSiteFollow = false;
jQuery( document ).ready( function() {
	jQuery( '#user-site-follow' ).on( 'click', function() {
		if (alreadySubmittedUserSiteFollow == true){
			return;
		}
		alreadySubmittedUserSiteFollow = true;
		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			window.location.href = "/wiki/Special:Login";
		}
		requestUserSiteFollowsResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgServer'),
			jQuery( '#user-site-follow' ).hasClass('unfollow')
		);
	} );

} );