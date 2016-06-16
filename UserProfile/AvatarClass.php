<?php
use OSS;
/**
 * wAvatar class - used to display avatars
 * Example usage:
 * @code
 *	$avatar = new wAvatar( $wgUser->getID(), 'l' );
 *	$wgOut->addHTML( $avatar->getAvatarURL() );
 * @endcode
 * This would display the current user's largest avatar on the page.
 *
 * @file
 * @ingroup Extensions
 */
class wAvatar {
	public $user_name = null;
	public $user_id;
	public $avatar_type = 0;
	const AVATAR_BUCKET = "huiji-avatar";
	protected $ossClient;

	/**
	 * Constructor
	 * @param $userid Integer: user's internal ID number
	 * @param $size String: 's' for small, 'm' for medium, 'ml' for medium-large and 'l' for large
	 */
	function __construct( $userId, $size ) {
		global $wgUseOss, $wgOssEndpoint;
		if ($wgUseOss){
            require_once "/var/www/html/Confidential.php";
            $accessKeyId = Confidential::$aliyunKey;
            $accessKeySecret = Confidential::$aliyunSecret;
            $endpoint = $wgOssEndpoint;
            try {
                $this->ossClient = new Oss\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            } catch (Oss\OssException $e) {
                wfErrorLog($e->getMessage(),'/var/log/mediawiki/SocialProfile.log');
            }
        }
		$this->user_id = $userId;
		$this->avatar_size = $size;
	}

	/**
	 * Fetches the avatar image's name from the filesystem
	 * @return Avatar image's file name (i.e. default_l.gif or wikidb_3_l.jpg;
	 *			first part for non-default images is the database name, second
	 *			part is the user's ID number and third part is the letter for
	 *			image size (s, m, ml or l)
	 */
	function getAvatarImage() {
		global $wgAvatarKey, $wgUploadDirectory, $wgMemc, $wgUseOss;

		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'avatar', $this->user_id, $this->avatar_size );
		$data = $wgMemc->get( $key );

		// Load from memcached if possible
		if ( $data ) {
			$avatar_filename = $data;
		} else {
			if($wgUseOss){
				
                $bucket = self::AVATAR_BUCKET;
                $avatar_filename = $wgAvatarKey . '_' . $this->user_id .  '_' . $this->avatar_size ;
                $jpgDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".jpg");
                if ($jpgDoesExist){
                	$avatar_filename .= ".jpg" . '?r=' . microtime();
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 * 365 ); // cache for 365 day
                	return $avatar_filename;
                }
                $pngDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".png");
                if ($pngDoesExist){
                	$avatar_filename .= ".png" . '?r=' . microtime();
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 * 365); // cache for 365 day
                	return $avatar_filename;
                }
				$gifDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".gif");  
				if ($gifDoesExist){
                	$avatar_filename .= ".gif" . '?r=' . microtime();
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 * 365); // cache for 365 day
                	return $avatar_filename;
				} 
				$avatar_filename = 'default_' . $this->avatar_size . '.gif';
				$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 * 365); // cache for 365 day
				return $avatar_filename;

			}
			// The old way without oss
			$files = glob( $wgUploadDirectory . '/avatars/' . $wgAvatarKey . '_' . $this->user_id .  '_' . $this->avatar_size . "*" );
			if ( !isset( $files[0] ) || !$files[0] ) {
				$avatar_filename = 'default_' . $this->avatar_size . '.gif';
			} else {
				$avatar_filename = basename( $files[0] ) . '?r=' . filemtime( $files[0] );
			}
			$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
		}
		return $avatar_filename;
	}
	/**
	 * Fetches the avatar image's name from the filesystem
	 * @return Avatar image's file name (i.e. default_l.gif or wikidb_3_l.jpg;
	 *			first part for non-default images is the database name, second
	 *			part is the user's ID number and third part is the letter for
	 *			image size (s, m, ml or l)
	 */
	function getAvatarUrlPath() {
		global $wgUseOss, $wgOssAvatarPath;
		if ($wgUseOss){
			return $wgOssAvatarPath.'/'.$this->getAvatarImage();
		}
		return '/uploads/avatars/'.$this->getAvatarImage();
	}

	/**
	 * @param Array $extraParams: array of extra parameters to give to the image
	 * @return String: <img> HTML tag with full path to the avatar image
	 * @deprecated use getAvatarHtml instead
	 * */
	function getAvatarURL( $extraParams = array() ) {
		return $this->getAvatarHtml( $extraParams );
	}

	/**
	 * @param Array $extraParams: array of extra parameters to give to the image
	 * @return String: <img> HTML tag with full path to the avatar image
	 * @deprecated use getAvatarHtml instead
	 * */
	function getOwnerAvatarURL( $extraParams = array() ) {
		return $this->getAvatarHtml( $extraParams );
	}

	/**
	 * @param Array $extraParams: array of extra parameters to give to the image
	 * @return String: <img> HTML tag with full path to the avatar image
	 * */
	function getAvatarHtml( $extraParams = array() ) {
		global $wgUploadPath, $wgUseOss, $wgOssAvatarPath;
		$user_id = $this->user_id;
		$user = User::newFromId( $user_id );
		$defaultParams = array(
			'src' => "{$wgUploadPath}/avatars/{$this->getAvatarImage()}",
			'alt' => 'avatar',
			'class' => 'headimg',
			'data-name' => $user->getName()
		);
		if ($wgUseOss){
			$defaultParams['src'] =  "{$wgOssAvatarPath}/{$this->getAvatarImage()}";
		}
		$params = array_merge( $extraParams, $defaultParams );

		return Html::element( 'img', $params, '' );
	}
	/**
	 * @param Array $extraParams: array of extra parameters to give to the image
	 * @return String: <a> HTML Anchor tag with full path to the avatar image
	 * */
	function getAvatarAnchor( $extraParams = array() ) {
		global $wgUploadPath, $wgUseOss, $wgOssAvatarPath;
		$user_id = $this->user_id;
		$user = User::newFromId( $user_id );
		$defaultParams = array(
			'src' => "{$wgUploadPath}/avatars/{$this->getAvatarImage()}",
			'alt' => 'avatar',
			'class' => 'headimg',
			'data-name' => $user->getName()
		);
		if ($wgUseOss){
			$defaultParams['src'] =  "{$wgOssAvatarPath}/{$this->getAvatarImage()}";
		}
		$params = array_merge( $extraParams, $defaultParams );
		$linker = Linker::LinkKnown($user->getUserPage(), Html::element( 'img', $params, '' ));
		return $linker;
	}	


}

