<?php
/**
 * A special page to upload images for system gifts (awards).
 * This is mostly copied from an old version of Special:Upload and changed a
 * bit.
 *
 * @file
 * @ingroup Extensions
 */

class SystemGiftManagerLogo extends GiftManagerLogo {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'SystemGiftManagerLogo', 'giftadmin' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		$this->checkPermissions();
		$this->checkReadonly();		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the robot policies, etc.
		$out->setArticleRelated( false );
		$out->setRobotPolicy( 'noindex,nofollow' );

		// Show a message if the database is in read-only mode
		if ( wfReadOnly() ) {
			$out->readOnlyPage();
			return;
		}

		// If user is blocked, s/he doesn't need to access this page
		if ( $user->isBlocked() ) {
			$out->blockedPage();
			return;
		}

		$this->gift_id = $this->getRequest()->getInt( 'gift_id' );
		$this->initLogo();
		$this->executeLogo();
	}

	function createThumbnail( $imageSrc, $ext, $imgDest, $thumbWidth ) {
		global $wgUseImageMagick, $wgImageMagickConvertCommand, $wgUseOss;

		list( $origWidth, $origHeight, $typeCode ) = getimagesize( $imageSrc );

		if ( $wgUseImageMagick ) { // ImageMagick is enabled
			if ( $origWidth < $thumbWidth ) {
				$thumbWidth = $origWidth;
			}
			$thumbHeight = ( $thumbWidth * $origHeight / $origWidth );
			if ( $thumbHeight < $thumbWidth ) {
				$border = ' -bordercolor white -border 0x' . ( ( $thumbWidth - $thumbHeight ) / 2 );
			}
			if ( $typeCode == 2 ) {
				$dest = $this->avatarUploadDirectory . '/sg_' . $imgDest . '.gif';
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' .
					$thumbWidth . ' -resize ' . $thumbWidth . '  -quality 100 ' .
					$border . ' ' . $imageSrc . ' ' .
					$this->avatarUploadDirectory . '/sg_' . $imgDest . '.jpg'
				);
			}
			if ( $typeCode == 1 ) {
				$dest = $this->avatarUploadDirectory . '/sg_' . $imgDest . '.gif';
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' .
					$thumbWidth . ' -resize ' . $thumbWidth . ' ' . $imageSrc .
					' ' . $border . ' ' .
					$this->avatarUploadDirectory . '/sg_' . $imgDest . '.gif'
				);
			}
			if ( $typeCode == 3 ) {
				$dest = $this->avatarUploadDirectory . '/sg_' . $imgDest . '.png';
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' .
					$thumbWidth . ' -resize ' . $thumbWidth . ' ' . $imageSrc .
					' ' . $this->avatarUploadDirectory . '/sg_' . $imgDest . '.png'
				);
			}
			// Copy the thumb, put it in OSS.
            if ($wgUseOss){
                $bucket = Gifts::GIFT_BUCKET;
                $object = 'sg_' . $imgDest . '.' . $ext;
                $content = file_get_contents($dest); // 上传的文件内容
                try {
                    $this->ossClient->putObject($bucket, $object, $content);
                } catch (Oss\OssException $e) {
                    // print $e->getMessage();
                    wfErrorLog($e->getMessage(),'/var/log/mediawiki/SocialProfile.log');
                }
                unlink( $dest );
            }

		} else { // ImageMagick is not enabled, so fall back to PHP's GD library
			// Get the image size, used in calculations later.
			switch( $typeCode ) {
				case '1':
					$fullImage = imagecreatefromgif( $imageSrc );
					$ext = 'gif';
					break;
				case '2':
					$fullImage = imagecreatefromjpeg( $imageSrc );
					$ext = 'jpg';
					break;
				case '3':
					$fullImage = imagecreatefrompng( $imageSrc );
					$ext = 'png';
					break;
			}

			$scale = ( $thumbWidth / $origWidth );

			// Create our thumbnail size, so we can resize to this, and save it.
			$tnImage = imagecreatetruecolor(
				$origWidth * $scale,
				$origHeight * $scale
			);

			// Resize the image.
			imagecopyresampled(
				$tnImage,
				$fullImage,
				0, 0, 0, 0,
				$origWidth * $scale,
				$origHeight * $scale,
				$origWidth,
				$origHeight
			);

			// Create a new image thumbnail.
			if ( $typeCode == 1 ) {
				imagegif( $tnImage, $imageSrc );
			} elseif ( $typeCode == 2 ) {
				imagejpeg( $tnImage, $imageSrc );
			} elseif ( $typeCode == 3 ) {
				imagepng( $tnImage, $imageSrc );
			}
			// Copy the thumb, put it in OSS.
            if ($wgUseOss){
                $bucket = Gifts::GIFT_BUCKET;
                $object = $imgDest . '.' . $ext;
                $content = $tnImage; // 上传的文件内容
                try {
                    $ossClient->putObject($bucket, $object, $content);
                } catch (OssException $e) {
                    print $e->getMessage();
                }
                imagedestroy( $fullImage );
                imagedestroy( $tnImage );
                unlink( $imageSrc );
            } else {

				// Clean up.
				imagedestroy( $fullImage );
				imagedestroy( $tnImage );

				// Copy the thumb
				copy(
					$imageSrc,
					$this->avatarUploadDirectory . '/sg_' . $imgDest . '.' . $ext
				);
			}
		}
	}

	/**
	 * Move the uploaded file from its temporary location to the final
	 * destination. If a previous version of the file exists, move
	 * it into the archive subdirectory.
	 *
	 * @todo If the later save fails, we may have disappeared the original file.
	 *
	 * @param string $saveName
	 * @param string $tempName full path to the temporary file
	 * @param bool $useRename if true, doesn't check that the source file
	 *					is a PHP-managed upload temporary
	 */
	function saveUploadedFile( $saveName, $tempName, $ext ) {
		$dest = $this->avatarUploadDirectory;

		$this->mSavedFile = "{$dest}/{$saveName}";
		$lext = strtolower($ext);
	 	$this->createThumbnail( $tempName, $lext, $this->gift_id . '_l', 200 );
		$this->createThumbnail( $tempName, $lext, $this->gift_id . '_ml', 50 );
		$this->createThumbnail( $tempName, $lext, $this->gift_id . '_m', 30 );
		$this->createThumbnail( $tempName, $lext, $this->gift_id . '_s', 16 );

		if ( $ext == 'JPG' ) {
			$type = 2;
		}
		if ( $ext == 'GIF' ) {
			$type = 1;
		}
		if ( $ext == 'PNG' ) {
			$type = 3;
		}
		if ($wgUseOss){
            if ( $type !== 2 ) {
                $this->ossClient->deleteObjects(Gifts::GIFT_BUCKET, array(
                    'sg_' . $this->gift_id  . '_s.jpg',
                    'sg_' . $this->gift_id  . '_m.jpg',
                    'sg_' . $this->gift_id  . '_l.jpg',
                    'sg_' . $this->gift_id  . '_ml.jpg',  
                ));
            }
            if ( $type !== 3 ) {
                $this->ossClient->deleteObjects(Gifts::GIFT_BUCKET, array(
                    'sg_' . $this->gift_id  . '_s.png',
                    'sg_' . $this->gift_id  . '_m.png',
                    'sg_' . $this->gift_id  . '_l.png',
                    'sg_' . $this->gift_id  . '_ml.png',  
                ));
            }
            if ( $type !== 1 ) {
                $this->ossClient->deleteObjects(Gifts::GIFT_BUCKET, array(
                    'sg_' . $this->gift_id  . '_s.gif',
                    'sg_' . $this->gift_id  . '_m.gif',
                    'sg_' . $this->gift_id  . '_l.gif',
                    'sg_' . $this->gift_id  . '_ml.gif',  
                ));
            }
            return;

        }

		if ( $ext != 'JPG' ) {
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_ml.jpg' );
			}
		}
		if ( $ext != 'GIF' ) {
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_ml.gif' );
			}
		}
		if ( $ext != 'PNG' ) {
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_s.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_m.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_'. $this->gift_id . '_l.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_l.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/sg_' . $this->gift_id . '_ml.png' );
			}
		}

		return $type;
	}

	/**
	 * Show some text and linkage on successful upload.
	 * @access private
	 */
	function showSuccess( $status ) {
		global $wgUploadPath;

		$ext = 'jpg';

		$output = '<h2>' . wfMessage( 'ga-uploadsuccess' )->plain() . '</h2>';
		$output .= '<h5>' . wfMessage( 'ga-imagesbelow' )->plain() . '</h5>';
		if ( $status == 1 ) {
			$ext = 'gif';
		}
		if ( $status == 2 ) {
			$ext = 'jpg';
		}
		if ( $status == 3 ) {
			$ext = 'png';
		}

		$output .= '<table cellspacing="0" cellpadding="5">
		<tr>
			<td valign="top" style="color:#666666;font-weight:800">' . wfMessage( 'ga-large' )->plain() . '</td>
			<td>'.SystemGifts::getGiftImageTag($this->gift_id, "l").'</td>
		</tr>
		<tr>
			<td valign="top" style="color:#666666;font-weight:800">' . wfMessage( 'ga-mediumlarge' )->plain() . '</td>
			<td>'.SystemGifts::getGiftImageTag($this->gift_id, "ml").'</td>
		</tr>
		<tr>
			<td valign="top" style="color:#666666;font-weight:800">' . wfMessage( 'ga-medium' )->plain() . '</td>
			<td><'.SystemGifts::getGiftImageTag($this->gift_id, "m").'</td>
		</tr>
		<tr>
			<td valign="top" style="color:#666666;font-weight:800">' . wfMessage( 'ga-small' )->plain() . '</td>
			<td>'.SystemGifts::getGiftImageTag($this->gift_id, "s").'</td>
		</tr>
		<tr>
			<td>
				<input type="button" onclick="javascript:history.go(-1)" value="' . wfMessage( 'ga-goback' )->plain() . '">
			</td>
		</tr>';

		$systemGiftManager = SpecialPage::getTitleFor( 'SystemGiftManager' );
		$output .= $this->getLanguage()->pipeList( array(
			'<tr><td><a href="' . htmlspecialchars( $systemGiftManager->getFullURL() ) . '">' .
				wfMessage( 'ga-back-gift-list' )->plain() . '</a>&#160;',
			'&#160;<a href="' . htmlspecialchars( $systemGiftManager->getFullURL( 'id=' . $this->gift_id ) ) . '">' .
				wfMessage( 'ga-back-edit-gift' )->plain() . '</a></td></tr>'
		) );
		$output .= '</table>';
		$this->getOutput()->addHTML( $output );
	}


	/**
	 * Displays the main upload form, optionally with a highlighted
	 * error message up at the top.
	 *
	 * @param string $msg as HTML
	 * @access private
	 */
	function mainUploadForm( $msg = '' ) {
		global $wgUseCopyrightUpload;

		$out = $this->getOutput();
		if ( $msg != '' ) {
			$sub = wfMessage( 'uploaderror' )->plain();
			$out->addHTML( "<h2>{$sub}</h2>\n" .
				"<h4 class='error'>{$msg}</h4>\n" );
		}

		$ulb = wfMessage( 'uploadbtn' )->plain();

		$titleObj = SpecialPage::getTitleFor( 'Upload' );
		$action = htmlspecialchars( $titleObj->getLocalURL() );

		$encDestFile = htmlspecialchars( $this->mDestFile );
		$source = null;

		if ( $wgUseCopyrightUpload ) {
			$source = "
	<td align='right' nowrap='nowrap'>" . wfMessage( 'filestatus' )->plain() . "</td>
	<td><input tabindex='3' type='text' name=\"wpUploadCopyStatus\" value=\"" .
	htmlspecialchars( $this->mUploadCopyStatus ) . "\" size='40' /></td>
	</tr><tr>
	<td align='right'>" . wfMessage( 'filesource' )->plain() . "</td>
	<td><input tabindex='4' type='text' name='wpUploadSource' value=\"" .
	htmlspecialchars( $this->mUploadSource ) . "\" style='width:100px' /></td>
	";
		}

		global $wgUploadPath;
		if ( $gift_image != '' ) {
			$output = '<table>
				<tr>
					<td style="color:#666666;font-weight:800">' .
						wfMessage( 'ga-currentimage' )->plain() . '</td>
				</tr>
				<tr>
					<td>
						'.SystemGifts::getGiftImageTag($this->gift_id, "l").'
					</td>
				</tr>
			</table>
		<br />';
		}
		$out->addHTML( $output );

		$out->addHTML( '
	<form id="upload" method="post" enctype="multipart/form-data" action="">
	<table border="0">
		<tr>

			<td style="color:#666666;font-weight:800">' .
				wfMessage( 'ga-file-instructions' )->escaped() . wfMessage( 'ga-choosefile' )->plain() . '<br />
				<input tabindex="1" type="file" name="wpUploadFile" id="wpUploadFile" style="width:100px" />
			</td>
		</tr>
		<tr>' . $source . '</tr>
		<tr>
			<td>
				<input tabindex="5" type="submit" name="wpUpload" value="' . $ulb . '" />
			</td>
		</tr>
		</table></form>' . "\n"
		);
	}

}
