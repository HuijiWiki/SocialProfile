<?php
/**
 * Format the number of daily edits
 * getEditRecordsFromUserIdGroupByDay  每天
 * getRecentPageEditCountOnWikiSiteFromUserId 当天
 * getPageEditCountOnWikiSiteFromUserId一段时间内所有
 *  getRecentPageEditCountOnWikiSiteFromUserId 某天 某月 某日
 *  getPageViewCountOnWikiSiteFromUserId($userId,$wikiSite,$fromTime,$toTime) pv
 *  getPageEditCountOnWikiSiteFromUserId($userId,$wikiSite,$fromTime,$toTime) pe
 */
class UserEditBox{

	public function __construct() {
		require_once __DIR__.'/../HuijiStatistics/interface.php';
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
		$today = date("Y-m-d");
		$oneYearAgo = date("Y-m-d",strtotime("-1 year"));
		$yesterday = date("Y-m-d",strtotime("-1 day"));
		$userEditInfo = self::getUserEditInfoCache( $userId );
		if($userEditInfo == ''){
			$receive = RecordStatistics::getEditRecordsFromUserIdGroupByDay( $userId, $oneYearAgo, $yesterday );
			if($receive->status == 'success'){
				$userEditInfo = $receive->result;
				$userEditInfo['lastSeen'] = $today;
				$wgMemc->set( $key, $userEditInfo );
			}else{
			 	$userEditInfo = false;
			}
		}else{
			if ($today == $userEditInfo['lastSeen']){
				return $userEditInfo;
			}
			$receive = RecordStatistics::getEditRecordsFromUserIdGroupByDay( $userId, $userEditInfo['lastSeen'], $yesterday );
			if($receive->status == 'success'){
				$EditSinceLastSeen = $receive->result;
				$userEditInfo = array_merge($userEditInfo, $EditSinceLastSeen);
				$userEditInfo['lastSeen'] = $today;
				$wgMemc->set( $key, $userEditInfo );		
			}else{
				$userEditInfo = false;
			}
		}
		return $userEditInfo;
	}
	static function getTodayEdit($userId){
		$receive = RecordStatistics::getRecentPageEditCountOnWikiSiteFromUserId($userId,'','day');
		if($receive->status == 'success'){
			$editNum = $receive->result;
		}else{
			$editNum = false;
		}
		return $editNum;
	}
	static function getSunday($mon,$year){
		$i=mktime(0,0,0,$mon,1,$year);
		$arrSun = array();
        while(1){
            $day=getdate($i);
            if ($day['mon']!=$mon) 
                break;
            if ($day['wday']==0 ) 
                $arrSun[] = "{$day['year']}-{$day['mon']}-{$day['mday']}";
            $i+=24*3600;
        }
        return $arrSun[0];
	}
	//pe
	public function getSiteEditCount( $userId,$wikiSite,$fromTime,$toTime ){
		$receive = RecordStatistics::getPageEditCountOnWikiSiteFromUserId(-1,$wikiSite,$fromTime, $toTime);
		if ($receive->status == 'success') {
			return $receive->result;
		}else{
			return false;
		}
	}
	//pv
	public function getSiteViewCount( $userId, $wikiSite, $fromTime, $toTime ){
		$receive = RecordStatistics::getPageViewCountOnWikiSiteFromUserId(-1,$wikiSite,$fromTime, $toTime);
		if ($receive->status == 'success') {
			return $receive->result;
		}else{
			return false;
		}
	}
	//page edit user
	public function getSiteEditUserCount( $fromTime, $toTime ){
		$receive = RecordStatistics::getEditorCountGroupByWikiSite( $fromTime, $toTime);
		if ($receive->status == 'success') {
			foreach ($receive->result as $value) {
				$resdata[$value->_id] = $value->value;
			}
			return $resdata;
		}else{
			return false;
		}
	}

}
