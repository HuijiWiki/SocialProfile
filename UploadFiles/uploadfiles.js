/**
 * Created by huiji-001 on 2015/12/24.
 */

var uploadfiles = {
    fileBtn: $('.file-btn').get(0),
    fileInput: $('#file').get(0),
    dragArea: $('#drag-area').get(0),
    submitBtn: $('#upload-btn').get(0),
    token: mw.user.tokens.get('editToken'),
    url: '/api.php',
    index: 0,
    filter: null,
    //触发隐藏input的事件
    funTrigger:function(){
       $('#file').trigger('click');
    },
    funDrag: function(e){
        //阻止浏览器默认拖拽行为
        e.stopPropagation();
        e.preventDefault();
    },
    funGetFiles: function(e){
        e.stopPropagation();
        e.preventDefault();
        //文件列表为选中或者拖拽
        var files = e.target.files||e.dataTransfer.files;
        var num = files.length;
        var self = this;
        mw.notification.autoHideSeconds = 1;
        //多个文件被选中进行遍历
        for(var i=0;i<num;i++) {
            var formData = new FormData();
            var file = files[i];
            var index = self.index;
            var content = '<div class="file-wrap default" id="wrap' + index + '"><i class="fa fa-spinner fa-spin"></i><div class="opacity"></div></div>';
            if (file == undefined)
                return;
            if(file.type.indexOf('image') === -1){
                mw.notification.notify("您拖的不是图片！");
                return false;
            }
            $('#drag-area').before(content);
            self.funContinue();
            //使用formdata进行传输图片，整个formdata作为ajax的data
            formData.append('action', 'upload');
            formData.append('filename', file.name);
            formData.append('stash', 1);
            formData.append('file', file);
            formData.append('token', self.token);
            formData.append('format', 'json');
            self.funDrawImg(index,formData,file.name);
            self.index++;
        }
    },
    funDrawImg: function(index,formData,name){
        var self = this;
        $.ajax({
            url: self.url,
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                if (data.upload.result == "Success") {
                    var content = '<img src="/wiki/特殊:上传藏匿/file/' + data.upload.filekey + '" data-filekey="' + data.upload.filekey + '" data-name="' + name + '" class="file-source wait" >' +
                        '<p class="prompt"></p><p class="file-name">' + name + '</p>';
                    $('#wrap' + index + ' .fa').remove();
                    $('#wrap' + index).removeClass('default').append(content);
                    var width = (data.upload.imageinfo.width) / (data.upload.imageinfo.height) * 120;
                    $('#wrap' + index).css('max-width', width + 'px');

                } else if (data.upload.result == "Warning") {
                    if (data.upload.warnings.exists) {
                        var content = '<img src="/wiki/特殊:上传藏匿/file/' + data.upload.filekey + '" data-filekey="' + data.upload.filekey + '" data-name="' + name + '" class="file-source wait" >' +
                            '<p class="prompt">已存在相同名称，请点名称重新命名</p><p class="file-name">' + name + '</p>';
                        $('#wrap' + index + ' .fa').remove();
                        $('#wrap' + index).removeClass('default').append(content).addClass('warning');
                    } else if (data.upload.warnings.duplicate) {
                        var content = '<img src="/wiki/特殊:上传藏匿/file/' + data.upload.filekey + '" data-filekey="' + data.upload.filekey + '" data-name="' + name + '" class="file-source wait" >' +
                            '<p class="prompt">已存在相同内容，建议删除本文件</p><p class="file-name">' + name + '</p>';
                        $('#wrap' + index + ' .fa').remove();
                        $('#wrap' + index).removeClass('default').append(content).addClass('suggest');
                    } else {
                        console.log(data)
                    }
                }
            }

        });
    },
    funContinue: function(){
        //添加第一张图片后改变样式
        $('#uploadfiles').addClass('continue');
        $('.file-btn').text('添加更多');
        if($('#des-btn').length==0){
            var content = "<div class='btn mw-ui-button mw-ui-constructive' id='des-btn' data-toggle='modal' data-target='.img-description'>批量描述</div>";
            $('#upload-btn').after(content);
        }
    },
    funFirst: function(){
        //当没有图片时返回样式
        $('#uploadfiles').removeClass('continue');
        $('.file-btn').text('选择电脑上的图片');
        $('#des-btn').remove();
    },
    funChangeName: function(){
        var self = this;
        $('#uploadfiles').on('click','.file-name',function(e){
            e.stopPropagation();
            var that = $(this);
            var text = $(this).text();
            var end = text.substring(text.length-4,text.length);
            var content = '<input type="text" class="new-file-name form-control" value="'+text+'">';
            $(this).parents('.file-wrap').append(content);
            $('.new-file-name').focus().blur(function(){
                var val = $(this).val();
                var filekey = $(this).siblings('img').data('filekey');
                //添加文件尾名
                if(val.substring(val.length-4,val.length)!=end){
                    val+=end;
                }
                that.text(val);
                $(this).siblings('img').attr('data-name',val);
                $(this).remove();
                //检查新名称
                $.ajax({
                    url: self.url,
                    data: {
                        action: 'upload',
                        stash: 1,
                        filename: val,
                        filekey: filekey,
                        token: self.token,
                        format: 'json'
                    },
                    type: 'POST',
                    success: function (data) {
                        if(data.upload.result == "Success"){
                            that.parents('.file-wrap').removeClass('warning');
                        }else if(data.upload.warnings.exists){
                            that.parents('.file-wrap').addClass('warning');
                            that.siblings('.prompt').text('已存在相同名称，请点名称重新命名');
                        }else if(data.upload.warnings.duplicate){
                            that.parents('.file-wrap').removeClass('warning').addClass('suggest');
                            that.siblings('.prompt').text('已存在相同内容，建议删除本文件');
                        }else{
                            console.log(data);
                        }
                    }
                });
            }).keyup(function(e){
                e.stopPropagation();
                e.preventDefault();
                if(e && e.keyCode==13){
                    $(this).blur();
                }
            });
        });
    },
    funHover:function(){
        $('#uploadfiles').on('mouseenter mouseleave','.opacity',function(e){
            if(e.type == "mouseenter"){
                var cancel = '<span class="icon-close file-delete"></span>';
                $(this).append(cancel);
            }else if(e.type == "mouseleave") {
                $(this).find('.file-delete').remove();
            }
        });
    },
    funDelete: function(){
        var self = this;
        $('#uploadfiles').on('click','.file-delete',function(e){
            e.stopPropagation();
            $(this).parents('.file-wrap').remove();
            if($('.file-wrap').length == 0){
                self.funFirst();
            }
        });
    },
    funGlobalDescription: function(){
        $('.des-save').click(function(){
            $('.file-source').attr('data-description',$('#des-text').val());
            mw.notification.notify('批量描述保存成功');
            $('.img-description').modal('hide');
        });
    },
    funSelfDescription: function(){
        $('#uploadfiles').on('click','.file-wrap',function(){
            $('.self-img-description').modal('show');
            var that = $(this);
            $('#self-des-text').val(that.find('img').data('description'));
            $('.self-des-save').click(function(){
                that.find('img').attr('data-description',$('#self-des-text').val());
            });
        });
        $('.self-des-save').click(function(){
            mw.notification.notify('描述保存成功');
            $('.self-img-description').modal('hide');
        });
    },
    funBtn:function(e){
        $('#upload-btn').on('click', function () {
            if($('.file-wrap.warning').length==0&&$('.file-wrap').length!=0)
            $(this).button('loading');
        });
    },
    funUpload: function(e){
        var self = this;
        if($('.file-source').length==0){
            mw.notification.notify('请选择文件');
        }else if($('.file-wrap').hasClass('warning')){
            mw.notification.notify('请处理命名已存在的文件');
        }else {
            $('.file-source.wait').each(function (index) {
                var xhr = new XMLHttpRequest();
                var formData = new FormData();
                var that = $(this);
                formData.append('action', 'upload');
                formData.append('filename', $(this).data('name'));
                formData.append('filekey', $(this).data('filekey'));
                formData.append('comment','upload');
                formData.append('text',$(this).data('description'));
                formData.append('token', self.token);
                formData.append('format', 'json');
                if (xhr.upload) {
                    //监听上传进度
                    xhr.upload.addEventListener("progress", function (e) {
                        self.onProgress(file, e.loaded, e.total);
                    }, false);
                }
                xhr.addEventListener("load", uploadComplete, false);
                xhr.open('POST',self.url);
                xhr.send(formData);
                function uploadComplete(evt){
                    var data = JSON.parse(evt.target.responseText);
                    if(data.upload){
                        that.siblings('.opacity,.prompt').remove();
                        that.removeClass('wait');
                        if(index == $('.file-source').length-1){
                            mw.notification.notify('上传成功');
                            $('#upload-btn').button('reset');
                        }
                    }else{
                        console.log(data);
                    }
                }
            });
        }
    },
    onProgress: function(file,loaded,total){
        //获得上传进度动态百分比
        var  percent = (loaded / total * 100).toFixed(2) + '%';
    },
    funAddEvent: function(){
        this.funHover();
        this.funDelete();
        this.funChangeName();
        this.funBtn();
        this.funGlobalDescription();
        this.funSelfDescription();
    },
    init: function(){
        var self = this;
        if(this.dragArea){
            this.dragArea.addEventListener("dragover", function(e) { self.funDrag(e); }, false);
            this.dragArea.addEventListener("dragleave", function(e) { self.funDrag(e); }, false);
            this.dragArea.addEventListener("drop", function(e) { self.funGetFiles(e); }, false);
        }

        if(this.fileBtn){
            this.fileBtn.addEventListener('click', function(){ self.funTrigger();}, false)
        }

        if (this.fileInput) {
            this.fileInput.addEventListener("change", function(e) { self.funGetFiles(e); }, false);
        }

        if (this.submitBtn) {
            this.submitBtn.addEventListener("click", function(e) { self.funUpload(e); }, false);
        }
        this.funAddEvent();
    }
};
$(function(){
    uploadfiles.init();
});
