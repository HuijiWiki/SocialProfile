
mw.forumlist = function ( option, callback ){
	var count, site, user;
	count = option.count || 5;
	site = option.site || '';
	user = option.user || '';
	mode = option.mode || 'list';
	var myTemplate = mw.template.get('ext.socialprofile.forumlist', 'forumlist.mustache');
	var param = {};
	if (site != ''){
		param['filter[q]'] = 'tag:'+site;
	} 
	if (user != ''){
		param['filter[q]'] = 'username:'+user;
	}
	$.ajax({
		url: 'http://forum.huiji.wiki/api/discussions',
		data: param,
		type: 'GET',
		success: function (res){
			var discussions = res.data;

			if ( discussions.length == 0 ){
				var nodiscussions = '<ul class="forumlist"><a class="empty-message">最近没有人发过贴呦~<a></ul>';
				callback($(nodiscussions));
				return;
			}
			
			if ( mode == 'list' ){
				var modelist = '<ul class="forumlist">';
				for( var pipe in discussions){
					modelist += '<li><a href="http://forum.huiji.wiki/d/' + encodeURIComponent(discussions[pipe].id) + '">' + discussions[pipe].attributes.title + '</a></li>';
				}
				modelist += '</ul>';
				callback($(modelist));
				return;
			}
			else {
				var included = res.included;
				var lookup = function(data){
					var type = data.type;
					var id = data.id;
					for (var key in included){
						if (included[key].type === type && included[key].id === id){
							return included[key].attributes;
						}

					}
				}
				var alteredDiscussion = [];
				for (var pipe in discussions){
					if (discussions[pipe].attributes.commentsCount === 1){
						var displayAuthor = lookup( discussions[pipe].relationships.startUser.data ).username + '发布于';
					} else {
						var displayAuthor = lookup( discussions[pipe].relationships.lastUser.data ).username + '回复于';
					}
					if (discussions[pipe].relationships.startPost){
						var displayContent = lookup(discussions[pipe].relationships.startPost.data).contentHtml;
					} else {
						var displayContent = '';
					}
					if (lookup( discussions[pipe].relationships.startUser.data ).avatarUrl == ''){
						var displayAvatar = lookup( discussions[pipe].relationships.startUser.data ).avatarUrl;
					} else {
						var displayAvatar = "http://av.huijiwiki.com/default_ml.gif";
					}
					var displayDate = new Date(discussions[pipe].attributes.lastTime);
					alteredDiscussion.push({
						url: "http://forum.huiji.wiki/d/" + encodeURIComponent( discussions[pipe].id ),
						heading: discussions[pipe].attributes.title,
						author: displayAuthor,
						image: displayAvatar,
						imageAlt: lookup(discussions[pipe].relationships.startUser.data).username,
						timestamp: displayDate.toLocaleString(),
						content: displayContent
					});	
									
				}
				$html = myTemplate.render({discussions: alteredDiscussion});
				callback($html);	
				//done;
			}

		}
	})

}