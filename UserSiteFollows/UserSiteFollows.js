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

		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		alreadySubmittedUserSiteFollow = true;
		requestUserSiteFollowsResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgServer'),
			jQuery( '#user-site-follow' ).hasClass('unfollow')
		);
	} );


	jQuery( '.modal' ).on( 'click','.user-href-follow', function() {
		var that = $(this);
		var server = 'http://' + that.parent().find('a').attr('href');
		console.log(server);
		if (alreadySubmittedUserSiteFollow == true){
			return;
		}

		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		console.log(mw.config.get('wgUserName'));
		console.log(server);
		alreadySubmittedUserSiteFollow = true;
		requestUserHrefFollowsResponse(
			mw.config.get('wgUserName'),
			server,
			that.hasClass('unfollow')
		);
		// wikis alert window
		function requestUserHrefFollowsResponse( username, servername, action ) {

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
							that.html('取关');
							that.addClass('unfollow');
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
							that.html('<span class="glyphicon glyphicon-plus"></span>关注</a>');
							that.removeClass('unfollow');	
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
	} );



	$('#site-follower-count').click(function(){
		var alreturn = $('.alert-return');
	    var alertp = $('.alert-return p');
        $('.follow-modal').empty();
	    function alertime(){
	        alreturn.show();
	        setTimeout(function(){
	            alreturn.hide()
	        },1000);
	    }
		var user = mw.config.get('wgUserName');
		var site_name = mw.config.get('wgServer');
		$.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserFollowsSiteResponse',
				rsargs: [user, site_name]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				console.log(user);
				console.log(site_name);
				console.log(res);
				if(res.success){
					$.each(res.result,
						function(i,item){
							if (item.is_follow == 'Y') {
								var msg = '<li><a href="'+item.userUrl+'">'+item.url+'</a><a href="'+item.userUrl+'">'+item.user+'</a>编辑次数：'+item.count+'<i>(已关注)</i></li>';
							}else{
								var msg = '<li><a href="'+item.userUrl+'">'+item.url+'</a><a href="'+item.userUrl+'">'+item.user+'</a>编辑次数：'+item.count+'</li>';
							}
							$('.follow-modal').append(msg);
						}
						
					);
				}else{
					alertime();
                    alertp.text(res.message);
				}
			});
	});

} );

function requestUserFollowsSiteResponse( username, servername) {

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
		jQuery.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUserFollowsSiteResponse',
				rsargs: [username, servername]
			},
			function( data ) {
				var res = jQuery.parseJSON(data);
				if (res.success){
									
				}else{
                    alertime();
                    alertp.text(res.message);
				}
				alreadySubmittedUserSiteFollow = false;
			}
		);
}






