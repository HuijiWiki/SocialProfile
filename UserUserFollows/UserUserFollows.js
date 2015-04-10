/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestUserUserFollowsResponse( follower, followee, action ) {
    var alreturn = $('.alert-return');
    var alertp = $('.alert-return p');
    function alertime(){
        alreturn.show();
        setTimeout(function(){
            alreturn.hide()
        },1000);
    }
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
                    alertime();
                    alertp.text(res.message);
					jQuery( '#user-user-follow').html('<i class="fa fa-plus-square-o"></i>关注');
				}
				alreadySubmittedUserUserFollow = false;
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
                    alertime();
                    alertp.text(res.message);
					jQuery( '#user-user-follow').html('<i class="fa fa-minus-square-o">取关');
				}
				alreadySubmittedUserUserFollow = false;
			}
		);		
	}
}

var alreadySubmittedUserUserFollow = false;

jQuery( document ).ready( function() {
	jQuery( 'li#user-user-follow' ).on( 'click', function() {
		if (alreadySubmittedUserUserFollow == true){
			return;
		}
		alreadySubmittedUserUserFollow = true;
		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
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