<?php   
/**
* TransModal
*/
class SpecialTransModal extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'TransModal' );
    }

    public function execute( $params ) {
        $this->setHeaders();
        $out = $this->getOutput();
        $output = '';
        $output .= '<div class="-wrap">
            <div class="trans-sign huiji-login">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <img src="http://cdn.huiji.wiki/lotr/uploads/0/0d/Translogo.png" class="trans-logo">
                            <h5 class="modal-title" id="ModalLabel">自由的合作翻译平台</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mw-ui-container login-wrap">
                                <div class="trans-login-form">
                                        <input id="trans-sign-name" class="huiji-login-text" type="text" placeholder="请输入用户名">
                                        <input id="trans-sign-password" class="huiji-login-text"  placeholder="请输入密码" type="password">
                                        <input id="trans-sign-mail" class="huiji-login-text"  placeholder="请输入邮箱地址" type="text">
                                        <input type="checkbox" value="1" id="wpRemember" ><label for="wpRemember">我已阅读并同意<a>用户协议</a></label>
                                        <input id="trans-sign-btn" class="mw-ui-button btn mw-ui-block mw-ui-constructive huiji-login-btn" data-loading-text="创建中..." type="button" value="创建账户">
                                        <div class="api-login">
                                            <a>登录已有账户</a>
                                            <div>
                                            <span>联合登录</span>
                                            <a href="#" class="icon-weibo-share"></a>
                                            <a href="#" class="icon-qq-share"></a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="trans-login huiji-login">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <img src="http://cdn.huiji.wiki/lotr/uploads/0/0d/Translogo.png" class="trans-logo">
                            <h5 class="modal-title" id="ModalLabel">自由的合作翻译平台</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mw-ui-container login-wrap">
                                <div class="trans-login-form">
                                        <input id="trans-login-name" class="huiji-login-text" type="text" placeholder="请输入用户名">
                                        <input id="trans-login-password" class="huiji-login-text"  placeholder="请输入密码" type="password">
                                        <a class="forget-pass">忘记密码？</a>
                                        <input type="checkbox" value="1" id="wpRemember" ><label for="wpRemember">记住我的登录状态</label>
                                        <input id="trans-login-btn" class="mw-ui-button btn mw-ui-block mw-ui-constructive huiji-login-btn" data-loading-text="登录中..." type="button" value="登 录">
                                        <div class="api-login">
                                            <a>注册新账户</a>
                                            <div>
                                            <span>联合登录</span>
                                            <a href="#" class="icon-weibo-share"></a>
                                            <a href="#" class="icon-qq-share"></a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="create-trans-group" class="trans-modal">
                <span class="trans-modal-close">×</span>
                <p class="invite-p">邀请成员加入</p>
                <h3 class="trans-modal-title">XXX翻译组</h3>
                <form class="trans-modal-form">
                    <span class="btn-float" id="post-invite-btn">发送邀请</span>
                    <input type="text" id="post-invite" placeholder="请输入邀请的用户ID">
                    <span class="btn-float" id="copy-invite-url-btn">复制邀请链接</span>
                    <input type="text" id="copy-invite-url" placeholder="请输入域名" value="http://huijitrans.com">

                </form>
            </div>

             <div id="create-trans-group" class="trans-modal">
                <span class="trans-modal-close">×</span>
                <h3 class="trans-modal-title">创建翻译组</h3>
                <p class="trans-modal-introduce">使用多人合作在线翻译工具，轻松翻译文档、字幕，汉化软件与游戏，并建立主页发布作品。翻译组创建后，您自动成为组管理员，可以邀请成员加入。</p>
                <form class="trans-modal-form">
                    <input type="text" id="trans-group-name" placeholder="请输入翻译组名称">
                    <span class="domain-float">.huijirans.com</span>
                    <input type="text" id="trans-group-domain" placeholder="请输入域名">
                    <div class="btn btn-default trans-modal-submit caption-submit">创建项目组</div>
                </form>
            </div>

            <div id="caption-upload" class="trans-modal">
                <span class="trans-modal-close">×</span>
                <h3 class="trans-modal-title">创建翻译项目</h3>
                <p class="trans-modal-introduce">翻译项目可以包含多个可排序的文档（例如一篇文章的多个章节），还可以引用翻译组的公共资源。翻译完成后，您可以在TRANS发布翻译作品。</p>
                <form class="trans-modal-form">
                    <input type="text" id="caption-id" placeholder="请输入项目id，不可为中文">
                    <textarea id="caption-des" placeholder="请输入项目描述"></textarea>
                    <input type="file" id="caption-file">

                    <div class="btn btn-default trans-modal-submit caption-submit">创建翻译项目</div>
                </form>
            </div>

            <div id="trans-publish-project" class="trans-modal">
                <span class="trans-modal-close">×</span>
                <h3 class="trans-modal-title">发布作品</h3>
                <p class="trans-modal-introduce warning-p">注意：请不要发布侵犯原作者著作权的翻译作品下载</p>
                <form class="trans-modal-form">
                    <span class="trans-label-left">作品名称</span>
                    <input type="text" id="trans-project-name" class="trans-input-right" placeholder="请输入名称">
                    <span class="trans-label-left">作品描述</span>
                    <textarea id="trans-project-des" class="trans-input-right" placeholder="请输入作品描述"></textarea>
                    <span class="trans-label-left">提供下载</span>
                    <div class="clear trans-checkbox-group">
                    <input type="checkbox" class="trans-checkbox"><span class="trans-label-left">srt</span></div>
                    <div class="btn btn-default trans-modal-submit publish-project-submit">发布到TRANS</div>
                </form>
            </div>

            <div id="trans-admin" class="trans-page">
                <div class="trans-page-header">
                    <h1>XXX字幕组：后台</h1>

                </div>
            <div>
        </div>';
        $out->addHTML($output);
        $out->addModuleStyles('ext.socialprofile.transmodal.css');
    }

}

