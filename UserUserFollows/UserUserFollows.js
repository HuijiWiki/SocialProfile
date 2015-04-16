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
					jQuery( '#user-user-follow').html('<a><i class="fa fa-minus-square-o">取关</i></a>');
					jQuery( '#user-user-follow').addClass('unfollow');
					var count = jQuery( '#user-following-count').html();
					count = parseInt(count)+1;
					jQuery( '#user-following-count').html(count.toString());
				}else{
                    alertime();
                    alertp.text(res.message);
					jQuery( '#user-user-follow').html('<a><i class="fa fa-plus-square-o"></i>关注</a>');
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
					jQuery( '#user-user-follow').html('<a><i class="fa fa-plus-square-o"></i>关注</a>');
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
					jQuery( '#user-user-follow').html('<a><i class="fa fa-minus-square-o">取关</i></a>');
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
		
		//TODO: Check if user is logged in, if not prompt login form.
		if (mw.config.get('wgUserName') == null){
			$('.user-login').modal();
			return;
		}
		alreadySubmittedUserUserFollow = true;
		jQuery( '#user-user-follow').html('<a><i class="fa fa-spinner fa-pulse"></i></a>');
		requestUserUserFollowsResponse(
			mw.config.get('wgUserName'),
			mw.config.get('wgTitle'),
			jQuery( '#user-user-follow' ).hasClass('unfollow')
		);
	} );

    $('.user-user-follow').on('click',function(){
        var that = $(this);
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
                            that.html('<a><i class="fa fa-minus-square-o">取关</i></a>');
                            that.addClass('unfollow');
                            var count = jQuery( '#user-following-count').html();
                            count = parseInt(count)+1;
                            jQuery( '#user-following-count').html(count.toString());
                        }else{
                            alertime();
                            alertp.text(res.message);
                            that.html('<a><i class="fa fa-plus-square-o"></i>关注</a>');
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
                            that.html('<a><i class="fa fa-plus-square-o"></i>关注</a>');
                            that.removeClass('unfollow');
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
                            that.html('<a><i class="fa fa-minus-square-o">取关</i></a>');
                        }
                        alreadySubmittedUserUserFollow = false;
                    }
                );
            }
        }
        if (alreadySubmittedUserUserFollow == true){
            return;
        }

        if (mw.config.get('wgUserName') == null){
            $('.user-login').modal();
            return;
        }
        alreadySubmittedUserUserFollow = true;
        that.html('<a><i class="fa fa-spinner fa-pulse"></i></a>');
        var followee = that.attr("data-username");
        requestUserUserFollowsResponse(
            mw.config.get('wgUserName'),
            followee,
            that.hasClass('unfollow')
        );
    });
} );