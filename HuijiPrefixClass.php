 <?php 
/***
 * A help class to translate huijiprefix and the actual site name.
 */
class HuijiPrefix{
	public static function prefixToSiteName( $prefix ){
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(
			'domain',
			array( 'domain_id', 'domain_name' ),
			array(
				'domain_prefix' => $prefix,
			),
			__METHOD__
		);

		if ( $s !== false ) {
			return $s->domain_name;
		}else{
			return $prefix;
		}
	}
	public static function prefixToSiteNameAnchor( $prefix ){
		return "<a href=\"".self::prefixToUrl($prefix)."\">".self::prefixToSiteName($prefix)."</a>";
	}
	public static function prefixToUrl( $prefix ){
		return 'http://'.$prefix.'.huiji.wiki/';
	}
	public static function getRandomPrefix(){
		
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->select(
			'domain',
			array( 'domain_prefix' ),
			array( 'domain_status' => 0, ),
 			__METHOD__
		);

		if ( $s !== false ) {

			$max = $dbr->numRows($s);
			$rng = rand(0, $max-1);
			$dbr->dataSeek($s, $rng);
			return $dbr->fetchObject($s)->domain_prefix;
			
		}else{
			return '';
		}
	}
}
