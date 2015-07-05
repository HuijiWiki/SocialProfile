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
	fillUsersModal: function( username, servername ){
		$.post(
			mw.util.wikiScript(), {
				action: 'ajax',
				rs: 'wfUsersFollowingSiteResponse',
				rsargs: [username, servername]
			},
			function( data ) {
                $('.follow-modal').empty();
				var res = jQuery.parseJSON(data);
				if(res.success){
					if(res.result.length==0){
						var sitename = mw.config.get('wgSiteName');
						$('.follow-modal').append('暂时还没人关注'+sitename+' >-<');
					}else{
                        var msg = '<li class="row"><span class="col-xs-6 col-md-4 col-sm-4">昵称</span><span class="hidden-xs col-md-4 col-sm-4">等级</span><span class="col-xs-6 col-md-4 col-sm-4">编辑次数</span></li>'
                        $('.follow-modal').append(msg);
                        if(res.result.length>4) {
                            var i;
                            for( i=0;i<4;i++ ){
                                var msg = '<li class="row"><span class="col-xs-6 col-md-4 col-sm-4 modal-user-info"><a href="' + res.result[i].userUrl + '" class="follow-modal-headimg">' + res.result[i].url + '</a><a href="' + res.result[i].userUrl + '" class="follow-modal-username">' + res.result[i].user + '</a></span><span class="follow-modal-level hidden-xs col-md-4 col-sm-4">' + res.result[i].level + '</span><span class="follow-modal-editnum col-xs-6 col-md-4 col-sm-4">' + res.result[i].count + '</span></li>';
                                $('.follow-modal').append(msg);
                            }

                            $('.follow-modal').append('<div class="follow-modal-more"><a href="/wiki/Special:EditRank">更多</a></div>');
                        }
                        else{
                            $.each(res.result,
                                function (i, item) {
                                    var msg = '<li class="row"><span class="col-xs-6 col-md-4 col-sm-4 modal-user-info"><a href="' + item.userUrl + '" class="follow-modal-headimg">' + item.url + '</a><a href="' + item.userUrl + '" class="follow-modal-username">' + item.user + '</a></span><span class="follow-modal-level hidden-xs col-md-4 col-sm-4">' + item.level + '</span><span class="follow-modal-editnum col-xs-6 col-md-4 col-sm-4">' + item.count + '</span></li>';
                                    $('.follow-modal').append(msg);
                                }
                            );
                        }
                    }
				}else{
					userSiteFollows.alerttime();
                    userSiteFollows.alertp.text(res.message);
				}
			}
		);
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


	$( '.modal' ).on('click', '.user-site-follow-from-modal', function(event) {	
		event.preventDefault();
		var that = $(this);
		var server = that.parent().attr('href');
		server = server.replace('http://','').replace('.huiji.wiki','');
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

	$('#site-follower-count').click(function(){
		var user = mw.config.get('wgUserName');
		var site_name = mw.config.get('wgHuijiPrefix');
		userSiteFollows.fillUsersModal(user, site_name);
        $('.follow-modal').empty().append('<i class="fa fa-spinner fa-spin fa-5x"></i>');
	});
} );
