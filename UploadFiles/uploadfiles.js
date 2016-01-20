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
    type:'png，jpg，jpeg，ogg，doc，xls，ppt，sxc，pdf，gif，ass，svg，ogg，ogv，oga，flac，wav，webm，ttf',
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
        //多个文件被选中进行遍历
        for(var i=0;i<num;i++) {
            var formData = new FormData();
            var file = files[i];
            var index = self.index;
            var content = '<div class="file-wrap default" id="wrap' + index + '"><i class="fa fa-spinner fa-spin"></i><div class="opacity"></div></div>';
            if (file == undefined)
                return;
            if(self.type.indexOf(file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase()) < 0){
                mw.notification.notify("您拖的文件不符合类型！");
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
            self.funDrawImg(index,formData,file.name,file.size,file);
            self.index++;
        }
    },
    funDrawImg: function(index,formData,name,size,file){
        var self = this;
        var reader = new FileReader();
        var selector = $('#wrap'+index);
        //将文件以Data URL形式读入页面
        reader.readAsDataURL(file);
        reader.onload=function(e){
            var src = this.result;
            var result = '';
            if(file.type.indexOf('image') === -1) {
                if(file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase() == 'pdf'){
                    src = '/resources/assets/file-type-icons/fileicon-pdf.png';
                }else if(file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase() == 'ogg'){
                    src = '/resources/assets/file-type-icons/fileicon-ogg.png';
                }else if(file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase() == 'ttf'){
                    src = '/resources/assets/file-type-icons/fileicon-ttf.png';
                }else{
                    src = '/resources/assets/file-type-icons/fileicon.png';
                }
            }
            result='<img src="' + src +'" data-name="'+name+'" data-description="" class="file-source wait" alt="" /><p class="prompt"></p><p class="file-name">' + name + '</p>';
            selector.find('i').remove();
            selector.append(result);

        };
        $.ajax({
            url: self.url,
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                selector.find('img').attr('data-filekey',data.upload.filekey).removeClass('default');
                if (data.upload.result == "Warning") {
                    if (data.upload.warnings.exists) {
                        selector.find('.prompt').text('已存在相同名称，请点名称重新命名');
                        selector.addClass('warning');
                    } else if (data.upload.warnings.duplicate) {
                        selector.find('.prompt').text('已存在相同内容，建议删除本文件');
                        selector.addClass('suggest');
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
        $('.file-btn').text('选择电脑上的文件');
        $('#des-btn').remove();
    },
    funChangeName: function(){
        var self = this;
        $('#uploadfiles').on('click','.file-name',function(e){
            e.stopPropagation();
            var that = $(this);
            var text = $(this).text();
            var end = text.substr(text.lastIndexOf(".")).toLowerCase();
            var content = '<input type="text" class="new-file-name form-control" value="'+text+'">';
            $(this).parents('.file-wrap').append(content);
            $('.new-file-name').focus().blur(function(){
                var val = $(this).val();
                var filekey = $(this).siblings('img').data('filekey');
                //添加文件尾名
                if(val.substr(val.lastIndexOf(".")).toLowerCase()!=end){
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
                            that.siblings('.prompt').text('');
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
        $('#uploadfiles').on('click','.opacity',function(){
            $('.self-img-description').modal('show');
            var that = $(this);
            $('#self-des-text').val(that.siblings('img').data('description'));
            $('.self-des-save').click(function(){
                that.siblings('img').attr('data-description',$('#self-des-text').val());
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
    funHref: function(){
        $('#uploadfiles').on('click','.file-wrap img',function(e){
            e.stopPropagation();
            window.open('/wiki/文件:'+$(this).data('name'));
        });
    },
    funUpload: function(e){
        var self = this;
        var len = $('.file-source.wait').length;
        if(len==0){
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
                formData.append('ignorewarnings','true');
                formData.append('comment','upload');
                formData.append('text',$(this).data('description'));
                formData.append('token', self.token);
                formData.append('format', 'json');
                that.before('<div class="upload-progress"></div>');
                if (xhr.upload) {
                    //监听上传进度
                    xhr.addEventListener("progress", function (e) {
                        self.onProgress(that, e.loaded, e.total);
                    }, false);
                    xhr.upload.addEventListener("progress", function (e) {
                        self.onUploadProgress(that, e.loaded, e.total);
                    }, false);
                }
                xhr.addEventListener("load", uploadComplete, false);
                xhr.open('POST',self.url);
                xhr.send(formData);
                function uploadComplete(evt){
                    var data = JSON.parse(evt.target.responseText);
                    that.siblings('.opacity,.prompt').remove();
                    if(index == len-1){
                        mw.notification.notify('上传成功');
                        $('#upload-btn').button('reset');
                    }
                    that.parents('.file-wrap').removeClass('suggest');
                    that.removeClass('wait');
                }
            });
        }
    },
    onUploadProgress: function(that,loaded,total){
        //获得上传进度动态百分比
        var  percent = (loaded / total * 50).toFixed(2) + '%';
        that.siblings('.upload-progress').css('width',percent);
    },
    onProgress: function(that,loaded,total){
        //获得下载进度动态百分比
        var  percent = 50+parseInt((loaded / total * 50).toFixed(2)) + '%';
        that.siblings('.upload-progress').css('width',percent);
    },
    funAddEvent: function(){
        this.funHover();
        this.funDelete();
        this.funChangeName();
        this.funBtn();
        this.funGlobalDescription();
        this.funSelfDescription();
        this.funHref();
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
    mw.notification.autoHideSeconds = 1;
    uploadfiles.init();
    
});
