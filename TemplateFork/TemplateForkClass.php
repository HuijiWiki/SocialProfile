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
				$result = self::getForkInfoByPageIdDB( $target_id, $prefix );
			}
			return $result;
		}

		static function getInfoByPageIdCache( $target_id, $prefix ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id, $prefix );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getForkInfoByPageIdDB( $target_id, $prefix ){
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
			$key = wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $target_id, $prefix );
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
		static function getForkCountByPageId( $template_id, $prefix_from ){
			// $result = self::getForkCountByPageIdCache( $template_id, $prefix_from );
			if ( $result == null ) {
				$result = self::getForkCountByPageIdDB( $template_id, $prefix_from );
			}
			return $result;
		}

		static function getForkCountByPageIdCache( $template_id, $prefix_from ){
			global $wgMemc;
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $template_id, $prefix_from );
			$result = $wgMemc->get( $key );
			return $result;
		}

		static function getForkCountByPageIdDB( $template_id, $prefix ){
			global $wgMemc;
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'template_fork_count',
				array(
					'fork_count'
				),
				array(
					'template_id' => $template_id
				),
				__METHOD__
			);
			$result = 0;
			if( $res !== false ){
				foreach ($res as $value) {
					$result = $value->fork_count;
				}
			}
			$key = wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $template_id, $prefix_form);
			$wgMemc->set( $key, $result );
			return $result;
		}

	}
	
?>