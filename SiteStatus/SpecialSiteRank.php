<?php
/**
 * A special page for rank all sites in huiji.wiki
 *
 * @file
 * @ingroup Extensions
 * @author slx
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class SpecialSiteRank extends SpecialPage {
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		global $wgUser,$wgSitename;
		parent::__construct( 'SiteRank' );
	}
	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgUser,$wgSitename,$wgHuijiPrefix,$wgUserLevels;
		$out = $this->getOutput();
		$this->setHeaders();
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );
		$request = $this->getRequest();
		$method = $request->getVal('method');
		$output = '<i>'.$this->msg( 'editranknote' )->plain().'</i>';
		$month = date("Y-m", time());
	    $allSiteRank = array();
		// if ($method == 'allSiteDonateRank') {
		if ($method == 1) {
			$out->addHtml( TopUsersPoints::getRankingDropdown('站点加油排行榜') );

			$siteArr = UserDonation::getAllSiteDonationRank();
	        $firstFourSite = array_slice($siteArr, 0,21);
	        $k=1;
	        foreach ($firstFourSite as $key => $value) {
	            if ( $k <= 20 ) {
	                $donateSite = WikiSite::newFromPrefix($key);
	                $siteUrl = $donateSite->getUrl();
	                $siteName = $donateSite->getName();
	                $rankSiteAvatar = $donateSite->getAvatar()->getAvatarHtml();
	                $allSiteRank[] = array(
	                                        'site_rank' => $k,
	                                        'site_prefix' => $key,
	                                        'site_score' => $value,//donate number
	                                    );
	                $k++;
	            }
	        }
	        $style = 'display:none';
	        $unit = '元';
		}else{
			$out->addHtml( TopUsersPoints::getRankingDropdown('站点排行榜') );
			// Add CSS
			$yesterday = date('Y-m-d',strtotime('-1 days'));
			$beforeyesterday = date('Y-m-d',strtotime('-2 days'));
			$allSiteRank = AllSitesInfo::getAllSitesRankData( '', $yesterday );
			$beforeallSiteRank = AllSitesInfo::getAllSitesRankData( '', $beforeyesterday );
			$beforeArr = array();
			foreach ($beforeallSiteRank as $value) {
				$beforeArr[$value['site_prefix']] = $value['site_rank'];
			}
			$style = '';
			$unit='马赫';
		}

		$output .= '<div class="top-ranking">';
		$total = count($allSiteRank);
		if($total > 50){
			$allSiteRank = array_slice($allSiteRank,0 ,50);
		} elseif ($total == 0){
			$output .= '<p>站点排行榜正在生成中...请刷新重试！</p>';
			$output .= '</div><div class="cleared"></div>';
			$out->addHTML( $output );
			return;
		}
		foreach ($allSiteRank as $key => $value) {
			if ($style == '') {
				$diff = abs( $value['site_rank'] - $beforeArr[$value['site_prefix']] );
				if( $diff==0 ){
					$diff ='';
				}
				if ( $value['site_rank'] > $beforeArr[$value['site_prefix']] ) {
					$change = 'fa fa-sort-down red';
				}elseif ( $value['site_rank'] < $beforeArr[$value['site_prefix']] ) {
					$change = 'fa fa-sort-up green';
				}else{
					$change = 'fa fa-minus';
				}
			}else{
				$diff = $change = '';
			}
			
			$output .= "<div class=\"top-ranking-row\">
				<span class=\"top-ranking-num\">{$value['site_rank']}.</span>
				<span class=\"top-ranking-name\"><a href='" . HuijiPrefix::prefixToUrl($value['site_prefix']) . "'>" . (new wSiteAvatar($value['site_prefix'], 's'))->getAvatarHtml() .
				HuijiPrefix::prefixToSiteName($value['site_prefix']) ."</a><i style=\"".$style."\" class= \"".$change." hidden-sm hidden-xs\">".$diff."</i><i style=\"".$style."\" class=\"fa fa-flag-checkered hidden-sm hidden-xs\">".(isset($value['best_rank'])?$value['best_rank']:"新")."</i></span><span class=\"top-ranking-points\">".$value['site_score'].$unit.'</sp>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
		}
		$output .= '</div><div class="cleared"></div>';
		$out->addHTML( $output );
		
	}
	function getGroupName() {
    		return 'wiki';
	}
}
