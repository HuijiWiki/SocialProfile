/**
 * Created by huiji-001 on 2016/3/17.
 */
var admin = {
    dragcontent:'',
    pos:'',
    limit: 20,
    cont:1,
    val:'',
    token:'',
    addlimit: 'bot,bureaucrat,sysop,rollback,staff',
    getPos: function(){
        this.pos = this.getSubClient($('.admin-tab-content').get(0)).top-100;
    },
    headerEdit: function(){
        $('.admin-header-editbtn').click(function(){
            $('.admin-header-msg').hide();
            $('.admin-header-form').show();
            $('.admin-header-form textarea').val($('.admin-header-des p').text());
        })
    },
    headerSubmit: function(){
        $('.admin-header-form-submit').click(function(e){
            e.preventDefault();
            var val = $('.admin-header-form textarea').val();
            $.ajax({
                url:mw.util.wikiScript(),
                data:{
                    action:'ajax',
                    rs:'wfUpdaSiteDescription',
                    rsargs:[val]
                },
                success: function(data){
                    var res = JSON.parse(data);
                    if(res.result=='success') {
                        $('.admin-header-des p').text(val);
                        $('.admin-header-msg').show();
                        $('.admin-header-form').hide();
                    }else{
                        mw.notification.notify('您的权限不足')
                    }
                }
            })
        })
        $('.admin-header-form-cancel').click(function(e) {
            e.preventDefault();
            $('.admin-header-msg').show();
            $('.admin-header-form').hide();
        });
    },
    getSubClient: function(p){
        var l = 0, t = 0, w, h;
        w = p.offsetWidth;
        h = p.offsetHeight;
        while(p.offsetParent){
            l += p.offsetLeft;
            t += p.offsetTop;
            p = p.offsetParent;
        }
        return { left: l, top: t, width: w, height: h };
    },
    dragStart:function(){
        var base = this;
        $('.admin-member-header-right .label').each(function () {
            this.ondragstart = function(e){
                e.dataTransfer.setData("Text","灰机wiki为何这么叼");
                base.dragcontent = e.target;
            }
        })
    },
    dragOver:function(){
        $('body').get(0).ondragover = function(e){
            e.preventDefault();
        }
    },
    drop: function(){
        var base = this;
        $('.admin-member-list').get(0).ondrop = function (e) {
            e.stopPropagation();
            e.preventDefault();
            var classname = base.dragcontent.className;
            var text = base.dragcontent.textContent;
            var rights = base.dragcontent.getAttribute('rights');
            var li = $(e.target).attr('class')=='clear'?$(e.target):$(e.target).parents('.admin-member-list>li');
            if (li.find('.label').hasClass(base.dragcontent.className)) return;
            base.changeRights(li,rights,classname,text);

        }
    },
    getTokens: function(li,rights,base,method){
        var base = this;
        base.token = li.attr('token');
        if(!base.token){
            $.ajax({
                url:'/api.php',
                data:{
                    action:'query',
                    list:'users',
                    meta:'tokens',
                    ususers:'Volvo',
                    type:'userrights',
                    format:'json'
                },
                type:'post',
                success: function(data){
                    li.attr('token',data.query.tokens.userrightstoken);
                    base.token = data.query.tokens.userrightstoken;
                    method(li,rights,base,base.token);
                }
            })
        }
        else{
            method(li,rights,base.token);
        }
    },
    changeRights:function(li,rights,classname,text){
        var base = this;
        var input;
        $.ajax({
            url:'/api.php',
            data:{
                action:'userrights',
                user:li.find('.a-user>a').text(),
                add: rights,
                token: base.token,
                format: 'json'
            },
            type:'post',
            success:function(data){
                if(data.userrights.added.length>0) {
                    li.find('.a-tag').append('<span class="' + classname + '" rights="' + rights + '"><i class="icon-close"></i>' + text + '</span>');
                    for(var i=0,j=li.find('input');i< j.length;i++){
                        if(j[i].getAttribute('data-name')==rights){
                            j[i].checked = true;
                            j[i].setAttribute('checked','');
                            $(j[i]).parents('.checkbox').removeClass('disabled');
                        }
                    }
                }else{
                    console.log(data);
//                    mw.notification.notify('权限不足')
                    for(var i=0,j=li.find('input');i< j.length;i++){
                        if(j[i].getAttribute('data-name')==rights){
                            $(j[i]).parents('.checkbox').removeClass('disabled');
                        }
                    }
                }

            }
        })
    },

    closeEvent: function(){
        var base = this;
        $('.admin-member-list').on('click','.icon-close',function(){
            var name = $(this).parents('.a-options').siblings('.a-msg').find('.a-user>a').text();
            var rights = $(this).parents('.label').attr('rights');
            base.removeRights(name,rights,$(this).parents('.label'));
        })
    },

    removeRights:function(name,rights,selector){
        var base = this;
        $.ajax({
            url:'/api.php',
            data:{
                action:'userrights',
                user:name,
                remove: rights,
                token: base.token,
                format: 'json'
            },
            type:'post',
            success:function(data){
                if(data.userrights.removed.length>0) {
                    for(var i=0,j=selector.parents('.admin-member-list>li').find('input');i< j.length;i++){
                        if(j[i].getAttribute('data-name')==rights){
                            j[i].checked = false;
                            j[i].removeAttribute('checked');
                            $(j[i]).parents('.checkbox').removeClass('disabled');
                        }
                    }
                    selector.remove();
                }else{
                    console.log(data);
//                    mw.notification.notify('权限不足');
                }
            }
        })
    },

    changeFixLabel:function(){
        var base = this;
        $(document).scroll(function(){
            var scroll = $(this).scrollTop();
            if(scroll>=base.pos){
                $('.admin-member-header-right').addClass('label-fix');
            }else{
                $('.admin-member-header-right').removeClass('label-fix');
            }
        });
    },
    getList: function(){
        var base = this;
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action:'ajax',
                rs:'wfGetUserStatusInfo',
                rsargs:[base.val,base.limit]
            },
            success: function(data){
                var res = JSON.parse(data)
                base.clearList();
                base.jointList(res);
                base.cont = res.continue;
            }
        })
    },
    jointList: function(data){
        var base = this;
        base.token = data.token;
        if(data.result == 'success'){
            var label = new Object();
            var users = data.users;
            var content = '';
            var lock;
            var add = new Array();
            var checklist;
            base.getMyAdd(add,data.admin.add);

            for(var i=0;i<users.length;i++){
                var tag = '';
                var check = '';
                label = base.getLabelObj(users[i].rights);
                checklist = base.getCheckObj(users[i].rights,add);
                for(var a in label){
                    tag += '<span class="'+label[a][0]+'" rights="'+label[a][1]+'"><i class="icon-close"></i>'+label[a][2]+'</span>';
                }
                for(var b in checklist){
                    check +='<li><a class="checkbox"><label><input type="checkbox"  value="option1" '+checklist[b].check+' data-name="'+b+'" data-color="'+checklist[b].color+'">'+checklist[b].name+'</label></a></li>'
                }
                if(users[i].status){
                    lock = '<button class="a-delete btn btn-sm unlock"><span class="fa fa-unlock"></span><a href="/wiki/special:解除封禁/'+users[i].name+'" target="_blank"> 解封</a></button>'
                }else{
                    lock = '<button class="a-delete btn btn-sm"><span class="fa fa-ban"></span><a href="/wiki/special:封禁/'+users[i].name+'" target="_blank"> 封禁</a></button>'
                }
                content+='<li class="clear">' +
                    '<div class="a-msg"><div class="a-avatar">'+users[i].img+'</div><div class="a-user"><a href="/wiki/%E7%94%A8%E6%88%B7:'+users[i].name+
                    '" title="用户:'+users[i].name+'" class="mw-userlink">'+users[i].name+'</a><span class="a-user-level">'+users[i].level+'</span><span class="a-user-editcount">编辑次数:'+users[i].editcount+'</span></div></div>'+
                    '<div class="a-options"><span class="a-tag">'+tag+'</span><div class="btn-group">' +
                    '<button type="button" class="a-change-role btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">管理 <span class="caret"></span></button>'+
                    '<ul class="dropdown-menu">'+check+'</ul></div>'+lock+'</div></li>'
            }
            $('.admin-member-list').append(content);
        }
    },
    getMyAdd:function(add , alladd){
        var base = this;
        alladd.forEach(function(item){
            if(base.addlimit.indexOf(item)>=0){
                add.push(item);
            }
        })
    },
    getCheckObj:function(rights,add){
        var obj = new Object();
        add.forEach(function(item){
            obj[item] = new Object();
            obj[item].check  = '';
            if(item == 'bot'){
                obj[item].name = '机器人';
                obj[item].color = 'primary';
            }else if(item == 'sysop'){
                obj[item].name = '管理员';
                obj[item].color = 'info';
            }else if(item == 'bureaucrat'){
                obj[item].name = '行政员';
                obj[item].color = 'success';
            }else if(item == 'rollback'){
                obj[item].name = '回退员';
                obj[item].color = 'warning';
            }else if(item == 'staff'){
                obj[item].name = '职员';
                obj[item].color = 'default';
            }
            for(var i=0;i<rights.length;i++){
                if(item==rights[i]){
                    obj[item].check = 'checked';
                }

            }
        })
        return obj;
    },
    getLabelObj:function (rights){
        var obj = new Object();
        rights.forEach(function(item){
            if(item=='bureaucrat'){
                obj.bureaucrat = ['label label-success',item,'行政员']
            }else if(item=='sysop'){
                obj.sysop = ['label label-info',item,'管理员']
            }else if(item=='rollback'){
                obj.rollback = ['label label-warning',item,'回退员']
            }else if(item=='bot'){
                obj.bot = ['label label-primary',item,'机器人']
            }else if(item=='staff'){
                obj.staff = ['label label-default',item,'职员' ]
            }
        });
        return obj;
    },
    clearList: function(){
        $('.admin-member-list').empty();
    },
    loadMore: function(){
        var base = this;
        $('.addmore').click(function(){
            $.ajax({
                url:mw.util.wikiScript(),
                data:{
                    action:'ajax',
                    rs:'wfGetUserStatusInfo',
                    rsargs:[$('.admin-member-search').val(),base.limit,base.cont]
                },
                success: function(data){
                    var res = JSON.parse(data)
                    if(!res.users){
                        mw.notification.notify("没有更多了");
                        return;
                    }
                    base.jointList(res);
                    base.cont = res.continue;
                }
            })
        });
    },
    search: function(){
        var base = this;
        $('.admin-member-search').keyup(function(){
            base.val = $('.admin-member-search').val();
            base.getList();
        });
    },

    checkRights:function(){
        var base = this;
        $('.admin-member-list').on('click','.btn-group .dropdown-menu li label',function(e){
            e.stopPropagation();
            e.preventDefault();
            var username = $(this).parents('.a-options').siblings('.a-msg').find('.a-user>a').text();
            var rights = $(this).find('input').data('name');
            var selector = $(this).parents('.btn-group').siblings('.a-tag').find('span');
            $(this).parents('.checkbox').addClass('disabled');
            for(var i=0;i<selector.length;i++){
                if(selector[i].getAttribute('rights') == rights){
                    selector = $(selector[i]);
                    break;
                }
            }
            if($(this).find('input').attr('checked')=='checked'){
                base.removeRights(username,rights,selector);
                $(this).parents('.checkbox').addClass('disabled');
            }else{
                var input = $(this).find('input');
                var li = $(this).parents('.admin-member-list>li');
                var name = input.attr('data-name');
                var classname = 'label label-'+input.attr('data-color');
                var text = $(this).text();
                base.changeRights(li,name,classname,text);
            }
        });
    },
    addToggleBtn: function(){
        var base = this;
        mw.loader.using( 'oojs-ui' ).done( function () {
            var name,value;
            $('.setting-toggle .toggle').each(function(){
                    var toggle = new  OO.ui.ToggleSwitchWidget( { value: $(this).data('value') , disabled: $(this).data('state')} );

                    toggle.on('change',function(e){
                        e = window.event || e;
                        name = $(e.target).parents('.setting-toggle .toggle').siblings('.setting-options').val();
                        value = $(e.target).attr('aria-checked')==='false'?1:0;    //点击时取的是变前的值，相反
                        base.sendSetting(name,value);
                    })
                $(this).append(toggle.$element)
                });

        });
    },
    sendSetting: function(name,value){
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action:'ajax',
                rs:'wfSetSiteProperty',
                rsargs:[name,value]
            },
            success: function(data){
                mw.notification.notify('设置成功',{tag:'toggle'});
            }
        })
    },
    domReady:function(){
        this.getPos();
        this.getList();
        this.addToggleBtn();
    },
    addEvent: function(){
        this.headerEdit();
        this.headerSubmit();
        this.changeFixLabel();
        this.dragStart();
        this.dragOver();
        this.drop();
        this.search();
        this.loadMore();
        this.checkRights();
        this.closeEvent();
        //this.sendSetting();
    },
    init: function(){
        this.domReady();
        this.addEvent();
    }
};

$(function(){

    admin.init();

});