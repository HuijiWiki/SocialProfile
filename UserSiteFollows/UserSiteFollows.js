/* javaScript for UserSiteFollow
 * Used on Sidebar.
 */
var userSiteFollows = {
	submitted : false,
	follow: function( username, servername, action ){
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
						jQuery( '#user-site-follow').addClass('unfollow').removeClass('mw-ui-progressive');
						var count = jQuery( '#site-follower-count').html();
						count = parseInt(count)+1;
						jQuery( '#site-follower-count').html(count.toString());					
					}else{
	                    userSiteFollows.alerttime();
	                    userSiteFollows.alertp.text(res.message);
					}
					userSiteFollows.submitted = false;
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
						jQuery( '#user-site-follow').removeClass('unfollow').addClass('mw-ui-progressive');	
						var count = jQuery( '#site-follower-count').html();
						count = parseInt(count)-1;
						if (count >= 0){
							jQuery( '#site-follower-count').html(count.toString());	
						}else{
							jQuery( '#site-follower-count').html(0);	
						}		
					}else{
	                    userSiteFollows.alerttime();
	                    userSiteFollows.alertp.text(res.message);
					}
					userSiteFollows.submitted = false;
				}

			);		
		}
	},
	followFromModal: function(username, servername, action, element){
		var mElement = element;
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
						mElement.html('取关');
						mElement.addClass('unfollow');
						var count = jQuery( '#site-following-count').html();
						count = parseInt(count)+1;
						jQuery( '#site-following-count').html(count.toString());					
					}else{
	                    userSiteFollows.alerttime();
	                    userSiteFollows.text(res.message);
					}
					userSiteFollows.submitted = false;
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
						mElement.html('关注');
						mElement.removeClass('unfollow');	
						var count = jQuery( '#site-following-count').html();
						count = parseInt(count)-1;
						if (count >= 0){
							jQuery( '#site-following-count').html(count.toString());	
						}else{
							jQuery( '#site-following-count').html(0);	
						}		
					}else{
	                    userSiteFollows.alerttime();
	                    thuserSiteFollowsis.alertp.text(res.message);
					}
					userSiteFollows.submitted = false;
				}

			);		
		}
	},
    alertp : $('.alert-return p'),
    alerttime : function(){
        alreturn.show();
        setTimeout(function(){
            alreturn.hide()
        },1000);
    }	

}

jQuery( document ).ready( function() {
	jQuery( '#user-site-follow' ).on( 'click', function() {
		if (userSiteFollows.submitted == true){
			return;
		}

		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		userSiteFollows.submitted = true;
		userSiteFollows.follow(
			mw.config.get('wgUserName'),
			mw.config.get('wgHuijiPrefix'),
			jQuery( '#user-site-follow' ).hasClass('unfollow')
		);
	} );


	$( '.modal,.top-users' ).on('click', '.user-site-follow-from-modal', function(event) {
		event.preventDefault();
		var that = $(this);
		var server = that.parent().attr('href');
		server = server.split('//')[1].split('.')[0];
		if (userSiteFollows.submitted == true){
			return;
		}
		//Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		userSiteFollows.submitted = true;
		userSiteFollows.followFromModal(
			mw.config.get('wgUserName'),
			server,
			that.hasClass('unfollow'),
			that
		);

	} );
} );
