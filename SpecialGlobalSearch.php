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
		$page = empty($request->getVal('page'))?1:$request->getVal('page');
		$per_page = 10;
		$star_page = $per_page*($page-1)+1;
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$output = "<span>global search</span>";
		$output .= "<form method='get' action='/wiki/special:globalsearch' >
			<input type='text' name='key' >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='搜索'>
			</form>";
		if ( !is_null($key) ) {
			$resJson = QueryInterface::pageSearch($key, $per_page, $star_page);
			$resObj = json_decode($resJson);
			if ( $resObj == null ) {
				$output .= "暂时没有此词条";
				$resCount = 0;
			}else{
				$resCount = $resObj->hits;
				$output .= "<div class=\"results-info\"><strong>".$resCount."</strong>条结果中的<strong>".$star_page."<span>到</span>".($per_page*$page)."</strong>条</div>
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
			$pcount = $resCount;
			$numofpages = $pcount / $per_page;

			$page_link = $this->getPageTitle();

			if ( $numofpages > 1 ) {
				$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';

				if ( $page > 1 ) {
					$output .= '<li>'.Linker::link(
						$page_link,
						'<span aria-hidden="true">&laquo;</span>',
						array(),
						array(
							'key' => $key,
							// 'rel_type' => $rel_type,
							'page' => ( $page - 1 )
						)
					) . '</li>';
				}

				if ( ( $pcount % $per_page ) != 0 ) {
					$numofpages++;
				}
				if ( $numofpages >= 9 && $page < $pcount ) {
					$numofpages = 9 + $page;
				}
				// if ( $numofpages >= ( $total / $per_page ) ) {
				// 	$numofpages = ( $total / $per_page ) + 1;
				// }

				for ( $i = 1; $i <= $numofpages; $i++ ) {
					if ( $i == $page ) {
						$output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
					} else {
						$output .= '<li>' .Linker::link(
							$page_link,
							$i,
							array(),
							array(
								'key' => $key,
								'page' => $i
							)
						);
					}
				}

				if ( ( $pcount - ( $per_page * $page ) ) > 0 ) {
					$output .= '<li>' .
						Linker::link(
							$page_link,
							'<span aria-hidden="true">&raquo;</span>',
							array(),
							array(
								'key' => $key,
								// 'rel_type' => $rel_type,
								'page' => ( $page + 1 )
							)
						).'</li>';	
				}

				$output .= '</nav></div>';
			}
		}
		$out->addHTML( $output );
	}
}
