$(function(){
    //user-home-item img show
    $('.user-home-item-img-wrap').each(function(){
        if($(this).find('a').length>4&&$(this).children('.show-btn').length==0){
            $(this).append('<span class="show-btn">显示全部</span>')
        }
    });
});