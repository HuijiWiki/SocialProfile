<?php
/**
 * Format the number of daily edits
 * getEditRecordsFromUserIdGroupByDay  每天
 * getRecentPageEditCountOnWikiSiteFromUserId 当天
 * getPageEditCountOnWikiSiteFromUserId一段时间内所有
 *  getRecentPageEditCountOnWikiSiteFromUserId 某天 某月 某日
 */
class UserEditBox{

	public function __construct( $username ) {
		require_once('/mnt/script/php/interface.php');
	}
	static function getUserEditInfoCache( $userId ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		$data = $wgMemc->get( $key );
		return $data;
	}
	public function getUserEditInfo($userId){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		$userEditInfo = self::getUserEditInfoCache( $userId );
		if($userEditInfo == ''){
			$today = date("Y-m-d");
			$oneYearAgo = date("Y-m-d",strtotime("-1 year"));
			$userEditInfo = RecordStatistics::getEditRecordsFromUserIdGroupByDay( $userId, $oneYearAgo, $today );
			$wgMemc->set( $key, $userEditInfo );
		}else{
			$num = (count($userEditInfo) >= 1)?(count($userEditInfo)-1):0;
			$yesterday = date("Y-m-d",strtotime("-1 day"));
			$yesterdayEdit = RecordStatistics::getPageEditCountOnWikiSiteFromUserId( $userId, '', $yesterday, $yesterday );
			if( $yesterdayEdit > 0 && $yesterday != $userEditInfo[$num]->_id ){
				$addEdit = array('_id'=>$yesterday,'value'=>$yesterdayEdit);
				$userEditInfo = $wgMemc->get( $key );
				$userEditInfo[] = (object)$addEdit;
				$wgMemc->set( $key, $userEditInfo );
			}
		}
		return $userEditInfo;
	}
}