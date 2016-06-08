<?php
/**
 * wSiteAvatar class - used to display avatars
 * Example usage:
 * @code
 *	$avatar = new wSiteAvatar( $wgUser->getID(), 'l' );
 *	$wgOut->addHTML( $avatar->getAvatarURL() );
 * @endcode
 * This would display the current site's largest avatar on the page.
 *
 * @file
 * @ingroup Extensions
 */
class wSiteAvatar extends wAvatar{

	/**
	 * Fetches the avatar image's name from the filesystem
	 * @return Avatar image's file name (i.e. default_l.gif or wikidb_3_l.jpg;
	 *			first part for non-default images is the database name, second
	 *			part is the user's ID number and third part is the letter for
	 *			image size (s, m, ml or l)
	 */
	function getAvatarImage() {
		global $wgSiteAvatarKey, $wgUploadDirectory, $wgMemc, $wgUseOss;

		$key = wfForeignMemcKey( 'huiji', '', 'site', 'profile', 'avatar', $this->user_id, $this->avatar_size );
		$data = $wgMemc->get( $key );

		// Load from memcached if possible
		if ( $data ) {
			$avatar_filename = $data;
		} else {
			if($wgUseOss){
				
                $bucket = self::AVATAR_BUCKET;
                $avatar_filename = $wgSiteAvatarKey . '_' . $this->user_id .  '_' . $this->avatar_size ;
                $jpgDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".jpg");
                if ($jpgDoesExist){
                	$avatar_filename .= ".jpg";
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
                	return $avatar_filename;
                }
                $pngDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".png");
                if ($pngDoesExist){
                	$avatar_filename .= ".png";
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
                	return $avatar_filename;
                }
				$gifDoesExist = $this->ossClient->doesObjectExist($bucket, $avatar_filename . ".gif");  
				if ($gifDoesExist){
                	$avatar_filename .= ".gif";
                	$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
                	return $avatar_filename;
				} 
				$avatar_filename = 'site_default_' . $this->avatar_size . '.gif';
				$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
				return $avatar_filename;

			}
			$files = glob( $wgUploadDirectory . '/avatars/' . $wgSiteAvatarKey . '_' . $this->user_id .  '_' . $this->avatar_size . "*" );
			if ( !isset( $files[0] ) || !$files[0] ) {
				$avatar_filename = 'site_default_' . $this->avatar_size . '.png';
			} else {
				$avatar_filename = basename( $files[0] ) . '?r=' . filemtime( $files[0] );
			}
			$wgMemc->set( $key, $avatar_filename, 60 * 60 * 24 ); // cache for 24 hours
		}
		return $avatar_filename;
	}
	/**
	 * @param Array $extraParams: array of extra parameters to give to the image
	 * @return String: <img> HTML tag with full path to the avatar image
	 * */
	function getAvatarHtml( $extraParams = array() ) {
		global $wgUploadPath, $wgUseOss, $wgOssAvatarPath;
		$site_prefix = $this->user_id;
		$name = HuijiPrefix::prefixToSiteName( $site_prefix );
		$defaultParams = array(
			'src' => "{$wgUploadPath}/avatars/{$this->getAvatarImage()}",
			'alt' => 'avatar',
			'border' => '0',
			'class' => 'siteimg',
			'data-name' => $name,
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
		global $wgUploadPath, $wgHuijiSuffix;
		$site_prefix = $this->user_id;
		$url = HuijiPrefix::PrefixToUrl($site_prefix);
		$linker = "<a href={$url} class='mw-sitelink'>".$this->getAvatarHtml($extraParams)."</a>";
		return $linker;
	}	
}