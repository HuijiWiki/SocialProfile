$(document).ready(function(){
    var u_gender;
    var u_avatar;
    if (mw.cookie){
        u_gender = mw.cookie.get( 'user_gender' );
        u_avatar = mw.cookie.get( 'user_avatar' );       
    }

    //gender & del gender cookie
    if( u_gender !=  null && mw.config.get( 'wgUserId' ) != null ){
        // var api = new mw.Api();
        new mw.Api().saveOption( 'gender',u_gender );
        mw.cookie.set( 'user_gender' , null);
    }
    //user avatar & del avatar cookie
    if( u_avatar != null && mw.config.get( 'wgUserId' ) != null ){
    	var api = new mw.Api();
    	api.postWithToken('edit', { 'action': 'avatarsubmit', 'format': 'json', 'avatar_src': u_avatar })
			.done(function( response ) {
				mw.cookie.set( 'user_avatar' , null);
			} );
    }
	$('.set-menu>li:last-child').on('click','a',function(e){
		WB2.logout();
   });

	if(mw.config.get('wgUserId') != null){
		return '';
	}

	var paras = {};
	// var qqOpenid;
	var wbOpenId, inviteuser, redirect_url;

	$('#qqConfirm').click(function(){
		var username = $("#qqloginusername").val();
		var email = $("#qqloginemail").val();
		var pass = $("#qqloginpassword").val();
		var qqOpenId = $('#qqOpenId').val();
		var userGender = $('#userGender').val();
		var userAvatar = $('#userAvatar').val();
        var userType = $('#userType').val();
        var inviter = $('#inviter').val();
        var token = $('#wpCreateaccountToken').val();
		inviteuser = $('#inviteuser').val();
        redirect_url = $('#redirect_url').val();
		mw.cookie.set( 'user_gender', userGender );
		mw.cookie.set( 'user_avatar', userAvatar );
        $('#qqConfirm').button('loading');
		wiki_signup(userType,username,email,pass,qqOpenId,inviteuser,redirect_url,inviter, token);
	})

	var loginerror = $('.login-error');
    function wiki_signup(type,login,email,pass,outhId,inviteuser,redirect_url,inviter,token){

        if ( inviteuser == 1 ) {
            var red = 'http://www'+mw.config.get('wgHuijiSuffix');
        }else{
            addOauth(type,outhId,data.createaccount.userid,inviteuser,redirect_url,inviter);
            var red = "http://"+redirect_url+".huiji.wiki/";
        }
        // $.post('/api.php?action=createaccount&name='+login+'&email='+email+'&password='+pass+ '&format=json',function(data){
        //     if(login==''){
        //         $('#qqConfirm').button('reset');
        //         mw.notification.notify('您的用户名不能为空');
        //     }
        //     else if(email==''){
        //         $('#qqConfirm').button('reset');
        //         mw.notification.notify('您的邮箱必须填写');
        //     }
        //     else if(data.createaccount.result=='NeedToken'){
                $.post('/api.php?action=createaccount&format=json',
                {   
                    createtoken: token,
                    username: login,
                    email: email,
                    password: pass,
                    retype: pass,
                    createreturnurl:red
                    
                },
                function(data){
                    // console.log(data);
                    if(!data.error){
                        if(data.createaccount.status=="PASS" ){
                            mw.notification.notify('注册成功，请稍候');
                            if ( inviteuser == 1 ) {
                                location.href = 'http://www'+mw.config.get('wgHuijiSuffix');
                            }else{
                                addOauth(type,outhId,data.createaccount.userid,inviteuser,redirect_url,inviter);
                                location = "http://"+redirect_url+".huiji.wiki/";
                            }
                        }
                        else{
                            $('#qqConfirm').button('reset');
                            mw.notification.notify(data.createaccount.result);
                        }
                    }
                    else{
                        $('#qqConfirm').button('reset');
                        mw.notification.notify('error' + data.error.info);
                        console.log(data.error.code);
                      
                    }
                });
            // }
            // else{
            //     $('#qqConfirm').button('reset');
            //     mw.notification.notify('error' + data.error.code);
            // }
        // });
    }

    function addOauth(type,openid,userid,inviteuser,redirect_url,inviter){

        $.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfAddInfoToOauth',
                rsargs: [type, openid, userid, inviteuser, inviter]
            },
            function (data) {
                var res = $.parseJSON(data);
                // var urlTo;
                if (type == 'weibo') {
                    redirect_url = mw.cookie.get( 'redirect_url' );
                }
                // console.log(redirect_url);return '';
                if (res.success == true) {
                    if ( redirect_url != null ) {
                        mw.cookie.set( 'redirect_url', null );
                        location.href = 'http://'+redirect_url+mw.config.get('wgHuijiSuffix');
                    }else{
                        // location.href = 
                        location.href = 'http://www'+mw.config.get('wgHuijiSuffix');
                    }
                } 
            }
        );
    }

});
