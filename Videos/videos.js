var setVideoStatus =  (function(){
    var videoSubmitted = false;
    return function (isSubmitted){
        if (isSubmitted == ''){
            return videoSubmitted;
        }
        videoSubmitted = isSubmitted;
        $('#upload-video-btn').prop('disabled', videoSubmitted);
        if (isSubmitted){
            $('#upload-video-btn').html('<i class="fa fa-spinner fa-spin"></i>添加');
        } else {
            $('#upload-video-btn').html('添加');
        }
        return videoSubmitted;
    }
})();
var onUploadSuccess = function(filename){
    var redirectTarget = '/wiki/File:'+filename;
    setVideoStatus(false);
    window.location = redirectTarget;
}
var onUploadError = function(){
    setVideoStatus(false);
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
        var video_from, video_id;
        var video_orig_title;
        var video_full_name;
        var video_name;
        var video_player_url;
        var video_tags;
        var video_thum;
        var video_duration;
        var token;
        var is_new_revision = $('.upload-new-revision').length;
        video_name = $('.video-name').val();
		switch(match[1]){
			case 'youku':
                mw.VideoHandler.queryYouku(url, video_name, onUploadSuccess, onUploadError, is_new_revision);
                break;
			case 'bilibili':
                mw.VideoHandler.queryBilibili(url, video_name, onUploadSuccess, onUploadError, is_new_revision);
                break;
			default:
                mw.notification.notify('上传失败（URL不支持）'); 
                setVideoStatus(false);
                return;
		}
    });
});