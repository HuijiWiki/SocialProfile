<?php   
/**
* CommonStyle
*/
class SpecialDonate extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'Donate' );
        
    }

    public function execute( $params ) {
        header("Content-type:text/html;charset=utf-8");
        require_once("alipay.config.php");
        require_once("lib/alipay_submit.class.php");
        global $wgHuijiPrefix, $wgUser;
        $templateParser = new TemplateParser(  __DIR__ . '/pages' );
        $this->setHeaders();
        $request = $this->getRequest();
        $out = $this->getOutput();
        $out->addModuleStyles('ext.socialprofile.donate.css');
        $output = '';
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $request->getVal('WIDout_trade_no');

        //订单名称，必填
        $subject = $request->getVal('WIDsubject');

        //付款金额，必填
        $total_fee = $request->getVal('WIDtotal_fee');
        $feeArr = explode('.',$total_fee);
        if ( isset($total_fee) && count($feeArr) > 1 ) {
            $out->addHTML( '<div class="bs-callout bs-callout-danger">
                                <h4><span class="mw-headline" >'.$this->msg( 'ga-error-donate' )->plain().'</span></h4>
                                <p>'.$this->msg( 'ga-error-donate-no-dot' )->plain().'</p>
                            </div>' );
            return false;
            
        }
        
        //商品描述，可空
        $body = $request->getVal('WIDbody');
        //构造要请求的参数数组，无需改动
        if ( isset($out_trade_no) && $out_trade_no != null && $subject != null && $total_fee != null) {
                $parameter = array(
                    "service"       => $alipay_config['service'],
                    "partner"       => $alipay_config['partner'],
                    "seller_id"  => $alipay_config['seller_id'],
                    "payment_type"  => $alipay_config['payment_type'],
                    "notify_url"    => $alipay_config['notify_url'],
                    "return_url"    => $alipay_config['return_url'],
                    
                    "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
                    "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
                    "out_trade_no"  => $out_trade_no,
                    "subject"   => $subject,
                    "total_fee" => $total_fee,
                    "body"  => $body,
                    "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
                    //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
                    //如"参数名"=>"参数值"goods_type
                    
            );
            //建立请求
            $alipaySubmit = new AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            $out->addHTML( $html_text );
        }
        //current momth
        $month = date('Y-m', time());
        $donateResult = UserDonation::getDonationRankByPrefix( $wgHuijiPrefix, $month );
        //this month total danation
        $currentDonate = array_sum($donateResult);
        $site = WikiSite::newFromPrefix($wgHuijiPrefix);
        $rating = $site->getRating();
        switch ($rating) {
            case 'A':
                $goalDonate = 5000;
                break;
            case 'B':
                $goalDonate = 1000;
                break;
            case 'C':
                $goalDonate = 200;
                break;
            case 'D':
                $goalDonate = 40;
                break;
            case 'E':
                $goalDonate = 8;
                break;
            case 'NA':
                $goalDonate = 200;
                break;
            default:
                $goalDonate = 100;
                break;
        }
        $siteAvatar = (new wSiteAvatar($wgHuijiPrefix, 'l'))->getAvatarHtml();
        $siteDescription = $site->getDescription();
        $userId = $wgUser->getId();
        if ( $userId == null ) {
            $type = 'hidden';
            $display = 'none';
        }else{
            $type = 'checkbox';
            $display = '';
        }
        $percentage = 100*$currentDonate/$goalDonate;
        //tradenumber
        $tradeNum = HuijiFunctions::getTradeNo('DS').'-'.$wgUser->getId();
        $siteName = $site->getName();
        //one site user month rank
        $month = date("Y-m", time());
        $monthRank = UserDonation::getDonationRankByPrefix( $wgHuijiPrefix, $month );
        $firstFourRank = array_slice($monthRank, 0, 6);
        $i=1;
        $resultMonthRank = array();
        foreach ( $firstFourRank as $key => $value ) {
            if ( $key != null && $i <= 5 ) {
                $userM = HuijiUser::newFromName( $key );
                $userPageM = Title::makeTitle( NS_USER, $key );
                $userUrlM = htmlspecialchars( $userPageM->getFullURL() );
                $userAvatarM = $userM->getAvatar('m')->getAvatarURL();
                $resultMonthRank[] = array(
                                        'rank'=> $i,
                                        'userName' => $key,
                                        'userUrl' => $userUrlM,
                                        'userAvatar' => $userAvatarM,
                                        'donateNum' => $value,
                                    );
                $i++;
            }
        }
        
        //one site user total rank
        $siteTotalRank = UserDonation::getDonationRankByPrefix( $wgHuijiPrefix, '' );
        $firstFourRankTotal = array_slice($siteTotalRank, 0, 6);
        $j=1;
        $resultTotalRank = array();
        foreach ( $firstFourRankTotal as $key => $value ) {
            if ( $key != null && $j <= 5 ) {
                $userT = HuijiUser::newFromName( $key );
                $userPageT = Title::makeTitle( NS_USER, $key );
                $userUrlT = htmlspecialchars( $userPageT->getFullURL() );
                $userAvatarT = $userT->getAvatar('m')->getAvatarURL();
                $resultTotalRank[] = array(
                                            'rank'=> $j,
                                            'userName' => $key,
                                            'userUrl' => $userUrlT,
                                            'userAvatar' => $userAvatarT,
                                            'donateNum' => $value
                                        );
                $j++;
            }
        }
        $userMonthLink = $userTotalLink = '';
        if ( count($monthRank) >= 5 ) {
            $userMonthLink = SpecialPage::getTitleFor('TopUsersRecent')->getFullURL(array('action' => 'donate', 'type' => 'month'));
            $hiddenMonth = false;
        }else{
            $hiddenMonth = true;
        }
        if ( count($siteTotalRank) >= 5 ) {
            $userTotalLink = SpecialPage::getTitleFor('TopUsersRecent')->getFullURL(array('action' => 'donate', 'type' => 'total'));
            $hiddenTotal = false;
        }else{
            $hiddenTotal = true;
        }
        $output .= $templateParser->processTemplate(
                            'donate',
                            array(
                                'siteName' => $siteName,
                                'tradeNum' => $tradeNum,
                                'siteAvatar' => $siteAvatar,
                                'siteDescription' => $siteDescription,
                                'display' => $display,
                                'type' => $type,
                                'goalDonate' => $goalDonate,
                                'percentage' => $percentage,
                                'currentDonate' => $currentDonate,
                                'monthRank' => $resultMonthRank,
                                'totalRank' => $resultTotalRank,
                                'moreMonthLink' => $userMonthLink,
                                'moreTotalLink' => $userTotalLink,
                                'hiddenMonth' => $hiddenMonth,
                                'hiddenTotal' => $hiddenTotal,
                            )
                    );
        $out->addHTML( $output );
    }
}
