/* javaScript for UserSiteFollow
 * Used on Sidebar.
 */
var userSiteFollows = {
	appended : false,
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

	$( '#pt-following > a' ).click(function(e){
		e.preventDefault();
	});
	
	$( '#pt-following' ).click(function(e){
		// if (window.is_mobile_device() ){
		// 	return;
		// }
		$( '#pt-following > a' ).attr('href', '');
		$ignore = $(this);
		if (userSiteFollows.appended){
			userSiteFollows.appended.toggle();
			return;
		}
		mw.loader.using(['oojs-ui','ext.echo.ui'], function(){
			username = mw.config.get("wgUserName");
			specialpage = "/wiki/Special:关注的站点?user_id="+mw.config.get("wgUserId")+"&target_user_id="+mw.config.get("wgUserId");
			jQuery.post(
					mw.util.wikiScript(), {
						action: 'ajax',
						rs: 'wfUserSiteFollowsDetailsResponse',
						rsargs: [username, username]
					},
					function( data ) {
						var header = new OO.ui.LabelWidget({
							label: '我关注的维基'
						});
						var dummyIcon = new OO.ui.IconWidget({
							icon: 'Next',
							iconTitle: 'dummy'
						});
						var moreIcon = new OO.ui.IconWidget( {
  							icon: 'Next',
  							iconTitle: '更多'
						} );
						var footer = new OO.ui.LabelWidget({
							label: $('<div class="oo-ui-widget oo-ui-widget-enabled oo-ui-buttonGroupWidget" aria-disabled="false"><div class="mw-echo-ui-notificationBadgeButtonPopupWidget-footer-allnotifs oo-ui-widget oo-ui-widget-enabled oo-ui-buttonElement oo-ui-buttonElement-framed oo-ui-iconElement oo-ui-labelElement oo-ui-buttonWidget" aria-disabled="false"><a class="oo-ui-buttonElement-button" role="button" aria-disabled="false" href="'+specialpage+'" rel="nofollow"><span class="oo-ui-iconElement-icon oo-ui-icon-next"></span><span class="oo-ui-labelElement-label">所有关注</span><span class="oo-ui-indicatorElement-indicator"></span></a></div><div class="mw-echo-ui-notificationBadgeButtonPopupWidget-footer-preferences oo-ui-widget oo-ui-widget-enabled oo-ui-buttonElement oo-ui-buttonElement-framed oo-ui-iconElement oo-ui-labelElement oo-ui-buttonWidget" aria-disabled="false"><a class="oo-ui-buttonElement-button" role="button" tabindex="0" aria-disabled="false" href="/wiki/%E7%89%B9%E6%AE%8A:%E5%8F%82%E6%95%B0%E8%AE%BE%E7%BD%AE#mw-prefsection-echo" rel="nofollow"><span class="oo-ui-iconElement-icon oo-ui-icon-advanced"></span><span class="oo-ui-labelElement-label">设置</span><span class="oo-ui-indicatorElement-indicator"></span></a></div></div>')
						});
						var out = '<div class="mw-echo-ui-notificationsWidget">';
						console.log(data);
						data = JSON.parse(data);
						if (data.success){
							if (data.result.length > 0){
								for (var i in data.result){
									out += '<div class="oo-ui-optionWidget oo-ui-labelElement mw-echo-ui-notificationOptionWidget">';
									var target = 'http://'+data.result[i].key+mw.config.get('wgHuijiSuffix');
									out += '<a class="mw-echo-ui-notificationOptionWidget-linkWrapper" href="'+target+'">';
									out += "<div class='mw-echo-ui-notificationOptionWidget mw-echo-state'>";
									out += '<img class="mw-echo-icon" src="http://av.huijiwiki.com/site_avatar_'+data.result[i].key+'_m.png">';
									out += "<span class='oo-ui-lableElement-label'>";
									out += "<div class='mw-echo-content'>";
									out += "<div class='mw-echo-title'>";
									out += "<span href='http://";
									out += data.result[i].key;
									out += mw.config.get('wgHuijiSuffix')+"'>"+data.result[i].val+"</span>";
									out += "</div>";
									out += "<div class='mw-echo-notification-footer'>编辑了"+data.result[i].count+"次</div>";
									out += "</div>";
									out += "</span>";
									out += "</div>";
									out += "</a>";
									out += "</div>";
								}
								out += "</div>";
								console.log(out);

							} else {
								out += "<div class='top-users'><li>您关注的维基会出现在这里~</li></div>";
							}
						
						} else {
							out += "<div><li>您关注的维基会出现在这里~</li></div>";
						}
						// Set the 'anchor' config option to 'false' to remove the triangular anchor that points toward the popup origin.
						var popup = new OO.ui.PopupWidget( {
						  $content: $( out ),
						  padded: false,
						  $container: $('body'),
						  anchor: true,
						  head:true,
						  autoClose: true,
						  classes: "following",
						  $autoCloseIgnore: $ignore,
						  label: dummyIcon.$element,
						  align: "force-right",
						  $footer: footer.$element
						} );
						popup.$label.append(header.$element);
						popup.closeButton.$element.remove();
						if (!userSiteFollows.appended){
							userSiteFollows.appended = popup;
							$ignore.append( popup.$element );
						}
						popup.toggle();

					}
			);
		});


	});
	

} );
