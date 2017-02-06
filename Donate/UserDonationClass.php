<?php
/**
 * This Class manages users donation
 */
class UserDonation{
	//add info
	static function addUserDonationInfo( $userName, $sitePrefix, $donationValue ){
		global $wgMemc;
		$dbw = wfGetDB( DB_MASTER );
		$month = date('Y-m', time());
		$dbw -> insert(
				'user_donation',
				array(
					'user_name' => $userName,
					'site_prefix' => $sitePrefix,
					'donation_value' => $donationValue,
					'date' => date('Y-m-d H:i:s', time()),
					'month' => $month,
				),
				 __METHOD__
			);
		if ( $dbw->insertId() ) {
			$key = wfForeignMemcKey('huiji','', 'one_site_user_donation_rank', $sitePrefix, $month );
			$key2 = wfForeignMemcKey('huiji','', 'all_site_user_donation_rank', '', $month );
			$key3 = wfForeignMemcKey( 'huiji', '' , 'all_site_donation_rank', '', $month );
			$key4 = wfForeignMemcKey( 'huiji', '' , 'site_month_donate_goal', $sitePrefix, $month );
			$wgMemc->delete($key);
			$wgMemc->delete($key2);
			$wgMemc->delete($key3);
			$wgMemc->delete($key4);
			return true;
		}
	}

	/**
	 * getDonationInfoByPrefix
	 * @param  string $sitePrefix if null, get allsite donation info
	 * @param  string $month      if null, get all month donation info
	 * @return array
	 */
	static function getDonationInfoByPrefix( $sitePrefix, $month = '*' ){
		global $wgMemc;
		$where = array();
		if ( $sitePrefix != null ) {
			if ( $month != null ) {
				$where = array(
							'site_prefix' => $sitePrefix,
							'month' => $month 
						);
			}else{
				$where = array(
							'site_prefix' => $sitePrefix
						);
			}	
		}else{
			if ( $month != null ) {
				$where = array(
							'month' => $month 
						);
			}else{
				$where = array();
			}
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
					'user_donation',
					array(
						'user_name',
						'site_prefix',
						'donation_value',
						'date',
						'month'
					),
					$where,
					__METHOD__
				);
		$result = array();
		if ($res) {
			foreach ($res as $key => $value) {
				$result[] = array(
							'userName' => $value->user_name,
							'sitePrefix' => $value->site_prefix,
							'donationValue' => $value->donation_value,
							'donateDate' => $value->date,
							'donateMonth' => $value->month,
						);
			}
		}
		return $result;
	}

	/**
	 * getDonationRankByPrefix
	 * @param  string $sitePrefix site's prefix
	 * @param  string $month(2016-05)     if null, get all month donation info
	 * @return array
	 */
	static function getDonationRankByPrefix( $sitePrefix, $month ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'one_site_user_donation_rank', $sitePrefix, $month );
		$data = $wgMemc->get( $key );
		if ( $data == null ) {
			$rankDate = self::getDonationInfoByPrefix( $sitePrefix, $month );
			$rankResult = array();
			foreach ($rankDate as $key => $value) {
				if ( !isset( $rankResult[$value['userName']] ) ) {
					$rankResult[$value['userName']] = $value['donationValue'];
				}else{
					$rankResult[$value['userName']] += $value['donationValue'];
				}
			}
			arsort($rankResult);
			$wgMemc->set( $key, $rankResult );
			return $rankResult;
		}else{
			return $data;
		}
	}

	/**
	 * getAllSiteDonationUserRank
	 * @param  string $month if month(ex:'2016-05') is null, get all sites donation total rank,else get one month rank
	 * @return array
	 */
	static function getAllSiteDonationUserRank( $month='*' ){
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'all_site_user_donation_rank', '', $month );
		$data = $wgMemc->get( $key );
		if( $data == null ){
			$rankDate = self::getDonationInfoByPrefix( '', $month );
			$rankResult = array();
			foreach ($rankDate as $key => $value) {
				if ( !isset( $rankResult[$value['userName']] ) ) {
					$rankResult[$value['userName']] = $value['donationValue'];
				}else{
					$rankResult[$value['userName']] += $value['donationValue'];
				}
			}
			arsort($rankResult);
			$wgMemc->set( $key, $rankResult );
			return $rankResult;
		}else{
			return $data;
		}
	}

	/**
	 * getAllSiteDonationRank
	 * @param  string $month if month is null, get all site rank,else  get all site rank in one month
	 * @return array
	 */
	static function getAllSiteDonationRank( $month ){
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '' , 'all_site_donation_rank', '', $month );
		$data = $wgMemc->get( $key );
		if ( $data == null ) {
			$rankDate = self::getDonationInfoByPrefix( '', $month );
			$rankResult = array();
			foreach ($rankDate as $key => $value) {
				if ( !isset( $rankResult[$value['sitePrefix']] ) ) {
					$rankResult[$value['sitePrefix']] = $value['donationValue'];
				}else{
					$rankResult[$value['sitePrefix']] += $value['donationValue'];
				}
			}
			arsort($rankResult);
			$wgMemc->set( $key, $rankResult );
			return $rankResult;
		}else{
			return $data;
		}
		
	}

}
