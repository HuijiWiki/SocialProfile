var setVideoStatus =  (function(){
    var videoSubmitted = false;
    return function (isSubmitted){
        if (isSubmitted == ''){
            return videoSubmitted;
        }
        videoSubmitted = isSubmitted;
        $('#upload-video-btn').prop('disabled', videoSubmitted);
        if (videoSubmitted){
            $('#upload-video-btn').html('<i class="fa fa-spinner fa-spin"></i>添加');
        } else {
            $('#upload-video-btn').html('添加');
        }
        console.log(videoSubmitted);
        return videoSubmitted;
    }
})();
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
                        var redirectTarget = '/wiki/File:'+data.upload.filename;
                        $.post(
                            mw.util.wikiScript(), {
                                action: 'ajax',
                                rs: 'wfinsertVideoInfo',
                                rsargs: [video_from, data.upload.filename, video_id, title, video_player_url, video_tags, video_duration]
                            },
                            function (data) {
                                var res = jQuery.parseJSON(data);
                                if (res.success) {
                                    mw.notification.notify('上传成功');
                                    setVideoStatus(false);
                                    window.location = redirectTarget;
                                } else {
                                    mw.notification.notify('上传失败');
                                    setVideoStatus(false);
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

                var redirectTarget = '/wiki/File:'+data.upload.filename;
                $.post(
                    mw.util.wikiScript(), {
                        action: 'ajax',
                        rs: 'wfinsertVideoInfo',
                        rsargs: [video_from, data.upload.filename, video_id, title, video_player_url, video_tags, video_duration]
                    },
                    function (data) {
                        var res = jQuery.parseJSON(data);
                        if (res.success) {
                            mw.notification.notify('上传成功');
                            setVideoStatus(false);
                            window.location = redirectTarget;
                        } else {
                            mw.notification.notify('上传失败');
                            setVideoStatus(false);
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
    $('#uploadvideos').keyup(function() {
        if($(this).val() != '') {
           $('#upload-video-btn').prop('disabled', false);
        }
     });
	$('#upload-video-btn').click(function(){
        if ($('#uploadvideos').val() == ''){
            mw.notification.notify('请输入视频URL');
            return;
        }
        setVideoStatus(true);

		var url = $('#uploadvideos').val();
		//check url & get video_id
		var regex = /\.(\w+)\.com/;
		var match = url.match(regex);
        if (!match){
            mw.notification.notify('上传失败（URL不支持）');
            return;
        }
		switch(match[1]){
			case 'youku':
				var regex2 = /id_([\w]+?)(?:==|\.html)/;
				var id = url.match(regex2);
				if (id != null && id[1] != null){
					video_id = id[1];
					video_from = 'youku';
				}else{
					mw.notification.notify('上传失败（URL不支持）');
				}
                break;
			// case 'qq':
			default:
                mw.notification.notify('上传失败（URL不支持）'); 
		}
		//get video info from youkuapi
		$.get("https://openapi.youku.com/v2/videos/show.json",{ 
		'client_id':'adc1f452c0653f53', 
		'video_id':video_id
		},function(data){
			// var title_str = data.title.replace('.video','');
			var video_orig_title = data.title;
            var video_full_name;
            var video_name = $('.video-name').val();
            if (video_name == ''){
                video_name = video_orig_title;
            }
            if (video_name.indexOf('.video') < 0){
                video_full_name = video_name + '.video';
            } else {
                video_full_name = video_name;
                video_name = video_full_name.substr(0, video_full_name.lastIndexOf('.'));
            }

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
            		if (! data.upload){
                        mw.notification.notify('上传失败（无法上传新版本）');
                        setVideoStatus(false);
                        return;
                    }
            		// alert(data.upload.filename);

                    var redirectTarget = '/wiki/File:'+data.upload.filename;
            		$.post(
			            mw.util.wikiScript(), {
			                action: 'ajax',
			                rs: 'wfinsertVideoInfo',
			                rsargs: [video_from, data.upload.filename, video_id, video_name, video_player_url, video_tags, video_duration]
			            },
			            function( data ) {
			                var res = jQuery.parseJSON(data);
			                if (res.success){
			                	/**
			                	 * api.php  upload file bigThumbnail as image 
			                	 * named as video_title
			                	 */
                                mw.notification.notify('上传成功');
			                	setVideoStatus(false);
                                window.location = redirectTarget;

			                }else{
                                mw.notification.notify('上传失败（无法获取视频信息）');
                                setVideoStatus(false);

			                }
			            }
			        );
            		
            	});
				
			}else{
				checkName(video_name,video_thum,token,video_from, video_id, video_player_url, video_tags, video_duration,'');
			}
			//ajax insert video's info
		});

    });
	
});

// $(document).ready(function(){})