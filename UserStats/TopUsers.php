<?php

class TopUsersPoints extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'TopUsers' );
	}

	/**
	 * Get a common dropdown for all ranking pages
	 */
	public static function getRankingDropdown($activeList){
		global $wgUser;
		$templateParser = new TemplateParser(  __DIR__  );
		$followed = UserSiteFollow::getTopFollowedSitesWithDetails($wgUser->getId(), $wgUser->getId());
		$output = $templateParser->processTemplate(
				    'dropdown',
				    array(
				    	'activeList' => $activeList,
				    	'followed' => $followed,
				    	'hasFollowed' => count($followed) > 0,
				    )
				);
		return $output;
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgMemc, $wgUserStatsTrackWeekly, $wgUserStatsTrackMonthly, $wgUserLevels;

		$out = $this->getOutput();

		// Load CSS
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );

		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$user = $this->getUser();

		$out->addHtml(self::getRankingDropdown( '用户'.$this->msg( 'user-stats-alltime-title' ) ));

		$out->setPageTitle( $this->msg( 'user-stats-alltime-title' )->plain() );

		$count = 100;
		$realcount = 50;

		$user_list = array();

		// Try cache
		$key = wfForeignMemcKey( 'huiji', '', 'user_stats', 'top', 'points', $realcount );
		$data = $wgMemc->get( $key );

		if ( $data != '' ) {
			wfDebug( "Got top users by points ({$count}) from cache\n" );
			$user_list = $data;
		} else {
			wfDebug( "Got top users by points ({$count}) from DB\n" );

			$params['ORDER BY'] = 'stats_total_points DESC';
			$params['LIMIT'] = $count;
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'user_stats',
				array( 'stats_user_id', 'stats_user_name', 'stats_total_points' ),
				array( 'stats_user_id <> 0' ),
				__METHOD__,
				$params
			);

			$loop = 0;

			foreach ( $res as $row ) {
				$user = User::newFromId( $row->stats_user_id );
                $user_group = $user->getEffectiveGroups();
				if ( !$user->isBlocked() && !in_array('bot', $user_group) && !in_array('bot-global',$user_group)  ) {
					$user_list[] = array(
						'user_id' => $row->stats_user_id,
						'user_name' => $row->stats_user_name,
						'points' => $row->stats_total_points
					);
					$loop++;
				}

				if ( $loop >= $realcount ) {
					break;
				}
			}

			$wgMemc->set( $key, $user_list, 60 * 5 );
		}

		// $recent_title = SpecialPage::getTitleFor( 'TopUsersRecent' );

		// $output = '<div class="top-fan-nav">
		// 	<h3>' . $this->msg( 'top-fans-by-points-nav-header' )->plain() . '</h3>
		// 	<p><b>' . $this->msg( 'top-fans-total-points-link' )->plain() . '</b></p>';

		// if ( $wgUserStatsTrackWeekly ) {
		// 	$output .= '<p><a href="' . htmlspecialchars( $recent_title->getFullURL( 'period=monthly' ) ) . '">' .
		// 		$this->msg( 'top-fans-monthly-points-link' )->plain() . '</a></p>';
		// }

		// if ( $wgUserStatsTrackMonthly ) {
		// 	$output .= '<p><a href="' . htmlspecialchars( $recent_title->getFullURL( 'period=weekly' ) ) . '">' .
		// 		$this->msg( 'top-fans-weekly-points-link' )->plain() . '</a></p>';
		// }

		// Build nav of stats by category based on MediaWiki:Topfans-by-category
		$by_category_title = SpecialPage::getTitleFor( 'TopFansByStatistic' );

		$byCategoryMessage = $this->msg( 'topfans-by-category' )->inContentLanguage();

		if ( !$byCategoryMessage->isDisabled() ) {
			$output .= '<h1 style="margin-top:15px !important;">' .
				$this->msg( 'top-fans-by-category-nav-header' )->plain() . '</h1>';

			$lines = explode( "\n", $byCategoryMessage->text() );
			foreach ( $lines as $line ) {
				if ( strpos( $line, '*' ) !== 0 ) {
					continue;
				} else {
					$line = explode( '|' , trim( $line, '* ' ), 2 );
					$stat = $line[0];

					$link_text = $line[1];
					// Check if the link text is actually the name of a system
					// message (refs bug #30030)
					$msgObj = $this->msg( $link_text );
					if ( !$msgObj->isDisabled() ) {
						$link_text = $msgObj->parse();
					}

					$output .= '<p> ';
					$output .= Linker::link(
						$by_category_title,
						$link_text,
						array(),
						array( 'stat' => $stat )
					);
					$output .= '</p>';
				}
			}
		}

		$output .= '</div>';

		$x = 1;
		$output .= '<div class="top-users">';
		$last_level = '';

		foreach ( $user_list as $item ) {
			$user_title = Title::makeTitle( NS_USER, $item['user_name'] );
			$avatar = new wAvatar( $item['user_id'], 'm' );
			$commentIcon = $avatar->getAvatarURL();

			// Break list into sections based on User Level if it's defined for this site
			// if ( is_array( $wgUserLevels ) ) {
			// 	$user_level = new UserLevel( number_format( $user['points'] ) );
			// 	if ( $user_level->getLevelName() != $last_level ) {
			// 		$output .= "<div class=\"top-fan-row\"><div class=\"top-fan-level\">
			// 			{$user_level->getLevelName()}
			// 			</div></div>";
			// 	}
			// 	$last_level = $user_level->getLevelName();
			// }
			if($user->getName() == $item['user_name']){
				$active = 'active';
			} else {
				$active = '';
			}
			$output .= "<div class=\"top-fan-row {$active}\">
				<span class=\"top-fan-num\">{$x}.</span>
				<span class=\"top-fan\"><a href='" . htmlspecialchars( $user_title->getFullURL() ) . "'>
					{$commentIcon} </a><a href='" . htmlspecialchars( $user_title->getFullURL() ) . "'>" .
						$item['user_name'] . '</a>
				</span>';

			$output .= '<span class="top-fan-points"><b>' .
				number_format( $item['points'] ) . '</b> ' .
				$this->msg( 'top-fans-points' )->plain() . '</span>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
			$x++;
		}

		$output .= '</div><div class="cleared"></div>';
		$out->addHTML( $output );
	}
	function getGroupName() {
    		return 'users';
	}
}
