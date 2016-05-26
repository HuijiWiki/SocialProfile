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
		global $wgUploadPath, $wgUser, $wgHuijiPrefix, $wgSiteSettings, $wgParser,$wgCommentsSortDescending;
		$templateParser = new TemplateParser(  __DIR__ . '/pages' );
		$out = $this->getOutput();
		$user = $this->getUser();
		if ( !$user->isAllowed( 'AdminDashboard' ) ) {
			$out->permissionRequired( 'AdminDashboard' );
			return;
		}
		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		//$out->addModules('ext.socialprofile.userprofile.css');	
		$out->addModuleStyles(
    		array(
    			'ext.socialprofile.admindashboard.css',
    			'ext.comments.css',
    		)
    	);
		$out->addModules( 
			array(
				'ext.socialprofile.admindashboard.js',
				'ext.comments.js',
				'skins.bootstrapmediawiki.emoji'
			) 
		);
		$out->addJsConfigVars( array( 'wgCommentsSortDescending' => $wgCommentsSortDescending ) );

		//$out->addModules('ext.socialprofile.userprofile.js');
		//Enable OOUI
		$out->enableOOUI();


		$output = ''; // Prevent E_NOTICE
	    $yesterday = date("Y-m-d",strtotime("-1 day"));
		$totaledit = SiteStats::edits();
		$ueb = new UserEditBox();
		$rankInfo = AllSitesInfo::getAllSitesRankData( $wgHuijiPrefix, $yesterday );
		$site = WikiSite::newFromPrefix($wgHuijiPrefix);
		$stats = $site->getStats(); 	
		$follows = $site->getFollowers();

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
		$changeMainpageTitle = Message::newFromKey('Pagetitle-view-mainpage')->getTitle()->getFullURL();
		$noarticletext = Message::newFromKey('noarticletext')->getTitle()->getFullURL();
		$quickInsert = Message::newFromKey('Edittools')->getTitle()->getFullURL();
		$preload = Message::newFromKey('Preloads')->getTitle()->getFullURL();
		$siteAvatar = (new wSiteAvatar($wgHuijiPrefix, 'l'))->getAvatarHtml();
		$token = $user->getEditToken();
		if(is_null($newFollow)){
			$newFollow = false;
		}

		// Settings Panel
		$rating = $site->getRating();
		$settings = $wgSiteSettings;
		$settings['hide-bots-in-concile']['title'] = wfMessage('doctor-hide-bots')->escaped();
		$settings['hide-bots-in-concile']['description'] = wfMessage('doctor-hide-bots-description')->escaped();
		$settings['hide-bots-in-concile']['value'] = wfMessage('enable-disabled')->text();
		$settings['hide-bots-in-concile']['level'] = 'NA';
		$settings['enable-pollny']['title'] = wfMessage('enable-pollny')->escaped();
		$settings['enable-pollny']['description'] = wfMessage('enable-pollny-description')->escaped();
		$settings['enable-pollny']['value'] = wfMessage('enable-disabled')->text();
		$settings['enable-voteny']['title'] = wfMessage('enable-voteny')->escaped();
		$settings['enable-voteny']['description'] = wfMessage('enable-voteny-description')->escaped();
		$settings['enable-voteny']['value'] = wfMessage('enable-disabled')->text();
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
		$protip = $gtA = $gtB = $gtC = $gtD = false;
		switch ($rating) {
			case 'A':
				foreach( $settings as $key => $value){
					if ( $value['level'] == 'A'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				$gtA = true;	
				if ($protip == ''){
					$protip = wfMessage('protip-rating-a')->escaped();
				}
				break;		
			case 'B':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'B'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				$gtB = true;
				if ($protip == ''){
					$protip = wfMessage('protip-rating-b')->escaped();
				}
				break;
			case 'C':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'C'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				$gtC = true;
				if ($protip == ''){
					$protip = wfMessage('protip-rating-c')->escaped();
				}
				break;
			case 'D':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'D'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				$gtD = true;
				if ($protip == ''){
					$protip = wfMessage('protip-rating-d')->escaped();
				}
				break;
			case 'E':
				foreach( $settings as $key => $value){
					if ($value['level'] == 'E'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				if ($protip == ''){
					$protip = wfMessage('protip-rating-e')->escaped();
				}
				break;
			default:
				foreach( $settings as $key => $value){
					if ($value['level'] == 'NA'){
						$enable = $site->getProperty($key);
						$settings[$key]['value'] = wfMessage("admin-switch-$enable");
					}
				}
				if ($protip == ''){
					$protip = wfMessage('protip-rating-na')->escaped();
				}
				break;
		}

		$settingRes = array();
		foreach ($settings as $key => $value) {
			$settingRes[]=array(
				'funName' => $key,
				'title' => $value['title'],
				'description' => $value['description'],
				'value' => $value['value'],
				'level' => $value['level'],
			);
		}
		$changeRes = array();
		$changeGroup = $wgUser->changeableGroups();
		$valueableGroup = array(
							'bot' => array(
									'group' => 'bot',
									'groupName' => '机器人',
									'groupClass' => 'label admin-label-bot draggable'
								),
							'sysop' => array(
									'group' => 'sysop',
									'groupName' => '管理员',
									'groupClass' => 'label admin-label-sysop draggable'
								), 
							'bureaucrat' => array(
									'group' => 'bureaucrat',
									'groupName' => '行政员',
									'groupClass' => 'label admin-label-bureaucrat draggable'
								),
							'rollback' => array(
									'group' => 'rollback',
									'groupName' => '回退员',
									'groupClass' => 'label admin-label-rollback draggable'
								),
							'staff' => array(
									'group' => 'staff',
									'groupName' => '职员',
									'groupClass' => 'label admin-label-staff draggable'
								),
							'member' => array(
									'group' => 'member',
									'groupName' => '成员',
									'groupClass' => 'label admin-label-member draggable'
								),
							'translate-proofr' => array(
									'group' => 'translate-proofr',
									'groupName' => '校对',
									'groupClass' => 'label admin-label-translate-proofr draggable'
								)
						);
		$userRight = $changeGroup['add'];
		if (in_array('staff', $userRight)) {
		    $changeRes[] = $valueableGroup['staff'];
		}
		if (in_array('bureaucrat', $userRight)) {
		    $changeRes[] = $valueableGroup['bureaucrat'];
		}
		if (in_array('sysop', $userRight)) {
		    $changeRes[] = $valueableGroup['sysop'];
		}
		if (in_array('rollback', $userRight)) {
		    $changeRes[] = $valueableGroup['rollback'];
		}
		if (in_array('bot', $userRight)) {
		    $changeRes[] = $valueableGroup['bot'];
		}
		if (in_array('member', $userRight)) {
		    $changeRes[] = $valueableGroup['member'];
		}
		if (in_array('translate-proofr', $userRight)) {
		    $changeRes[] = $valueableGroup['translate-proofr'];
		}
		if ($site->getProperty('hide-bots-in-concile') == 1){
			$showBots = false;
		} else {
			$showBots = true;
		}


		$doctor = new WikiDoctor();
		$advice = '';
		switch($doctor->engagementCheck()){
			case 'score':
				$advice = wfMessage('doctor-score-too-low')->escaped();
				break;
			case 'followers':
				$advice = wfMessage('doctor-followers-too-few')->escaped();
				break;
			case 'articles':
				$advice = wfMessage('doctor-articles-too-few')->escaped();
				break;
			case 'edits':
				$advice = wfMessage('doctor-edits-too-few')->escaped();
				break;
		};
		list($problem, $num) = $doctor->categoryCheck();
		if ($num == ''){
			$hasCategoryAdvice = false;
		} else {
			$hasCategoryAdvice = true;
		}
		$linkAdvice = wfMessage('doctor-link-advice', $doctor->linkCheck())->parse();
		$categoryAdvice = wfMessage('doctor-category-advice', $problem, $num)->parse();
		$commentHtml = '<div class="clearfix"></div>';
        $wgParser->setTitle(Title::newFromId(1));
        $commentHtml .= CommentsHooks::displayComments( '', array(), $wgParser); 

        $inviteLink = SpecialPage::getTitleFor('InviteUser')->getFullURL(array('user' => $user->getName(), 'prefix' => $wgHuijiPrefix));


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
				        'noarticletext' =>$noarticletext,
				        'replaceText' =>$replaceText,
				        'siteRankPage' =>$siteRankPage,
				        'allSpecial' =>$allSpecial,
				        'siteAvatar' =>$siteAvatar,
				        'addEmote' => $addEmote,
				        'changePageTitle' => $changePageTitle,
				        'changeMainpageTitle' => $changeMainpageTitle,
				        'quickInsert' => $quickInsert,
				        'preload' => $preload,
				        'token' => $token,
				        'bureaucrat' => $bureaucrat,
				        'sysop' => $sysop,
				        'siteName' => $site->getName(),
				        'siteDescription' => $site->getDescription(),
				        'settingRes' => $settingRes,
				        'siteLevel' => $site->getRating(),
				        'changeRes' => $changeRes,
				        'gtA' => $gtA,
				        'gtB' => $gtB,
				        'gtC' => $gtC,
				        'gtD' => $gtD,
				        'protip' => $protip,
				        'advice' => $advice,
				        'hasCategoryAdvice' =>$hasCategoryAdvice,
				        'categoryAdvice' => $categoryAdvice,
				        'linkAdvice' => $linkAdvice,
				        'comments' => $commentHtml,
				        'showBots' => $showBots,
				        'inviteLink' => $inviteLink,


				    )
				);
		$out->addHtml($output);
	}
}
