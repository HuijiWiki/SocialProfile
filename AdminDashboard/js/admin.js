/**
 * Created by huiji-001 on 2016/3/15.
 */
$(function(){
    var dragcontent;
    var pos;
    var cont=1;
    pos = getSubClient($('.admin-tab-content').get(0)).top-100;
    console.log(pos);
    for(var i=0;i<4;i++) {
        $('.admin-member-header-right .label').get(i).addEventListener('dragstart', function (e) {
            dragcontent = e.target;
            console.log(e.target.className, e.target.class)
//        e.dataTransfer.setData('content', e.target.textContent);
        })
    }
    for(var j=0;j<$('.admin-member-list li').length;j++) {
        $('.admin-member-list li').get(j).addEventListener('drop', function (e) {
            e.stopPropagation();
            e.preventDefault();
//        var content = e.dataTransfer.getData('content');
            if ($(this).find('.label').hasClass(dragcontent.className)) return;
            $(this).find('.a-tag').append('<span class="' + dragcontent.className + '">' + dragcontent.textContent + '</span>');
        })
    }

    $(document).scroll(function(){
        var scroll = $(this).scrollTop();
        if(scroll>=pos){
            $('.admin-member-header-right').addClass('label-fix');
        }else{
            $('.admin-member-header-right').removeClass('label-fix');
        }
    });
    $('.admin-member-search').keyup(function(){
        var name = '';
        var val = $(this).val();
//        $.ajax({
//            url:'/api.php',
//            data:{
//                action:'query',
//                list: 'allusers',
//                auprefix: $(this).val(),
//                aulimit: 20,
//                auprop:'editcount',
//                format:'json'
//            },
//            type: 'post',
//            format: 'json',
//            success:function(data){
////                console.log(data.query.allusers);
//                for(var i in data.query.allusers){
//                    name += data.query.allusers[i].name+'|'
//                }
//                name = name.substring(0,name.length-1);
//                getInfo(name);
//            }
//        })
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action:'ajax',
                rs:'wfGetUserStatusInfo',
                rsargs:[val,10]
            },
            success: function(data){
                var res = JSON.parse(data)
                console.log(res);
                cont = res.continue;
            }
        })
    });
    $('.addmore').click(function(){
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action:'ajax',
                rs:'wfGetUserStatusInfo',
                rsargs:[$('.admin-member-search').val(),10,cont]
            },
            success: function(data){
                var res = JSON.parse(data)
                console.log(res);
            }
        })
    });
    $('.btn-group .dropdown-menu li').click(function(e){
        e.stopPropagation();
        console.log('aaa');
    });
    function getInfo(user){
        $.ajax({
            url:'/api.php',
            data:{
                action:'query',
                list:'users',
                ususers: user,
                usprop:'blockinfo|groups|editcount|registration|emailable|gender',
                format: 'json'
            },
            type:'post',
            success: function(data){
                console.log(data);
            }
        })
    }
    function getSubClient(p){
        var l = 0, t = 0, w, h;
        w = p.offsetWidth;
        h = p.offsetHeight;
        while(p.offsetParent){
            l += p.offsetLeft;
            t += p.offsetTop;
            p = p.offsetParent;
        }
        return { left: l, top: t, width: w, height: h };
    };
    $('.admin-header-editbtn').click(function(){
        $('.admin-header-msg').hide();
        $('.admin-header-form').show();
        $('.admin-header-form textarea').val($('.admin-header-des p').text());
    })
    $('.admin-header-form-submit').click(function(){
        var val = $('.admin-header-form textarea').val();
        $('.admin-header-des p').text(val);
        $('.admin-header-msg').show();
        $('.admin-header-form').hide();
    })
})
