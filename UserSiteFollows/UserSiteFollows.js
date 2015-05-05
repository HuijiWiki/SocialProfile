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
                    if($('#user-site-follow').hasClass('mw-ui-progressive')){
                        $('#user-site-follow').removeClass('mw-ui-progressive');
                    }else{
                        $('#user-site-follow').addClass('mw-ui-progressive');
                    }
                    $( '#user-site-follow').html('取消关注');
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
                    $( '#user-site-follow').html('<span class="glyphicon glyphicon-plus"></span>关注</a>');
                    if($('#user-site-follow').hasClass('mw-ui-progressive')){
                        $('#user-site-follow').removeClass('mw-ui-progressive');
                    }else{
                        $('#user-site-follow').addClass('mw-ui-progressive');
                    }
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
        $( '#user-site-follow').html('<i class="fa fa-spinner fa-pulse"></i>');
        alreadySubmittedUserSiteFollow = true;
        requestUserSiteFollowsResponse(
            mw.config.get('wgUserName'),
            mw.config.get('wgServer'),
            jQuery('#user-site-follow').hasClass('unfollow')
        );
	} );


	$( '.modal' ).on('click', '.user-href-follow', function(event) {	
		event.preventDefault();
		var that = $(this);
		var server = that.parent().attr('href');
		if (alreadySubmittedUserSiteFollow == true){
			return;
		}

		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		alreadySubmittedUserSiteFollow = true;
		requestUserHrefFollowsResponse(
			mw.config.get('wgUserName'),
			server,
			that.hasClass('unfollow'),
			that
		);
		// wikis alert window
		function requestUserHrefFollowsResponse( username, servername, action, element ) {

			//TODO: add waiting message.
			//TODO: validate wgUserName.
			var mElement = element;
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
							mElement.html('取关');
							mElement.addClass('unfollow');
							// if (servername == mw.config.get('wgServer')){
							// 	var count = jQuery( '#site-follower-count').html();
							// 	count = parseInt(count)+1;
							// 	jQuery( '#site-follower-count').html(count.toString());										
							// }
			
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
							mElement.html('关注');
							mElement.removeClass('unfollow');	
							
							// if (servername == mw.config.get('wgServer')){
								// var count = jQuery( '#site-follower-count').html();
								// count = parseInt(count)-1;
							// 	if (count >= 0){
							// 		jQuery( '#site-follower-count').html(count.toString());	
							// 	}else{
							// 		jQuery( '#site-follower-count').html(0);	
							// 	}	
							// }	
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






