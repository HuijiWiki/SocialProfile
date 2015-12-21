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
		$star_page = $per_page*($page-1);
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$output = "";
		$output .= "<form method='get' action='/wiki/special:globalsearch' >
			<input type='text' name='key' value='".$key."' >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='搜索'>
			</form>";
		if ( !is_null($key) ) {
			$resJson = QueryInterface::pageSearch($key, $per_page, $star_page);
			$resObj = json_decode($resJson);
			$resCount = $resObj->hits;
			if ( $resCount == 0 ) {
				$output .= "暂时没有此词条";
			}else{
				$output .= "<div class=\"results-info\"><strong>".$resCount."</strong>条结果中的<strong>".($star_page+1)."<span>到</span>".($per_page*$page)."</strong>条</div>
						<ul class=\"mw-search-results\">";
				foreach ($resObj->sites as $value) {
					$d = strtotime($value->timestamp);
					$output .= "<li><div class=\"mw-search-result-heading\">
										<a href=\"http://".$value->address."\">".$value->title."</a><br>
										<a href=\"http://".$value->sitePrefix.$wgHuijiSuffix."\">".$value->siteName."</a>
									</div>
									<div class=\"searchresult\">".$value->content."
									</div>
									<div class=\"mw-search-result-data\">".date("Y年m月d日 h:i:s", $d)."
									</div>
								</li>";
				}
				$output .= '</ul>';
			}
			/**
			 * Build next/prev navigation links
			 */
			$pcount = $resCount;
			$page_link = $this->getPageTitle();
			$numofpages = ceil($pcount / $per_page);
			// Middle is used to "center" pages around the current page.
			$pager_middle = ceil( $per_page / 2 );
			// first is the first page listed by this pager piece (re quantity)
			$pagerFirst = $page - $pager_middle + 1;
			// last is the last page listed by this pager piece (re quantity)
			$pagerLast = $page + $per_page - $pager_middle;
			// Prepare for generation loop.
			$i = $pagerFirst;
			if ( $pagerLast > $numofpages ) {
				// Adjust "center" if at end of query.
				$i = $i + ( $numofpages - $pagerLast );
				$pagerLast = $numofpages;
			}
			if ( $i <= 0 ) {
				// Adjust "center" if at start of query.
				$pagerLast = $pagerLast + ( 1 - $i );
				$i = 1;
			}
			if ( $numofpages > 1 ) {
				$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';
				$pagerEllipsis = '<li><span>...</span></li>';
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

				// if ( ( $pcount % $per_page ) != 0 ) {
				// 	$numofpages++;
				// }
				// if ( $numofpages >= 9 && $page < $pcount ) {
				// 	$numofpages = 9 + $page;
				// 	if ( $numofpages >= ( $pcount / $per_page ) ) {
				// 		$numofpages = ( $pcount / $per_page ) + 1;
				// 	}
				// }
				// Whether to display the "First page" link
				if ( $i > 1 ) {
					$output .= '<li>' .
						Linker::link(
							$page_link,
							1,
							array(),
							array(
								'key' => $key,
								// 'rel_type' => $rel_type,
								'page' => 1
							)
						).'</li>';	
				}
				// When there is more than one page, create the pager list.
				if ( $i != $numofpages ) {
					if ( $i > 2 ) {
						$output .= $pagerEllipsis;
					}
					for ( ; $i <= $pagerLast && $i <= $numofpages; $i++ ) {
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
							).'</li>';
						}
					}
					if ( $i < $numofpages ) {
						$output .= $pagerEllipsis;
					}
				}
				// Whether to display the "Last page" link
				if ( $numofpages > ( $i - 1 ) ) {
					$output .= '<li>' .
						Linker::link(
							$page_link,
							$numofpages,
							array(),
							array(
								'key' => $key,
								'page' => $numofpages
							)
						).'</li>';
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
