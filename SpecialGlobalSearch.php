<?php
/**
 * add user info
 *
 */

class SpecialGlobalSearch extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'GlobalSearch' );

	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'wiki';
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgParser, $wgHuijiSuffix;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$key = empty($request->getVal( 'key' ))?null:$request->getVal( 'key' );
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$output = "<span>global search</span>";
		$output .= "<form method='get' action='/wiki/special:globalsearch' >
			<input type='text' name='key' >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='搜索'>
			</form>";
		if ( !is_null($key) ) {
			$resJson = QueryInterface::pageSearch($key,3,0);
			$resObj = json_decode($resJson);
			$resCount = $resObj->hits;
			$output .= "<div class=\"results-info\"><strong>".$resCount."</strong>条结果中的<strong>1~20</strong>条</div>
					<ul class=\"mw-search-results\">";
			foreach ($resObj->sites as $value) {
				$d=strtotime($value->timestamp);
				$output .= "<li><div class=\"mw-search-result-heading\">
									<a href=\"http://".$value->address."\">".$value->title."</a><br>
									<a href=\"http://".$value->sitePrefix.$wgHuijiSuffix."\">".$value->siteName."</a>
								</div>
								<div class=\"searchresult\">".$value->content."
								</div>
								<div class=\"mw-search-result-data\">".date("Y年m月d日 h:i", $d)."
								</div>
							</li>";
			}
			$output .= '</ul>';
		}
		$out->addHTML( $output );
	}
}
