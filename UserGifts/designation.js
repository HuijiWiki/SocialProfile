/**
 * Created by huiji-001 on 2016/4/12.
 */
$(function(){
    mw.loader.using( 'oojs-ui' ).done( function () {

        var arr1 = new Array();
        var arr2 = new Array();
        $('#gift-list .setting-toggle .toggle').each(function(index){
            var toggle = new  OO.ui.ToggleSwitchWidget( { value: $(this).data('value') , disabled: $(this).data('state')} );
            var id,val,from;
            toggle.index = index;

            arr1.push(toggle);
            toggle.on('change',function(e) {
                e = window.event || e;
                id = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-id').val();
                val = $(e.target).hasClass('oo-ui-toggleWidget-on') ? 1 : 2;    //点击时取的是变前的值，相反
                from = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-from').val();

                if(val == 2) {
                    var i = toggle.index;
                    for (var j = 0; j < arr1.length; j++) {
                        if (arr1[j].index != i) {
                            arr1[j].value = false;
                            console.log('aaa');
                        }
                    }
                    $(e.target).parents('.list-wrap .admin-setting-li').siblings().find('.oo-ui-toggleWidget-on').removeClass('oo-ui-toggleWidget-on').addClass('oo-ui-toggleWidget-off');
                }
                $.ajax({
                    url: mw.util.wikiScript(),
                    data: {
                        action: 'ajax',
                        rs: 'wfChangeGiftTitleStatus',
                        rsargs: [id, val, from]
                    },
                    type: 'post',
                    format: 'json',
                    success: function (data) {
//                        mw.notification.notify('设置成功', {tag: 'toggle'});
                    }
                });
            });
            $(this).append(toggle.$element);
        });
        $('#system-list .setting-toggle .toggle').each(function(index){
            var toggle = new  OO.ui.ToggleSwitchWidget( { value: $(this).data('value') , disabled: $(this).data('state')} );
            var id,val,from;
            toggle.index = index;

            arr2.push(toggle);
            toggle.on('change',function(e) {
                e = window.event || e;
                id = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-id').val();
                val = $(e.target).attr('aria-checked') === 'false' ? 2 : 1;    //点击时取的是变前的值，相反
                from = $(e.target).parents('.setting-toggle .toggle').siblings('.gift-title-from').val();

                if(val == 2) {
                    var i = toggle.index;
                    for (var j = 0; j < arr2.length; j++) {
                        if (arr2[j].index != i) {
                            arr2[j].value = false;
                        }
                    }
                    $(e.target).parents('.list-wrap .admin-setting-li').siblings().find('.oo-ui-toggleWidget-on').removeClass('oo-ui-toggleWidget-on').addClass('oo-ui-toggleWidget-off');
                }
                $.ajax({
                    url: mw.util.wikiScript(),
                    data: {
                        action: 'ajax',
                        rs: 'wfChangeGiftTitleStatus',
                        rsargs: [id, val, from]
                    },
                    type: 'post',
                    format: 'json',
                    success: function (data) {
//                        mw.notification.notify('设置成功', {tag: 'toggle'});
                    }
                });
            });
            $(this).append(toggle.$element);
        });

    });
});