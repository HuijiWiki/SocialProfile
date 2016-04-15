/**
 * Created by huiji-001 on 2016/4/12.
 */
$(function(){
    mw.loader.using( 'oojs-ui' ).done( function () {
        $('.setting-toggle .toggle').each(function(){
            var toggle = new  OO.ui.ToggleSwitchWidget( { value: $(this).data('value') , disabled: $(this).data('state')} );
            var id,value,from;

            toggle.on('change',function(e) {
                e = window.event || e;
                id = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-id').val();
                value = $(e.target).attr('aria-checked') === 'false' ? 2 : 1;    //点击时取的是变前的值，相反
                from = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-from').val();
                $.ajax({
                    url: mw.util.wikiScript(),
                    data: {
                        action: 'ajax',
                        rs: 'wfChangeGiftTitleStatus',
                        rsargs: [id, value, from]
                    },
                    type: 'post',
                    format: 'json',
                    success: function (data) {
//                        mw.notification.notify('设置成功', {tag: 'toggle'});
                    }
                });
//                if(value == 2) {
//                    $(e.target).parents('.list-wrap .admin-setting-li').siblings().find('.oo-ui-toggleWidget-on').click();
//                }
            });
            $(this).append(toggle.$element);
        });

    });
});