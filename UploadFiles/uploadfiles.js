/**
 * Created by huiji-001 on 2015/12/24.
 */
$(function(){
    var token = mw.user.tokens.get('editToken');
    console.log(token);
    $('#upload-btn').click(function(){
        var formData = new FormData(document.getElementById( "uploadfiles" ));
        var file = document.getElementById('file');
        $.ajax({
            url: '/api.php',
            data: {
                action: "upload",
                filename: 'new.png',
                file:formData,
                token:token,
                format:'json'
            },
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(data){
                console.log(data);
            }
        })
    });

//    var api = new mw.Api()
//    api.postWithToken( "edit", {
//        action: "upload",
//        filename: 'zs.png',
//        url:'http://cdn.huijiwiki.com/www/uploads/avatars/my_wiki_15_l.png?r=1442918710',
//        format:'json'
//    } ).done( function( data) {
//        console.log(data);
//    }).fail( function( data) {
//        console.log(data);
//    } );
});