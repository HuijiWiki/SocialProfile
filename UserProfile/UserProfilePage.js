/**
 * JavaScript functions used by UserProfile
 */
var UserProfilePage = {
	posted: 0,
	numReplaces: 0,
	replaceID: 0,
	replaceSrc: '',
	oldHtml: '',

	sendMessage: function() {
		var userTo = decodeURIComponent( mw.config.get( 'wgTitle' ) ), //document.getElementById( 'user_name_to' ).value;
			encMsg = encodeURIComponent( document.getElementById( 'message' ).value ),
			msgType = document.getElementById( 'message_type' ).value;
		if ( document.getElementById( 'message' ).value && !UserProfilePage.posted ) {
			UserProfilePage.posted = 1;
			jQuery.post(
				mw.util.wikiScript(), {
					action: 'ajax',
					rs: 'wfSendBoardMessage',
					rsargs: [userTo, encMsg, msgType, 10]
				},
				function( data ) {
					jQuery( '#user-page-board' ).html( data );
					UserProfilePage.posted = 0;
					jQuery( '#message' ).val( '' );
				}
			);
		}
	},

	deleteMessage: function( id ) {
		if ( confirm( '你确认删除此条留言？' ) ) {
			jQuery.post(
				mw.util.wikiScript(), {
					action: 'ajax',
					rs: 'wfDeleteBoardMessage',
					rsargs: [id]
				},
				function( data ) {
					//window.location.reload();
					// 1st parent = span.user-board-red
					// 2nd parent = div.user-board-message-links
					// 3rd parent = div.user-board-message = the container of a msg
					jQuery( '[data-message-id="' + id + '"]' ).parent().parent().parent().hide( 100 );
				}
			);
		}
	},

	showUploadFrame: function() {
		document.getElementById( 'upload-container' ).style.display = 'block';
		document.getElementById( 'upload-container' ).style.visibility = 'visible';
	},

	uploadError: function( message ) {
		document.getElementById( 'mini-gallery-' + replaceID ).innerHTML = UserProfilePage.oldHtml;
		document.getElementById( 'upload-frame-errors' ).innerHTML = message;
		document.getElementById( 'imageUpload-frame' ).src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75';

		document.getElementById( 'upload-container' ).style.display = 'block';
		document.getElementById( 'upload-container' ).style.visibility = 'visible';
	},

	textError: function( message ) {
		document.getElementById( 'upload-frame-errors' ).innerHTML = message;
		document.getElementById( 'upload-frame-errors' ).style.display = 'block';
		document.getElementById( 'upload-frame-errors' ).style.visibility = 'visible';
	},

	completeImageUpload: function() {
		document.getElementById( 'upload-frame-errors' ).style.display = 'none';
		document.getElementById( 'upload-frame-errors' ).style.visibility = 'hidden';
		document.getElementById( 'upload-frame-errors' ).innerHTML = '';
		UserProfilePage.oldHtml = document.getElementById( 'mini-gallery-' + UserProfilePage.replaceID ).innerHTML;

		for ( var x = 7; x > 0; x-- ) {
			document.getElementById( 'mini-gallery-' + ( x ) ).innerHTML =
				document.getElementById( 'mini-gallery-' + ( x - 1 ) ).innerHTML.replace( 'slideShowLink(' + ( x - 1 ) + ')', 'slideShowLink(' + ( x ) + ')' );
		}
		document.getElementById( 'mini-gallery-0' ).innerHTML =
			'<a><img height="75" width="75" src="' +
			mw.config.get( 'wgExtensionAssetsPath' ) +
			'/SocialProfile/images/ajax-loader-white.gif" alt="" /></a>';

		if ( document.getElementById( 'no-pictures-containers' ) ) {
			document.getElementById( 'no-pictures-containers' ).style.display = 'none';
			document.getElementById( 'no-pictures-containers' ).style.visibility = 'hidden';
		}
		document.getElementById( 'pictures-containers' ).style.display = 'block';
		document.getElementById( 'pictures-containers' ).style.visibility = 'visible';
	},

	uploadComplete: function( imgSrc, imgName, imgDesc ) {
		UserProfilePage.replaceSrc = imgSrc;

		document.getElementById( 'upload-frame-errors' ).innerHTML = '';

		//document.getElementById( 'imageUpload-frame' ).onload = function() {
			var idOffset = -1 - UserProfilePage.numReplaces;
			//$D.addClass( 'mini-gallery-0', 'mini-gallery' );
			//document.getElementById('mini-gallery-0').innerHTML = '<a href=\"javascript:slideShowLink(' + idOffset + ')\">' + UserProfilePage.replaceSrc + '</a>';
			document.getElementById( 'mini-gallery-0' ).innerHTML = '<a href=\"' + __image_prefix + imgName + '\">' + UserProfilePage.replaceSrc + '</a>';

			//UserProfilePage.replaceID = ( UserProfilePage.replaceID == 7 ) ? 0 : ( UserProfilePage.replaceID + 1 );
			UserProfilePage.numReplaces += 1;
		//}
		//if ( document.getElementById( 'imageUpload-frame' ).captureEvents ) document.getElementById( 'imageUpload-frame' ).captureEvents( Event.LOAD );

		document.getElementById( 'imageUpload-frame' ).src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75&extra=' + UserProfilePage.numReplaces;
	},

	slideShowLink: function( id ) {
		//window.location = 'index.php?title=Special:UserSlideShow&user=' + __slideshow_user + '&picture=' + ( numReplaces + id );
		window.location = 'Image:' + id;
	},

	doHover: function( divID ) {
		document.getElementById( divID ).style.backgroundColor = '#4B9AF6';
	},

	endHover: function( divID ) {
		document.getElementById( divID ).style.backgroundColor = '';
	}
};

jQuery( document ).ready( function() {

	// "Send message" button on (other users') profile pages
	jQuery( 'div.user-page-message-box-button input[type="button"]' ).on( 'click', function() {
		UserProfilePage.sendMessage();
	} );

	// Board messages' "Delete" link
	jQuery( 'span.user-board-red a' ).on( 'click', function() {
		UserProfilePage.deleteMessage( jQuery( this ).data( 'message-id' ) );
	} );


    //修改个人资料
    $('.form-location').editable({
        type: 'text',
        url: '/post',
        title: '居住城市'
    });
    $('.form-date').editable({
        type: 'date',
        format: 'yyyy-mm-dd',
        viewformat: 'dd/mm/yyyy',
        datepicker: {
            weekStart: 1
        }
    });
    $('.form-sex').editable({
        type: 'select',
        value: 2,
        source: [
            {value: 1, text: '无'},
            {value: 2, text: '男'},
            {value: 3, text: '女'}
        ]
    });
    $('.form-autograph').editable({
        type:'textarea',
        title:'个性签名'
    });

    $(".form-change").click(function(){
        var location = $(".form-location").text();
        var autograph = $(".form-autograph").text();
        var birthday =$(".form-date").text();
        var birthdaydata =$(".form-date").attr('data-birthday');
        var sex = $(".form-sex").text();
        var msg = '<form class="form-edit"><input type="text" class="input-location form-control">' +
            '<span>|</span><input type="date" name="user_date" class="input-date form-control"><span>|</span>' +
            '<input type="radio" name="sex" class="sex-man" value="♂" data-male="male">男<input type="radio" name="sex" ' +
            ' class="sex-woman" value="♀" data-male="female">女' +
            '<textarea class="form-textarea form-control"></textarea>' +
            '<botton type="submit" class="btn btn-info form-submit">确定</botton></form>'
        $(".profile-actions").append(msg);
        if(autograph=="填写个人状态"){
            autograph = '';
            $(".form-textarea").attr("placeholder","个人状态");
        }
        if(location=="填写居住地") {
            location = '';
            $(".input-location").attr("placeholder", "居住地");
        }
        if(birthday=="填写生日"){
            birthday = '';
        }else{
            $(".input-date").val(birthdaydata);
        }
        if(sex=="♂"){
            $(".sex-man").attr("checked","checked")
        }else if(sex=="♀"){
            $(".sex-woman").attr("checked","checked")
        }
        $(".form-textarea").val(autograph);
        $(".input-location").val(location);
        $(".form-container").hide();
    });
    $(".profile-actions").on("click",".form-submit",function(){
        var location = $(".input-location").val();
        location = location.replace(/(^\s*)|(\s*$)/g, "");
        var autograph = $(".form-textarea").val();
        autograph = autograph.replace(/(^\s*)|(\s*$)/g, "");
        var birthday = $(".input-date").val();
        var sex = $('.form-edit input:radio:checked').val();
        var gender = '';
        var username = mw.config.get('wgTitle');
        if (sex == '♂'){
            gender = 'male';
        }else if(sex == '♀'){
            gender = 'female';
        }else{
            gender = 'unknown';
        }
        if(location!=$(".form-location").text()||sex!=$(".form-sex").text()||autograph!=$(".form-autograph").text()||birthday!=$(".form-date").attr('data-birthday')) {//如果没变 不发出数据 减少负担
            $.post(
                mw.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfUpdateUserStatus',
                    rsargs: [username, gender, '', location, birthday, autograph]
                },
                function (data) {
                    console.log(gender);
                    var res = $.parseJSON(data);
                    if (res.success) {
                        $(".form-container").show();
                        if (location == '') {
                            $(".form-location").text("填写居住地").addClass("edit-on");
                            //$(".edit-on").addEventListener('click',editer);
                        } else {
                            $(".form-location").text(location).removeClass("edit-on");
                            //$(".edit-on").removeEventListener('click',editer);
                        }
                        if (autograph == '') {
                            $(".form-autograph").text("填写个人状态").addClass("edit-on");
                        } else {
                            $(".form-autograph").text(autograph).removeClass("edit-on");
                        }
                        if (birthday == '') {
                            $(".form-date").text("填写生日").addClass("edit-on");
                        } else {
                            var age = ages(birthday);
                            $(".form-date").attr('data-birthday', birthday);
                            console.log($(".form-date").data('birthday'));
                            $(".form-date").text(age).removeClass("edit-on");
                        }
                        $(".form-sex").text(sex);
                        $(".form-edit").remove();
                    } else {
                        alertime();
                        alertp.text(res.message);
                    }
                }
            )
        }else{
            $(".form-container").show();
            $(".form-edit").remove();
        }
    });
    var alreturn = $('.alert-return');
    var alertp = $('.alert-return p');
    function alertime(){
        alreturn.show();
        setTimeout(function(){
            alreturn.hide()
        },1000);
    }
    var reload = ages($(".form-date").attr('data-birthday'));
    if(reload!="0000-00-00"&&reload!='') {
        $(".form-date").text(reload);
    }
    function ages(str)
    {
        if(!str)
            return;
        var   r   =   str.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
        if(r==null)return   false;
        var   d=   new   Date(r[1],   r[3]-1,   r[4]);
        if   (d.getFullYear()==r[1]&&(d.getMonth()+1)==r[3]&&d.getDate()==r[4])
        {
            var   Y   =   new   Date().getFullYear();
            return((Y-r[1])+"岁");
        }
        return("输入的日期格式错误！");
    }
    $('.profile-top-right-bottom>a').click(function(){
        $('.watch-url').modal();
        $('.modal-body .list-group').empty();
        var t_name = mw.config.get('wgTitle');
        var user_name = mw.config.get('wgUserName');
        $.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserSiteFollowsDetailsResponse',
                rsargs: [user_name,t_name]
            },
            function(data){
                var res = $.parseJSON(data);//console.log(res);
                if (res.success==true){
                    if (res.result.length == 0) {
                        $('.modal-body .btn-default').hide();
                        $('.modal-body .list-group').append('您暂时还没有关注的wiki哦');
                    };
                    if(user_name != null){
                        $.each(res.result,
                            function(i, item){
                                if( i<10 && i>=0){
                                    $('.modal-body .btn-default').hide();
                                    if (item.is == 'Y') {
                                        var msg='<a href="'+"http://"+item.key+'.huiji.wiki" class="list-group-item">'+item.val+'<span class="badge user-site-follow-from-modal unfollow">取关</span></a>';
                                    }else{
                                        var msg='<a href="'+"http://"+item.key+'.huiji.wiki" class="list-group-item">'+item.val+'<span class="badge user-site-follow-from-modal">关注</span></a>';
                                    }
                                    $('.modal-body .list-group').append(msg);
                                }else if (i>=10) {
                                    $('.modal-body .btn-default').show();
                                };                                
                            }
                        );
                    }else{
                        $.each(res.result,
                            function(i, item){
                                if( i<10 && i>=0){
                                    $('.modal-body .btn-default').hide();
                                    if (item.is == 'Y') {
                                        var msg='<a href="'+"http://"+item.key+'.huiji.wiki" class="list-group-item">'+item.val+'</a>';
                                    }else{
                                        var msg='<a href="'+"http://"+item.key+'.huiji.wiki" class="list-group-item">'+item.val+'</a>';
                                    }
                                    $('.modal-body .list-group').append(msg);
                                }else if (i>=10) {
                                    $('.modal-body .btn-default').show();
                                };                                
                            }
                        );
                    }
                }
            }
        );
    });
    $('svg .day').tooltip({title:"tooltip - title", container:"body"});
});

