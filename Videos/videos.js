function checkName(title,url,token,video_from, video_id, video_player_url, video_tags, video_duration, n){
    $.post('/api.php',{
        action:'upload',
        filename: title+n+'.video',
        url: url,
        token: token,
        format: 'json'
    },function(data){
        if(data.upload) {
            if (data.upload.warnings) {
                if(data.upload.warnings.exists) {
                    if (n == '')
                        n = 0;
                    n++;
                    checkName(title, url, token, video_from, video_id, video_player_url, video_tags, video_duration, n);
                }else{
                    $.post('/api.php',{
                        action:'upload',
                        filename: title+n+'.video',
                        url: url,
                        token: token,
                        ignorewarnings: true,
                        format: 'json'
                    },function(data){
                        $.post(
                            mw.util.wikiScript(), {
                                action: 'ajax',
                                rs: 'wfinsertVideoInfo',
                                rsargs: [video_from, title + n + '.video', video_id, title + n, video_player_url, video_tags, video_duration]
                            },
                            function (data) {
                                var res = jQuery.parseJSON(data);
                                if (res.success) {
                                    mw.notification.notify('上传成功');
                                } else {
                                    mw.notification.notify('上传失败');
                                }
                            }
                        );
                    });
                }
            }
            else if (data.upload.result == 'Success') {
                /**
                 * api.php  upload file bigThumbnail as image
                 * named as video_title
                 */
                $.post(
                    mw.util.wikiScript(), {
                        action: 'ajax',
                        rs: 'wfinsertVideoInfo',
                        rsargs: [video_from, title + n + '.video', video_id, title + n, video_player_url, video_tags, video_duration]
                    },
                    function (data) {
                        var res = jQuery.parseJSON(data);
                        if (res.success) {
                            mw.notification.notify('上传成功');
                        } else {
                            mw.notification.notify('上传失败');
                        }
                    }
                );
            }
        }else if(data.error){
            mw.notification.notify(data.error.code);
        }
    });
}
$(function(){
	$('#upload-video-btn').click(function(){
		var url = $('#uploadvideos').val();
		//check url & get video_id
		var regex = /\.(\w+)\.com/;
		var match = url.match(regex);
		switch(match[1]){
			case 'youku':
				var regex2 = /id_([\w]+?)(?:==|\.html)/;
				var id = url.match(regex2);
				if (id != null && id[1] != null){
					video_id = id[1];
					video_from = 'youku';
				}else{
					alert('failed');
				}
			// case 'qq':
			// default: 
		}
		//get video info from youkuapi
		$.get("https://openapi.youku.com/v2/videos/show.json",{ 
		'client_id':'adc1f452c0653f53', 
		'video_id':video_id
		},function(data){
			var title_str = data.title.replace('.video','');
			video_title = title_str.replace(/\s/g, '_');
			// alert(video_title);
			var video_player_url = data.player;
			var video_tags = data.tags;
			var video_thum = data.bigThumbnail;
			var video_duration = data.duration;
			var token = mw.user.tokens.get('editToken');
			
			if ( $('.upload-new-revision').length > 0 ) {
				video_name = $('.video-name').val();
				// alert(111);
				$.post('/api.php',{
            		action:'upload',
            		filename: video_name, 
            		// filename: 'video-test7',
            		url: video_thum, 
            		token: token, 
            		ignorewarnings: true,
            		format: 'json',
            		comment: '添加视频'
            	},function(data){
            		console.log(data);
            		// alert(data.upload.filename);
            		$.post(
			            mw.util.wikiScript(), {
			                action: 'ajax',
			                rs: 'wfinsertVideoInfo',
			                rsargs: [video_from, data.upload.filename, video_id, video_name, video_player_url, video_tags, video_duration]
			            },
			            function( data ) {
			            	console.log(data);
			                var res = jQuery.parseJSON(data);
			                if (res.success){
			                	/**
			                	 * api.php  upload file bigThumbnail as image 
			                	 * named as video_title
			                	 */
			                	
			                   alert('update success');
			                }else{
			                   alert('update filed');
			                }
			            }
			        );
            		
            	});
				
			}else{

				checkName(video_title,video_thum,token,video_from, video_id, video_player_url, video_tags, video_duration,'');
			}
			console.log(data);
			//ajax insert video's info
		});

    });
	
});

// $(document).ready(function(){})