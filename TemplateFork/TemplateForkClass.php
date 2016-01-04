<?php 

	class TemplateFork{

		/**
		 * [getForkInfoByPageId description]
		 * @param  [type] $target_id 
		 * [ get the template info(come from, fork user..)]
		 * @return [type]            [result json]
		 */
		static function getForkInfoByPageId( $target_id ){
			$result = self::getInfoByPageIdCache( $target_id );
			if ( $result == null ) {
				$result = self::getInfoPageIdDB( $target_id );
			}
			return $result;
		}

		static function getInfoByPageIdCache( $target_id ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getInfoPageIdDB( $target_id ){
			global $wgMemc;
			$dbr = wfGetDB(DB_SLAVE);
			$res = $dbr->select(
				'template_fork',
				array(
					'fork_from',
					'fork_user',
					'fork_date',
				),
				array(
					'target_id' => $target_id
				),
				__METHOD__,
				array( 
					'ORDER BY' => 'fork_date DESC'
				)
			);
			$result = array();
			if( $res ){
				foreach ($res as $value) {
					$result['fork_from'] = $value->fork_from;
					$result['fork_sitename'] = HuijiPrefix::prefixToSiteName($value->fork_from);
					$result['fork_user'] = $value->fork_user;
					$result['fork_date'] = $value->fork_date;
				}
			}
			$jsonRes = json_encode( $result );
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id );
			$wgMemc->set( $key, $jsonRes );
			return $jsonRes;
		}

		/**
		 * getForkCountByPageId
		 * @param  [type] $page_id
		 *   get the template fork count
		 * @return [type]            [result json]
		 * 
		 */
		static function getForkCountByPageId( $page_id ){
			$result = self::getForkCountByPageIdCache( $page_id );
			if ( $result == null ) {
				$result = self::getForkCountByPageIdDB( $page_id );
			}
			return $result;
		}

		static function getForkCountByPageIdCache( $page_id ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $page_id );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getForkCountByPageIdDB( $page_id ){
			global $wgMemc;
			$dbr = wfGetDB(DB_SLAVE);
			$res = $dbr->select(
				'template_fork_count',
				array(
					'fork_count'
				),
				array(
					'template_id' => $page_id
				),
				__METHOD__
			);
			$result = 0;
			if( $res !== false ){
				foreach ($res as $value) {
					$result = $value->fork_count;
				}
			}
			$jsonRes = json_encode( $result );
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $page_id );
			$wgMemc->set( $key, $jsonRes );
			return $jsonRes;
		}

	}

?>