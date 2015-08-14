/**
 * JavaScript for UserSiteFollow
 * Used on Sidebar.
 */

function requestUserUserFollowsResponse( follower, followee, action,btn ) {
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
                    btn.html('<a><i class="fa fa-minus-square-o"></i> 取关</a>');
                    btn.addClass('unfollow');
                    var count = jQuery( '#user-follower-count').html();
                    count = parseInt(count)+1;
                    jQuery( '#user-follower-count').html(count.toString());
                }else{
                    alertime();
                    alertp.text(res.message);
                    btn.html('<a><i class="fa fa-plus-square-o"></i> 关注</a>');
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
                    btn.html('<a><i class="fa fa-plus-square-o"></i> 关注</a>');
                    btn.removeClass('unfollow');
                    var count = jQuery( '#user-follower-count').html();
                    count = parseInt(count)-1;
                    if (count >= 0){
                        jQuery( '#user-follower-count').html(count.toString());
                    }else{
                        jQuery( '#user-follower-count').html(0);
                    }
                }else{
                    alertime();
                    alertp.text(res.message);
                    jQuery( '#user-user-follow').html('<a><i class="fa fa-minus-square-o"></i> 取关</a>');
                }
                alreadySubmittedUserUserFollow = false;
            }
        );
    }
}

var alreadySubmittedUserUserFollow = false;

jQuery( document ).ready( function() {

    $('li#user-user-follow').on('click',function(){
        var that = $(this);
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
            mw.config.get('wgTitle'),
            that.hasClass('unfollow'),
            that
        );
    });
    $('body').on('click','.user-user-follow',function(){
        var that = $(this);
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
            that.hasClass('unfollow'),
            that
        );
    });
});