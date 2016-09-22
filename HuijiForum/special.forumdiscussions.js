$(document).ready(function(){
	mw.loader.using('ext.socialprofile.forumlist').done(function(){
		$(".forumlist-container").each(function(){
			$this = $(this);
			mw.forumlist(
				{
					count: $this.data('count'),
					mode: $this.data('mode'),
					user: $this.data('user'),
					site: mw.config.get('wgHuijiPrefix')
				}, 
				function(html){
					$this.append(html);
				}
			);
		});

	});
});