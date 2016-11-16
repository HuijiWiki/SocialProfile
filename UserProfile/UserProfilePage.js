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
			encMsg = $('.emoji-wysiwyg-editor').html(),
			msgType = document.getElementById( 'message_type' ).value;
		if ( document.getElementById( 'message' ).value|| document.getElementById( 'message' ).textContent  && !UserProfilePage.posted ) {
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
					jQuery( '.mention-area' ).text( '' );
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
    
    if ( jQuery( '.avatar-view.upload-tool' ).length ){

        mw.loader.using('skins.editable', function(){
            $.fn.editable.defaults.url = '/index.php';
            $('.form-location.edit').editable({
                type: 'text',
                send: 'always',
                params: function(params){
                    var data = {};
                    data['action']='ajax';
                    data['rs'] = 'wfUpdateUserStatus';
                    data['rsargs'] = [mw.config.get('wgTitle'),'up_location_city',params.value];
                    return data;
                },
                title: '居住城市'
            });
            var birthdate = $('.form-date').data('birthday');
            $('.form-date.edit').editable({
                type: 'date',
                send: 'always',
                format: 'yyyy-mm-dd',
                value: birthdate,
                viewformat: 'dd/mm/yyyy',
                datepicker: {
                    weekStart: 1
                },
                params: function(params){
                    console.log(params);
                    var data = {};
                    data['action']='ajax';
                    data['rs'] = 'wfUpdateUserStatus';
                    data['rsargs'] = [mw.config.get('wgTitle'),'up_birthday',params.value];
                    return data;
                },
                display: function(value){
                    if(!value){
                        return;
                    }
                    var year = new Date().getFullYear() - value.getFullYear();
                    var month = new Date().getMonth() - value.getMonth();
                    var day = new Date().getDate() - value.getDate();
                    var age;
                    if(day<0) month--;
                    if(month<0) year--;
                    if(day == 0&&month == 0) alert('今天是您的生日，生日快乐');
                    age = year + '岁';
                    $(this).text(age);
                    if(value == null){
                        $(this).text('设置生日');
                    }
                }
            });
            var sex = $('.form-sex').data('sex');
            $('.form-sex.edit').editable({
                type: 'select',
                send: 'always',
                value: sex,
                source: [
                    {value: 'unkown', text: '无', val:'♂/♀'},
                    {value: 'male', text: '男', val:'♂'},
                    {value: 'female', text: '女', val:'♀'}
                ],
                params: function(params){
                    console.log(params);
                    var data = {};
                    data['action']='ajax';
                    data['rs'] = 'wfUpdateUserStatus';
                    data['rsargs'] = [mw.config.get('wgTitle'),'gender',params.value];
                    return data;
                },
                display: function(value, sourceData) {
                    var colors = {"unkown": "", "male": "", "female": ""},
                        elem = $.grep(sourceData, function(o){return o.value == value;});
                    if(elem.length) {
                        $(this).text(elem[0].val).css("color", colors[value]);
                    } else {
                        $(this).text('♂/♀');
                    }
                }
            });
            $('.form-autograph.edit').editable({
                type:'textarea',
                send: 'always',
                params: function(params){
                    var data = {};
                    data['action']='ajax';
                    data['rs'] = 'wfUpdateUserStatus';
                    data['rsargs'] = [mw.config.get('wgTitle'),'up_about',params.value];
                    return data;
                },
                title:'个性签名'
            });
        });
    }

    if(!$('.form-date').hasClass('edit')&&$('.form-date').data('birthday')!=''){
       var age = new Date().getFullYear()-$('.form-date').data('birthday').split('-')[0]+'岁';
        $('.form-date').text(age);
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
                                        var msg='<a href="'+"http://"+item.key+mw.config.get('wgHuijiSuffix')+'" class="list-group-item">'+item.val+'<span class="badge user-site-follow-from-modal unfollow">取关</span></a>';
                                    }else{
                                        var msg='<a href="'+"http://"+item.key+mw.config.get('wgHuijiSuffix')+'" class="list-group-item">'+item.val+'<span class="badge user-site-follow-from-modal">关注</span></a>';
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
                                        var msg='<a href="'+"http://"+item.key+mw.config.get('wgHuijiSuffix')+'" class="list-group-item">'+item.val+'</a>';
                                    }else{
                                        var msg='<a href="'+"http://"+item.key+mw.config.get('wgHuijiSuffix')+'" class="list-group-item">'+item.val+'</a>';
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

