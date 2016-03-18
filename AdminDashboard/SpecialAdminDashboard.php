<?php
/**
 * A special page for sysop to read news and manage site.
 *
 * @file
 * @ingroup Extensions
 */

class SpecialAdminDashboard extends UnlistedSpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'AdminDashboard', 'admindashboard' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgUploadPath, $wgUser, $wgHuijiPrefix, $wgSiteSettings;
		$templateParser = new TemplateParser(  __DIR__ . '/pages' );
		$out = $this->getOutput();
		$user = $this->getUser();
		if ( !$user->isAllowed( 'AdminDashboard' ) ) {
			$out->permissionRequired( 'AdminDashboard' );
			return;
		}
		// Set the page title, robot policies, etc.
		//$this->setHeaders();

		// Add CSS
		//$out->addModules('ext.socialprofile.userprofile.css');	
		$out->addModules( 'ext.socialprofile.admindashboard.css' );
		
		// Add js and message
		// $out->addModules( 'skins.bootstrapmediawiki.huiji.getRecordsInterface.js' );
		$out->addModules( 'ext.socialprofile.admindashboard.js' );
		//$out->addModules('ext.socialprofile.userprofile.js');

		$output = ''; // Prevent E_NOTICE
	    $yesterday = date("Y-m-d",strtotime("-1 day"));
		$totaledit = SiteStats::edits();
		$ueb = new UserEditBox();
		$rankInfo = AllSitesInfo::getAllSitesRankData( $wgHuijiPrefix, $yesterday );
		$site = WikiSite::newFromPrefix($wgHuijiPrefix);
		$stats = $site->getStats(); 	
		$follows = $site->getFollowers();

		// print_r($follows);
		$followCount = $stats['followers'];
		if($followCount >= 8){
			$follows = array_slice($follows, 0, 8);
			$display = '';
		}else{
			$display = 'none';
		}
		$newFollow = array();
		foreach ($follows as $value) {
			$arr['user_name'] = $value['user_name'];
			$userPage = Title::makeTitle( NS_USER, $value['user_name'] );
			$arr['user_url'] = htmlspecialchars( $userPage->getFullURL() );
			$arr['follow_date'] = wfMessage( 'comments-time-ago', HuijiFunctions::getTimeAgo( strtotime( $value['follow_date'] ) ) )->text();
			$newFollow[] = $arr;
		}
		/* Crew members */
		$sysopRaw = $site->getUsersFromGroup('sysop');
		$sysop = '';
        $nums = count($sysopRaw);
        for ($j=0; $j < $nums; $j++) {
            $sysop .= '<a class="crew-avatar" href="'.$sysopRaw[$j]['url'].'"  title="'.$sysopRaw[$j]['user_name'].'">'.$sysopRaw[$j]['avatar'].'</a>';
        }
        $bureaucratRaw = $site->getUsersFromGroup('bureaucrat');
        $bureaucrat = '';
        $nums = count($bureaucratRaw);
        for ($j=0; $j < $nums; $j++) {
            $bureaucrat .= '<a class="crew-avatar" href="'.$bureaucratRaw[$j]['url'].'"  title="'.$bureaucratRaw[$j]['user_name'].'">'.$bureaucratRaw[$j]['avatar'].'</a>';
        }        
		
		$sentToAll = SpecialPage::getTitleFor( 'SendToFollowers' )->getFullURL();
		$showMore = SpecialPage::getTitleFor( 'EditRank' )->getFullURL();
		$rightsManage = SpecialPage::getTitleFor( '用户权限' )->getFullURL();
		$blockUsers = SpecialPage::getTitleFor( '封禁' )->getFullURL();
		$freezeUsers = SpecialPage::getTitleFor( '解除封禁' )->getFullURL();
		$replaceText = SpecialPage::getTitleFor( '替换文本' )->getFullURL();
		$siteRankPage = SpecialPage::getTitleFor( 'SiteRank' )->getFullURL();
		$allSpecial = SpecialPage::getTitleFor( '特殊页面' )->getFullURL();
		$addEmote = Message::newFromKey('comments-add-emoji-emote')->getTitle()->getFullURL();
		$changePageTitle = Message::newFromKey('Pagetitle')->getTitle()->getFullURL();
		$changeMainpageTitle = Message::newFromKey('MediaWiki:Pagetitle-view-mainpage')->getTitle()->getFullURL();
		$siteAvatar = (new wSiteAvatar($wgHuijiPrefix, 'l'))->getAvatarHtml();
		$token = $user->getEditToken();
		if(is_null($newFollow)){
			$newFollow = false;
		}

		// Settings Panel
		$rating = $site->getRating();
		$settings = $wgSiteSettings;
		$settings = array();
		$settings['enable-pollny'] = array(
					'title' => wfMessage('enable-pollny')->escaped(),
					'description' => wfMessage('enable-pollny-description')->escaped(),
					'value' => wfMessage('enable-disabled')->text(), 
					'level' => 'C',
				);
		$settings['enble-voteny'] = array(
					'title' => wfMessage('enable-voteny')->escaped(),
					'description' => wfMessage('enable-voteny-description')->escaped(),
					'value' => wfMessage('enable-disabled')->text(),
					'level' => 'B',
				);

		//$out->enableOOUI();
// 		$btn = new OOUI\ButtonWidget( array(
//     'label' => 'Click me!'
// ) );
// 	echo $btn->toString();
		// $widget = new OOUI\DeferredWidget( array (
  // 			'type' => 'toggleswitch',
  // 			'class' => 'ToggleSwitchWidget',
		// ) );
		// array_key_exists(key, search)
		// echo $widget->toString();
		switch ($rating) {
			case 'A':
				foreach( $settings as $key => $value){
					if ( $value['level'] == 'A'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}					
			case 'B':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'B'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
			case 'C':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'C'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}

			case 'D':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'D'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
			case 'E':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'E'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
			default:
				foreach( $settings as $key => $value){
					if ($value['level'] == 'NA'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				# code...
				break;
		}

		$output .= $templateParser->processTemplate(
				    'admin_index',
				    array(
				    	'siteRank' => isset($rankInfo[0]['site_rank'])?$rankInfo[0]['site_rank']:'暂无',
				    	'siteScore' => isset($rankInfo[0]['site_score'])?$rankInfo[0]['site_score']:'暂无',
				        'yesterdayCount' => UserSiteFollow::getFollowerCountOneday( $wgHuijiPrefix, $yesterday ),
				        'totalCount' => $stats['followers'],
				        'yesterdayEdit' => $ueb->getSiteEditCount( '', $wgHuijiPrefix, $yesterday, $yesterday ),
				        'totalEdit' => $totaledit,
				        'totalView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, '', '' ),
				        'yesterdayView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, $yesterday, $yesterday ),
				        'newFollow' => $newFollow,
				        'sendToAll' => $sentToAll,
				        'showMore' => $showMore,
				        'display' => $display,
				        'rightsManage' =>$rightsManage,
				        'blockUsers' =>$blockUsers,
				        'freezeUsers' =>$freezeUsers,
				        'replaceText' =>$replaceText,
				        'siteRankPage' =>$siteRankPage,
				        'allSpecial' =>$allSpecial,
				        'siteAvatar' =>$siteAvatar,
				        'addEmote' => $addEmote,
				        'changePageTitle' => $changePageTitle,
				        'changeMainpageTitle' => $changeMainpageTitle,
				        'token' => $token,
				        'bureaucrat' => $bureaucrat,
				        'sysop' => $sysop,
				    )
				);
		$out->addHtml($output);
	}
}
