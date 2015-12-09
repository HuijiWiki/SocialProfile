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

	static function getUserEditInfoCache( $userId ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		$data = $wgMemc->get( $key );
		return $data;
	}
	public function getUserEditInfo($userId){
		global $wgMemc, $wgUser;
		$userId = intval($userId);
		$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		$today = date("Y-m-d");
		$oneYearAgo = date("Y-m-d",strtotime("-1 year"));
		$yesterday = date("Y-m-d",strtotime("-1 day"));
		$userEditInfo = self::getUserEditInfoCache( $userId );
		if($userEditInfo == ''){
			$receive = RecordStatistics::getAllPageEditRecordsFromUserIdGroupByDay( $userId, $oneYearAgo, $yesterday );
			if($receive->status == 'success'){
				$userEditInfo = $receive->result;
				$userEditInfo['lastSeen'] = $today;
				$wgMemc->set( $key, $userEditInfo );
			}else{
			 	throw new Exception("Error getUserEditInfo/getEditRecordsFromUserIdGroupByDay Bad Request");
			}
		}else{
			if ($today == $userEditInfo['lastSeen']){
				return $userEditInfo;
			}
			$Delres = array();
			if($userEditInfo['lastSeen'] == $yesterday){
				$receive = RecordStatistics::getAllPageEditRecordsFromUserIdGroupByDay( $userId, $yesterday, $yesterday);
				if($receive->status == 'success'){
					$Beres = $receive->result;
					$Delres['_id'] = $yesterday;
					$Delres['value'] = $Beres;
					$resData[] = (object)$Delres;
					$userEditInfo = array_merge($userEditInfo, $resData);
					$userEditInfo['lastSeen'] = $today;
					$wgMemc->set( $key, $userEditInfo );		
				}else{
					throw new Exception("Error getUserEditInfo/getPageEditCountOnWikiSiteFromUserId Bad Request");
				}
			}else{
				$receive = RecordStatistics::getAllPageEditRecordsFromUserIdGroupByDay( $userId, $userEditInfo['lastSeen'], $yesterday );
				if($receive->status == 'success'){
					$EditSinceLastSeen = $receive->result;
					$userEditInfo = array_merge($userEditInfo, $EditSinceLastSeen);
					$userEditInfo['lastSeen'] = $today;
					$wgMemc->set( $key, $userEditInfo );		
				}else{
					throw new Exception("Error getUserEditInfo/getEditRecordsFromUserIdGroupByDay Bad Request");
				}
			}
			
		}
		return $userEditInfo;
	}
	static function getTodayEdit($userId){
		$receive = RecordStatistics::getRecentPageEditCountOnWikiSiteFromUserId($userId,'','day');
		if($receive->status == 'success'){
			$editNum = $receive->result;
		}else{
			throw new Exception("Error getTodayEdit/getRecentPageEditCountOnWikiSiteFromUserId Bad Request");
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
			throw new Exception("Error getSiteEditCount/getPageEditCountOnWikiSiteFromUserId Bad Request");
		}
	}
	//pv
	public function getSiteViewCount( $userId, $wikiSite, $fromTime, $toTime ){
		$receive = RecordStatistics::getPageViewCountOnWikiSiteFromUserId(-1,$wikiSite,$fromTime, $toTime);
		if ($receive->status == 'success') {
			return $receive->result;
		}else{
			throw new Exception("Error getSiteViewCount/getPageViewCountOnWikiSiteFromUserId Bad Request");
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
			throw new Exception("Error getSiteEditUserCount/getEditorCountGroupByWikiSite Bad Request");
		}
	}

}
