<?php
/**
 * Format the number of daily edits
 * getEditRecordsFromUserIdGroupByDay  每天
 * getRecentPageEditCountOnWikiSiteFromUserId 当天
 * getPageEditCountOnWikiSiteFromUserId一段时间内所有
 *  getRecentPageEditCountOnWikiSiteFromUserId 某天 某月 某日
 */
class UserEditBox{

	public function __construct() {
		require_once __DIR__.'/../HuijiStatistics/interface.php';
	}
	static function getUserEditInfoCache( $userId ) {
		global $wgMemc;
		// $key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		// $data = $wgMemc->get( $key );
		return $data;
	}
	public function getUserEditInfo($userId){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $userId );
		$userEditInfo = self::getUserEditInfoCache( $userId );
		if($userEditInfo == ''){
			$today = date("Y-m-d");
			$oneYearAgo = date("Y-m-d",strtotime("-1 year"));
			$receive = RecordStatistics::getEditRecordsFromUserIdGroupByDay( $userId, $oneYearAgo, $today );
			if($receive->status == 'success'){
			    $userEditInfo = $receive->result;
				$wgMemc->set( $key, $userEditInfo );
			}else{
			 	$userEditInfo = false;
			}
		}else{
			$num = (count($userEditInfo) >= 1)?(count($userEditInfo)-1):0;
			$yesterday = date("Y-m-d",strtotime("-1 day"));
			$receive = RecordStatistics::getPageEditCountOnWikiSiteFromUserId( $userId, '', $yesterday, $yesterday );
			if($receive->status == 'success'){
			    $yesterdayEdit = $receive->result;
				if( $yesterdayEdit > 0 && $yesterday != $userEditInfo[$num]->_id ){
					$addEdit = array('_id'=>$yesterday,'value'=>$yesterdayEdit);
					$userEditInfo = $wgMemc->get( $key );
					$userEditInfo[] = (object)$addEdit;
					$wgMemc->set( $key, $userEditInfo );
				}
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
}