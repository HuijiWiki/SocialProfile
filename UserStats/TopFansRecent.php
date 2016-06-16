<?php

class TopFansRecent extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'TopUsersRecent' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgMemc, $wgHuijiPrefix;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Load CSS
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );

		$periodFromRequest = $request->getVal( 'period' );
		$action = $request->getVal( 'action' );
		$type = $request->getVal( 'type' );
		$user_list = array();
		if ( $action == 'donate' ) {
			$month = date("Y-m", time());
			$unit = 'top-fans-donate';
			if ( $type == 'month' ) {
		        $monthRank = UserDonation::getDonationRankByPrefix( $wgHuijiPrefix, $month );
		        $firstFourRank = array_slice($monthRank, 0, 21);
		        $i = 1;
		        $pageTitle = 'top-fans-monthly-donate-rank';
		        foreach ( $firstFourRank as $key => $value ) {
		            if ( $key != null && $i <= 20 ) {
		                $userM = HuijiUser::newFromName( $key );
		                $user_id = $userM->getId();
		                $user_list[] = array(
	                                        // 'rank'=> $i,
	                                        'user_id' => $user_id,
	                                        'user_name' => $key,
	                                        // 'userUrl' => $userUrlM,
	                                        // 'userAvatar' => $userAvatarM,
	                                        'points' => $value,//donate_number
	                                    );
		                $i++;
		            }
		        }
			}elseif ( $type == 'total' ) {
				$siteTotalRank = UserDonation::getDonationRankByPrefix( $wgHuijiPrefix, '' );
		        $firstFourRankTotal = array_slice($siteTotalRank, 0, 21);
		        $j = 1;
		        $pageTitle = 'top-fans-totally-donate-rank';
		        foreach ( $firstFourRankTotal as $key => $value ) {
		            if ( $key != null && $j <= 20 ) {
		                $userT = HuijiUser::newFromName( $key );
		                $user_id = $userT->getId();
		                $user_list[] = array(
                                            // 'rank'=> $j,
                                            'user_id' => $user_id,
                                            'user_name' => $key,
                                            // 'userUrl' => $userUrlT,
                                            // 'userAvatar' => $userAvatarT,
                                            'points' => $value//donate number
                                        );
		                $j++;
		            }
		        }
			}elseif ( $type == 'allsite' ) {
				$allSiteUserRank = UserDonation::getAllSiteDonationUserRank();
		        $firstFourAllRank = array_slice($allSiteUserRank, 0, 21);
		        $m = 1;
		        $pageTitle = 'top-fans-totally-donate-rank-allsite';
		        foreach ($firstFourAllRank as $key => $value) {
		            if ( $key != null && $m <= 20 ) {
		                $userT = HuijiUser::newFromName( $key );
		                $user_id = $userT->getId();
		                $user_list[] = array(
	                                        // 'rank'=> $m,
	                                        'user_id' => $user_id,
	                                        'user_name' => $key,
	                                        // 'userUrl' => $userUrlA,
	                                        // 'userAvatar' => $userAvatarA,
	                                        'points' => $value//donate number
	                                    );
		                $m++;
		            }
		        }
			}
		}else{
			if ( $periodFromRequest == 'weekly' ) {
				$period = 'weekly';
			} elseif ( $periodFromRequest == 'monthly' ) {
				$period = 'monthly';
			}

			if ( !isset( $period ) ) {
				$period = 'weekly';
			}

			if ( $period == 'weekly' ) {
				$pageTitle = 'top-fans-weekly-points-link';
			} else {
				$pageTitle = 'top-fans-monthly-points-link';
			}
			
			$unit = 'top-fans-points';
			$count = 50;

			// Try cache
			$key = wfForeignMemcKey( 'huiji', '', 'user_stats', $period, 'points', $count );
			$data = $wgMemc->get( $key );

			if ( $data != '' ) {
				// wfDebug( "Got top users by {$period} points ({$count}) from cache\n" );
				$user_list = $data;
			} else {
				// wfDebug( "Got top users by {$period} points ({$count}) from DB\n" );

				$params['ORDER BY'] = 'up_points DESC';
				$params['LIMIT'] = $count;

				$dbr = wfGetDB( DB_SLAVE );
				$res = $dbr->select(
					"user_points_{$period}",
					array( 'up_user_id', 'up_user_name', 'up_points' ),
					array( 'up_user_id <> 0' ),
					__METHOD__,
					$params
				);

				foreach ( $res as $row ) {
					$userObj = User::newFromId( $row->up_user_id );
	                $user_group = $userObj->getEffectiveGroups();
					if ( !in_array('bot', $user_group) && !in_array('bot-global',$user_group)  ) {
						$user_list[] = array(
							'user_id' => $row->up_user_id,
							'user_name' => $row->up_user_name,
							'points' => $row->up_points
						);
					}
				}

				$wgMemc->set( $key, $user_list, 60 * 5 );
			}
		}
		$out->addHtml(TopUsersPoints::getRankingDropdown( '用户'.$this->msg( $pageTitle )->plain() ));
		$out->setPageTitle( $this->msg( $pageTitle )->plain() );
		$x = 1;
		$output = '<div class="top-users">';
		foreach ( $user_list as $item ) {
			$user_title = Title::makeTitle( NS_USER, $item['user_name'] );
			$avatar = new wAvatar( $item['user_id'], 'm' );
			$avatarImage = $avatar->getAvatarURL();
			if ($item['points'] < 0) {
				$points = 0;
			}else{
				$points = $item['points'];
			}
			if($user->getName() == $item['user_name']){
				$active = 'active';
			} else {
				$active = '';
			}
			$output .= '<div class="top-fan-row {$active}">
				<span class="top-fan-num">' . $x . '.</span>
				<span class="top-fan"><a href="' . htmlspecialchars( $user_title->getFullURL() ) . '" >' .
					$avatarImage .
					'</a><a href="' . htmlspecialchars( $user_title->getFullURL() ) . '" >' . $item['user_name'] . '</a>
				</span>';

			$output .= '<span class="top-fan-points"><b>' .
				$this->getLanguage()->formatNum( $points ) . '</b> ' .
				$this->msg( $unit )->plain() . '</span>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
			$x++;
		}

		$output .= '</div><div class="cleared"></div>';
		$out->addHTML( $output );
	}
}
