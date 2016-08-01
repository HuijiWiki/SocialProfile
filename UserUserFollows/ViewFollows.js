var WikiUserFollow = {
	theNextPage: '',
	theScroll: 0,
	userId: 0, //of the listed user
	username: '', //of the current viewpoint user

	init: function() {

	},
	OOjsPanel: function() {
		var panel = new OO.ui.PanelLayout({
			expanded: false,
			framed: false,
			padded: false,
			$content: $('<div></div>'),
			classes: ['UserFollowSelf'],
		});
		$('#mw-content-text').append(panel.$element);
		return panel;
	},
	UserNameAjax: function(num, next) {
		if (this.rel_type == 1 || this.rel_type == ''){
			var API = '/api.php?action=query&list=allhuijiusers&aufollowing=' + this.username + '&aulimit=' + num + '&format=json&aufrom=' + next;
		} else {
			var API = '/api.php?action=query&list=allhuijiusers&aufollowedby=' + this.username + '&aulimit=' + num + '&format=json&aufrom=' + next;
		}
		
		var allUser = [];
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("get", API, false);
		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
				var data = JSON.parse(xmlHttp.responseText);
				var theData = data.query.allhuijiusers;
				//console.log(data.continue.aufrom)
				if (data.continue) {
					WikiUserFollow.theNextPage = data.continue.aufrom || '';
				}
				for (var i = 0; i < theData.length; i++) {
					allUser[i] = theData[i].name;
				}
			}
		}
		xmlHttp.send(null);
		allUser = allUser.join('|');
		return allUser;
	},
	UserInfoAjax: function(userList) {
		var API = '/api.php?action=query&list=huijiusers&ususers=' + userList + '&usprop=context|avatar|gender|context|editcount|gender|status|followingcount|followercount|level|stats|&format=json';
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("get", API, false);
		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
				$('.spinner').html('').remove();
				var data = JSON.parse(xmlHttp.responseText);
				var theData = data.query.huijiusers;
				WikiUserFollow.OOjsPanel();
				if ($('.UserFollow').length > 0) {

				} else {
					$('#mw-content-text').append('<div class="UserFollow"></div>');
				}
				for (var i = 0; i < theData.length; i++) {
					//$('.aufollowing').append('<li class="list-group-item">' + theData[i].name + '<span style="color:green;float:right;">' + theData[i].gender + '</span></li>')
					var panel = new OO.ui.PanelLayout({
						expanded: false,
						framed: false,
						padded: false,
						$content: $(''),
						classes: ['UserFollow_Self' + WikiUserFollow.userId, 'UserFollow_Self'],
					});
					$('.UserFollow').append(panel.$element);
					$('.UserFollow_Self' + WikiUserFollow.userId).append('<div class="UserFollow_Self_left">' +
						'<img src="http://av.huijiwiki.com/default_ml.gif" />' +
						'</div>');
					$('.UserFollow_Self' + WikiUserFollow.userId).append('<div class="UserFollow_Self_right">' +
						'<a class="mw-userlink UserFollow_Self_right_name" title="用户:' + theData[i].name + '" href="/wiki/User:' + encodeURIComponent(theData[i].name) + '">' + theData[i].name + '</a>' + '<span class="icon-lv' + theData[i].level + '"></span>' +
						'<div class="UserFollow_Self_right_count">被关注:' + '<a href="/wiki/Special:ViewFollows&user='+encodeURIComponent(theData[i].name)+'&rel_type=2">' + theData[i].followingcount + '</a>&nbsp;&nbsp;&nbsp;' + '|&nbsp;&nbsp;&nbsp;关注数:' + '<a href="/wiki/Special:ViewFollows&user='+encodeURIComponent(theData[i].name)+'&rel_type=1">' + theData[i].followercount + '</a>&nbsp;&nbsp;&nbsp;' + ' |&nbsp;&nbsp;&nbsp;编辑:<a href="/wiki/Special:Contribution&target="'+encodeURIComponent(theData[i].name)+'&contribs=user">' + theData[i].stats.edits + '</a>&nbsp;&nbsp;&nbsp;</div>' +
						'<div class="UserFollow_Self_right_dec">' + theData[i].status + '</div>' +
						'</div>');
					var button0 = new OO.ui.ButtonWidget({
						label: '关注',
						icon: 'add',
						classes: ['UserFollowBtn_follow'+ WikiUserFollow.userId,'UserFollowBtn_follow'],
					});
					var button1 = new OO.ui.ButtonWidget({
						label: '取关',
						icon: 'close',
						classes: ['UserFollowBtn_unfollow'+ WikiUserFollow.userId,'UserFollowBtn_unfollow'],
					});
					var button2 = new OO.ui.ButtonWidget({
						label: '礼物',
						icon: 'window',
						href: 'http://www.huiji.wiki/index.php?title=%E7%89%B9%E6%AE%8A:GiveGift&user='+encodeURIComponent(theData[i].name),
						classes: ['UserFollowBtn_gift'],
					});
					$('.UserFollow_Self' + WikiUserFollow.userId + ' .UserFollow_Self_right').append(button0.$element, button1.$element, button2.$element);
					if (theData[i].context.followedbyme == 'true') {
						$('.UserFollowBtn_unfollow'+ WikiUserFollow.userId).show();
					} else {
						$('.UserFollowBtn_follow'+ WikiUserFollow.userId).show();
					}
					WikiUserFollow.userId++;
				}
			}
		}
		xmlHttp.send(null);
	},
	FollowUserBtn: function() {
		$('.UserFollowBtn_follow').on('click', function() {
			var _this = $(this)
			if (mw.config.get('wgUserName')) {
				var API = '/api.php?action=useruserfollow&follower=' + mw.config.get('wgUserName') + '&followee=' + $(this).siblings('.UserFollow_Self_right_name').html() + '&format=json';
				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open("post", API, true);
				xmlHttp.send(API);
				xmlHttp.onreadystatechange = function() {
					if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
						//alert('关注成功！');
						_this.hide();
						_this.siblings('.UserFollowBtn_unfollow').show();
					}
				}
			} else {
				$('#pt-login').trigger('click');
			}
		})
	},
	UnFollowUserBtn: function() {
		$('.UserFollowBtn_unfollow').on('click', function() {
			var _this = $(this)
			if (mw.config.get('wgUserName')) {
				var API = '/api.php?action=useruserunfollow&follower=' + mw.config.get('wgUserName') + '&followee=' + $(this).siblings('.UserFollow_Self_right_name').html() + '&format=json';
				console.log(API)
				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open("post", API, true);
				xmlHttp.send(API);
				xmlHttp.onreadystatechange = function() {
					if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
						//alert('关注成功！');
						_this.hide();
						_this.siblings('.UserFollowBtn_follow').show();
					}
				}
			} else {
				$('#pt-login').trigger('click');
			}
		})
	},
	ScrollAjax: function() {
		$(window).scroll(function() {　
			var scrollTop = $(this).scrollTop();
			var scrollHeight = $(document).height();　　
			var windowHeight = $(this).height();　　
			if (scrollTop + windowHeight == scrollHeight && WikiUserFollow.theScroll == 0 && _thisInput.getValue() == '') {
				WikiUserFollow.theScroll = 1;
				$('#mw-content-text').append('<div class="spinner">' +
					'<div class="rect1"></div>' +
					'<div class="rect2"></div>' +
					'<div class="rect3"></div>' +
					'<div class="rect4"></div>' +
					'<div class="rect5"></div>' +
					'</div>');
				window.setTimeout(function() {
					if (WikiUserFollow.theNextPage != ''){
						WikiUserFollow.UserInfoAjax(WikiUserFollow.UserNameAjax(20, WikiUserFollow.theNextPage));
					}
					WikiUserFollow.theScroll = 0;
				}, 800);
			}
		});
	},
	OOjs: function() {
		var textInput = new OO.ui.TextInputWidget({
			type: 'search',
			icon: 'search',
			value: '',
			classes: ['searchInput']
		});
		return textInput;
	},
	Search: function() {
		var allUser = [];
		_thisInput = WikiUserFollow.OOjs();
		$('#firstHeading').after(_thisInput.$element);
		$('.searchInput').on('keyup', function() {
			var _inputValue = _thisInput.getValue().toUpperCase();
			if (_inputValue) {
				var API = '/api.php?action=query&list=allhuijiusers&aufollowing=Volvo&aulimit=10&format=json&aufrom=' + _inputValue;
				console.log(API)
				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open("get", API, false);
				xmlHttp.onreadystatechange = function() {
					if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
						console.log('hahahah')
						$('.UserFollow').html('').remove();
						var data = JSON.parse(xmlHttp.responseText);
						var theData = data.query.allhuijiusers;
						if (data.continue) {
							WikiUserFollow.theNextPage = data.continue.aufrom;
						}
						for (var i = 0; i < theData.length; i++) {
							allUser[i] = theData[i].name;
						}
						allUser = allUser.join('|');
					}
				}
				xmlHttp.send(null);
				WikiUserFollow.UserInfoAjax(allUser);
			} else {
				$('.UserFollow').html('').remove();
				WikiUserFollow.UserInfoAjax(WikiUserFollow.UserNameAjax(10, ''));
			}
		})
	},
	getParameterByName: function(name, url) {
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, "\\$&");
	    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
	        results = regex.exec(url);
	    if (!results) return null;
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, " "));
	}
}
$(function() {
	mw.loader.using('oojs-ui').done(function() {
		WikiUserFollow.username = WikiUserFollow.getParameterByName('user') || mw.config.get('wgUserName');
		WikiUserFollow.rel_type = WikiUserFollow.getParameterByName('rel_type') || 1;
		WikiUserFollow.UserInfoAjax(WikiUserFollow.UserNameAjax(8, ''));
		WikiUserFollow.ScrollAjax();
		WikiUserFollow.Search();
		WikiUserFollow.FollowUserBtn();
		WikiUserFollow.UnFollowUserBtn();
	});

})