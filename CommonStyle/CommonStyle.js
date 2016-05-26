/**
 * Created by huiji-001 on 2016/4/25.
 */
$(function(){
    var file;
    var obj = new Object();
    $('.picker-color').click(function(e){
//        e.preventDefault();
        e.stopPropagation();
    })
    $('.picker-color .color-box').each(function(){
        var variable = $(this).attr('data-variable');
        var color =  $(this).attr('value');
        obj[variable] = color;
    });
    $('.jcolor').each(function(){
        $(this).colorpicker({
            colorSpace: 'hsla',
            displayColor: 'hex'
        }).on('newcolor',function(e,colorpicker){
            var variable = $(this).attr('data-variable');
            var type = $(this).parents('ul').attr('class');
            var color = colorpicker.toCssString();
            obj[variable] = color;
            console.log(obj);
//            if(type == 'picker-color'){
                less.modifyVars(obj);
//            }
        });
    });
//    $('.jcolor').colorpicker({
//        colorSpace: 'hsla',
//        displayColor: 'css'
//    });
//    $('.jcolor')

    $('.picker-img .color-box').click(function(){
        $(this).siblings('.file-btn').trigger('click');
    });
    $('.picker-img .file-btn').change(function(e){
        var reader = new FileReader();
        var self = $(this);
        var file;
        var selector = $(this).attr('data-selector');
        file = e.target.files[0];
        reader.readAsDataURL(file);
        reader.onload=function(e) {
            var src = this.result;
            self.siblings('.color-box').css('background-image','url("'+src+'")');
            $(selector).css({'background-image':'url("'+src+'")','background-size':'100%'});
        }
    });
    $('.commonstyle-reset').click(function(){
        var that = this;
        $(this).attr('disabled','');
        var reset = [];
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action: 'ajax',
                rs: 'wfUpdateCssStyle',
                rsargs: ['','HuijiColor1',1]
            },
            type: 'post',
            format: 'json',
            success: function(data){
                console.log(data);
                $(that).removeAttr('disabled');

                var res = JSON.parse(data)
                console.log(res.result);
                if(res.result == 'true'){
                    mw.notification.notify('设置成功');
                    location.reload();
                }
            }
        });
    });
    $('.commonstyle-submit').click(function(){
        var that = this;
        $(this).attr('disabled','');
//        var css = '';
//        $('.color-picker li').each(function(){
//            var selector = $(this).find('input').attr('data-selector');
//            var style = $(this).find('.color-box').attr('style');
//            console.log(style);
//            if(style){
//                css+=selector+'{'+style+'}';
//            }
//        })
        var state = $('.is-new').val();
        console.log(obj);
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action: 'ajax',
                rs: 'wfUpdateCssStyle',
                rsargs: [obj,'HuijiColor1',state]
            },
            type: 'post',
            format: 'json',
            success: function(data){
                console.log(data);
                $(that).removeAttr('disabled');

                var res = JSON.parse(data)
                console.log(res.result);
                if(res.result == 'true'){
                    mw.notification.notify('设置成功');
                    location.reload();
                }
            }
        });
    });
    var content = '<link rel="stylesheet/less" href="/skins/bootstrap-mediawiki/less/custom.less"><script type="text/javascript">' +
        'less = {env: "development",async: false,fileAsync: false,poll: 1000,functions: {},dumpLineNumbers: "comments", relativeUrls: false, rootpath: ":/a.com/" };</script>' +
        '<script src="http://www.leemagnum.com/js/less-1.4.2.min.js" type="text/javascript"></script>';
    $('head').append(content);
//$('body').click(function(){
//    var num = parseInt(Math.random()*3);
//    var arr = ['red','blue','green'];
//    less.modifyVars({
//        "@bg": arr[num]
//    });
//    console.log(arr[num]);
//})

});