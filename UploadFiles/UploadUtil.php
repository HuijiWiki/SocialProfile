<?php
class UploadUtil {
	public function check($tmpfile, $extension){
	    $magic = MimeMagic::singleton();
       	$mime = $magic->guessMimeType( $tmpfile, false );
        # check mime type, if desired
        global $wgVerifyMimeType;
        if ( $wgVerifyMimeType ) {
            # check mime type against file extension
            if ( !UploadBase::verifyExtension( $mime, $extension ) ) {
                wfErrorLog('not pass!!!!1'.$extension,'/var/log/mediawiki/SocialProfile.log');
                return Status::newFatal( 'uploadcorrupt' );
            }

            # check mime type blacklist
            global $wgMimeTypeBlacklist;
            if ( isset( $wgMimeTypeBlacklist ) && !is_null( $wgMimeTypeBlacklist )
                && UploadBase::checkFileExtension( $mime, $wgMimeTypeBlacklist ) ) {
                wfErrorLog('not pass!!!!2','/var/log/mediawiki/SocialProfile.log');
                return Status::newFatal( 'badfiletype', htmlspecialchars( $mime ) );
            }
        }
        # check for htmlish code and javascript
        if ( UploadBase::detectScript( $tmpfile, $mime, $extension ) ) {
            wfErrorLog('not pass!!!!3','/var/log/mediawiki/SocialProfile.log');
            return Status::newFatal( 'uploadscripted' );
        }
        wfErrorLog('all clear!!!!4','/var/log/mediawiki/SocialProfile.log');
		// wfDebug( __METHOD__ . ": all clear; passing.\n" );
		return Status::newGood();
	}
}