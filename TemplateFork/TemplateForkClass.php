<?php 

	class TemplateFork{

		/**
		 * [getForkInfoByPageId description]
		 * @param  [type] $target_id 
		 * [ get the template info(come from, fork user..)]
		 * @return [type]            [result json]
		 */
		static function getForkInfoByPageId( $target_id, $prefix ){
			$result = self::getInfoByPageIdCache( $target_id, $prefix );
			if ( $result == null ) {
				$result = self::getForkInfoByPageIdDB( $target_id );
			}
			return $result;
		}

		static function getInfoByPageIdCache( $target_id, $prefix ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id, $prefix );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getForkInfoByPageIdDB( $target_id ){
			global $wgMemc;
			$dbr = wfGetDB(DB_SLAVE);
			$res = $dbr->select(
				'template_fork',
				array(
					'template_id',
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
					$result['template_id'] = $value->template_id;
					$result['fork_from'] = $value->fork_from;
					$result['fork_sitename'] = HuijiPrefix::prefixToSiteName($value->fork_from);
					$result['fork_user'] = $value->fork_user;
					$result['fork_date'] = $value->fork_date;
				}
			}
			$jsonRes = json_encode( $result );
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id, $result['fork_from'] );
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
			$res = self::getForkInfoByPageIdDB( $page_id );
			// print_r($res);die;
			$res = json_decode($res);
			// print_r($res);
			$template_id = $res->template_id;
			$prefix = $res->fork_from;
			$result = self::getForkCountByPageIdCache( $template_id, $prefix );
			if ( $result == null ) {
				$result = self::getForkCountByPageIdDB( $template_id, $prefix );
			}
			return $result;
		}

		static function getForkCountByPageIdCache( $page_id, $prefix ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $page_id, $prefix );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getForkCountByPageIdDB( $page_id, $prefix ){
			global $wgMemc, $isProduction;
			if ( !is_null($prefix) ) {
				if( $isProduction == true &&( $prefix == 'www' || $prefix == 'home') ){
					$prefix = 'huiji_home';
				}elseif ( $isProduction == true ) {
					$prefix = 'huiji_sites-'.str_replace('.', '_', $prefix);
				}else{
					$prefix = 'huiji_'.str_replace('.', '_', $prefix);
				}
			}else{
				die( "error: empty $prefix;function:getAllUploadFileCount.\n" );
			}
			$dbr = wfGetDB( DB_SLAVE,$groups = array(),$wiki = $prefix );
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
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $page_id, $prefix);
			// $wgMemc->set( $key, $jsonRes );
			return $jsonRes;
		}

	}

?>