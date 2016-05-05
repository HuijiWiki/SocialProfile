/**
 * Created by huiji-001 on 2016/4/25.
 */
$(function(){
    var file;
    $('.color-box').change(function(){
        var selector = $(this).attr('data-selector');
        var type = $(this).parents('ul').attr('class');
        console.log(type);
        if(type == 'picker-bg'){
            $(selector).css('background-color','#'+this.jscolor);
        }else if(type == 'picker-font'){
            $(selector).css('color','#'+this.jscolor);
        }else if(type == 'picker-border'){
            $(selector).css('border-color','#'+this.jscolor);
        }
    });
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
    $('.commenstyle-submit').click(function(){
        var css = '';
        $('.color-picker li').each(function(){
            var selector = $(this).find('input').attr('data-selector');
            var style = $(this).find('.color-box').attr('style');
            console.log(style);
            if(style){
                css+=selector+'{'+style+'}';
            }
        })
        console.log(css);
        $.ajax({
            url:mw.util.wikiScript(),
            data:{
                action: 'ajax',
                rs: 'wfUpdateCssStyle',
                rsargs: [css,'Custom.css']
            },
            type: 'post',
            format: 'json',
            success: function(data){
                console.log(data);
            }
        });
    });
});