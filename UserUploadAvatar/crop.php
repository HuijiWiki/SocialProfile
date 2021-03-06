<?php
/**
 * This Class is way too hacky. It violate a bunch of rules of mediawiki principle.
 * We should rewrite this someday. For now it is working. @author Reasno
 * 
 * 
 */
use OSS;
class CropAvatar {
    private $src;
    private $data;
    private $file;
    private $type;
    private $extension;
    private $msg;
    private $avatarUploadDirectory;
    private $isUserAvatar;
    private $ossClient;


    function __construct($src, $data, $file, $isUserAvatar = true) {
        global $wgUseOss, $wgOssEndpoint;
        if ($wgUseOss){
            require_once "/var/www/html/Confidential.php";
            $accessKeyId = Confidential::$aliyunKey;
            $accessKeySecret = Confidential::$aliyunSecret;
            $endpoint = $wgOssEndpoint;
            try {
                $this->ossClient = new OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            } catch (OssException $e) {
                // print $e->getMessage();
            }
        }
        $this->isUserAvatar = $isUserAvatar;
        if (filter_var($src, FILTER_VALIDATE_URL)){
            $this->putExternalFile($src);
        } else {
            $this->setSrc($file->getTempName());
            $this->file = $file;
            if (!empty($data)){
                $this->setData($data);
                $this->crop($this->src, $this->file->getTempName(), $this->data);
            }
            $this->setFile($this->file);   
          
        }
        $responseBody = array(
            'state'  => 200,
            'message' => $this -> getMsg(),
            'result' => $this -> getResult(),
        );

    }
    private function putExternalFile($src){
        global $wgUser, $wgHuijiPrefix, $wgAvatarKey, $wgSiteAvatarKey, $wgUploadDirectory, $wgUseOss;
        $file = file_get_contents($src);
        if ( empty($file) ){
            $this->msg = "无法读取图片文件（错误代码：10）";
            return;
        }
        if (! $this->isUserAvatar ){
            $uid = $wgHuijiPrefix;
            $avatarKey = $wgSiteAvatarKey;
            $avatar = new wSiteAvatar( $uid, 'l' );
        } else {
            $uid = $wgUser->getId();
            $avatarKey = $wgAvatarKey;
            $avatar = new wAvatar( $uid, 'l' );
        }
        $tempName = tempnam("/tmp", "php");
        file_put_contents( $tempName, $file);

        $type = exif_imagetype( $tempName );
    
        if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {
            $this->avatarUploadDirectory = $wgUploadDirectory . '/avatars';
            if ($wgUseOss){
                $this->avatarUploadDirectory = "/tmp";
            }
            $nameL = $avatarKey . '_' . $uid . '_l';
            $nameML = $avatarKey . '_' . $uid . '_ml';
            $nameM = $avatarKey . '_' . $uid . '_m';
            $nameS = $avatarKey . '_' . $uid . '_s';
            $imageInfo = getimagesize( $tempName );
            switch ( $imageInfo[2] ) {
                case 1:
                    $ext = 'gif';
                    break;
                case 2:
                    $ext = 'jpg';
                    break;
                case 3:
                    $ext = 'png';
                    break;
                default:
                    return $this->msg = '请上传如下类型的图片: JPG, PNG, GIF（错误代码：14）';
            }
            $veri = UploadUtil::check($tempName, $ext);
            if ( !$veri->isGood() ) {
                return $this->msg = '文件类型与MIME不符（错误代码：15）';
            }
        // upload first avatar should be awarded, atomically
            if (HuijiFunctions::addLock('first-avatar-'.$uid)){

                if ( $this->isUserAvatar && strpos( $avatar->getAvatarImage(), 'default_' ) !== false ) {
                    $stats = new UserStatsTrack( $uid, $wgUser->getName() );
                    $stats->incStatField( 'user_image' );
                }  
                $this->createThumbnail( $tempName , $imageInfo, $nameL, 200 );
                $this->createThumbnail( $tempName , $imageInfo, $nameML, 50 );
                $this->createThumbnail( $tempName , $imageInfo, $nameM, 30 );
                $this->createThumbnail( $tempName , $imageInfo, $nameS, 16 );

                unlink( $tempName );
                $this->cleanUp( $ext, $avatarKey, $uid );
              
                HuijiFunctions::releaseLock('first-avatar-'.$uid);
            }
        } else {
            $this->msg = '请上传如下类型的图片: JPG, PNG, GIF（错误代码：12）';
            unlink( $tempName );
        }
    
    }

    private function setSrc($src) {
        global $wgUploadDirectory, $wgUseOss;
        if (!empty($src)) {
            $type = exif_imagetype($src);
            if ($type) {
                $this->avatarUploadDirectory = $wgUploadDirectory . '/avatars';
                if ($wgUseOss){
                    $this->avatarUploadDirectory = "/tmp";
                }
                $this->src = $src;
                $this->type = $type;
                $this->extension = image_type_to_extension($type);
            }
        }
    }

    private function setData($data) {
        if (!empty($data)) {
            $this->data = json_decode(stripslashes($data));
        }
    }

    private function setFile($file) {
        global $wgUploadDirectory, $wgAvatarKey, $wgMemc, $wgUser, $wgSiteAvatarKey, $wgHuijiPrefix, $wgUploadAvatarInRecentChanges;
    
        if (! $this->isUserAvatar ){
            $uid = $wgHuijiPrefix;
            $avatarKey = $wgSiteAvatarKey;
            $avatar = new wSiteAvatar( $uid, 'l' );
        } else {
            $uid = $wgUser->getId();
            $avatarKey = $wgAvatarKey;
            $avatar = new wAvatar( $uid, 'l' );
        }
        // $dest = $this->avatarUploadDirectory;
        $imageInfo = getimagesize( $file->getTempName() );
        $errorCode = $file->getError();
        if ($errorCode === UPLOAD_ERR_OK) {
            $type = $this->type;

            if ($type) {
                $veri = UploadUtil::check($file->getTempName(), substr($this->extension, 1) );
                if ( !$veri->isGood() ) {
                    return $this->msg = '文件类型与MIME不符（错误代码：15）';
                }

                // $src = $this->avatarUploadDirectory. '/' . date('YmdHis') . '.original' . $extension;

                if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {

                    // if (file_exists($src)) {
                    //     unlink($src);
                    // }
                    // If this is the user's first custom avatar, update statistics (in
                    // case if we want to give out some points to the user for uploading
                    // their first avatar)
                    // 
                    if (HuijiFunctions::addLock('first-avatar-'.$uid)){
                        if ( $this->isUserAvatar && strpos( $avatar->getAvatarImage(), 'default_' ) !== false ) {
                            $stats = new UserStatsTrack( $uid, $wgUser->getName() );
                            $stats->incStatField( 'user_image' );
                        }
                        $this->createThumbnail( $file->getTempName() , $imageInfo, $avatarKey . '_' . $uid . '_l', 200 );
                        $this->createThumbnail( $file->getTempName() , $imageInfo, $avatarKey . '_' . $uid . '_ml', 50 );
                        $this->createThumbnail( $file->getTempName() , $imageInfo, $avatarKey . '_' . $uid . '_m', 30 );
                        $this->createThumbnail( $file->getTempName() , $imageInfo, $avatarKey . '_' . $uid . '_s', 16 );
                        switch ( $imageInfo[2] ) {
                            case 1:
                                $ext = 'gif';
                                break;
                            case 2:
                                $ext = 'jpg';
                                break;
                            case 3:
                                $ext = 'png';
                                break;
                            default:
                                return $this->msg = '请上传如下类型的图片: JPG, PNG, GIF（错误代码：14）';
                        }
                        $this->cleanUp($ext, $avatarKey, $uid);

                        HuijiFunctions::releaseLock('first-avatar-'.$uid);
                    }
              
                    /* add log entry */
              		if ($this->isUserAvatar){
                        $log = new LogPage( 'avatar' );
              		    if ( !$wgUploadAvatarInRecentChanges ) {
              			   $log->updateRecentChanges = false;
              		    }
                		$log->addEntry(
                			'avatar',
                			$wgUser->getUserPage(),
                			wfMessage( 'user-profile-picture-log-entry' )->inContentLanguage()->text()
                		);
              		} else {
                        $log = new LogPage( 'site-avatar' );
                  		if ( !$wgUploadAvatarInRecentChanges ) {
                  			$log->updateRecentChanges = false;
                  		}
                    	$log->addEntry(
                    		'site-avatar',
                    		$wgUser->getUserPage(),
                    			wfMessage( 'site-avatar-log-entry' )->inContentLanguage()->text()
                    	);      		  
              	    }
                } else {
                    $this->msg = '请上传如下类型的图片: JPG, PNG, GIF（错误代码：12）';
                }
            } else {
                $this->msg = '请上传一个图片文件（错误代码：11）';
            }
        } else {
            $this->msg = $this->codeToMessage($errorCode);
        }
    }
    private function cleanUp ($ext, $avatarKey, $uid){
        global $wgMemc, $wgUseOss, $wgOssAvatarPath;
        if ($this->isUserAvatar){
            $key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'avatar', $uid, 's' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'avatar', $uid, 'm' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'avatar', $uid , 'l' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'avatar', $uid, 'ml' );
            $data = $wgMemc->delete( $key );
         
        } else {
            $key = wfForeignMemcKey( 'huiji', '', 'site', 'profile', 'avatar', $uid, 's' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'site', 'profile', 'avatar', $uid, 'm' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'site', 'profile', 'avatar', $uid , 'l' );
            $data = $wgMemc->delete( $key );
      
            $key = wfForeignMemcKey( 'huiji', '', 'site', 'profile', 'avatar', $uid, 'ml' );
            $data = $wgMemc->delete( $key );           
        } 
        if ($wgUseOss){
            if ( $ext !== 'jpg' ) {
                $this->ossClient->deleteObjects(wAvatar::AVATAR_BUCKET, array(
                    $avatarKey . '_' . $uid . '_s.jpg',
                    $avatarKey . '_' . $uid . '_m.jpg',
                    $avatarKey . '_' . $uid . '_l.jpg',
                    $avatarKey . '_' . $uid . '_ml.jpg',  
                ));
            }
            if ( $ext !== 'png' ) {
                $this->ossClient->deleteObjects(wAvatar::AVATAR_BUCKET, array(
                    $avatarKey . '_' . $uid . '_s.png',
                    $avatarKey . '_' . $uid . '_m.png',
                    $avatarKey . '_' . $uid . '_l.png',
                    $avatarKey . '_' . $uid . '_ml.png',  
                ));
            }
            if ( $ext !== 'gif' ) {
                $this->ossClient->deleteObjects(wAvatar::AVATAR_BUCKET, array(
                    $avatarKey . '_' . $uid . '_s.gif',
                    $avatarKey . '_' . $uid . '_m.gif',
                    $avatarKey . '_' . $uid . '_l.gif',
                    $avatarKey . '_' . $uid . '_ml.gif',  
                ));
            }
            return;

        }
        if ( $ext !== 'jpg' ) {
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.jpg' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.jpg' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.jpg' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.jpg' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.jpg' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.jpg' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.jpg' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.jpg' );
            }
        }
        if ( $ext !== 'gif' ) {
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.gif' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.gif' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.gif' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.gif' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.gif' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.gif' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.gif' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.gif' );
            }
        }
        if ( $ext !== 'png' ) {
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.png' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_s.png' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.png' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_m.png' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.png' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_l.png' );
            }
            if ( is_file( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.png' ) ) {
                unlink( $this->avatarUploadDirectory . '/' . $avatarKey . '_' . $uid . '_ml.png' );
            }
        }
  
    }
    private function crop($src, $dst, $data) {
        if (!empty($src) && !empty($dst) && !empty($data)) {
            switch ($this->type) {
                case IMAGETYPE_GIF:
                    $src_img = imagecreatefromgif($src);
                    break;

                case IMAGETYPE_JPEG:
                    $src_img = imagecreatefromjpeg($src);
                    break;

                case IMAGETYPE_PNG:
                    $src_img = imagecreatefrompng($src);
                    break;
            }

            if (!$src_img) {
                $this->msg = "无法读取图片文件（错误代码：10）";
                return;
            }

            $size = getimagesize($src);
            $size_w = $size[0]; // natural width
            $size_h = $size[1]; // natural height

            $src_img_w = $size_w;
            $src_img_h = $size_h;

            $degrees = $data->rotate;

            // Rotate the source image
            if (is_numeric($degrees) && $degrees != 0) {
                // PHP's degrees is opposite to CSS's degrees
                $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

                imagedestroy($src_img);
                $src_img = $new_img;

                $deg = abs($degrees) % 180;
                $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

                $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
                $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

                // Fix rotated image miss 1px issue when degrees < 0
                $src_img_w -= 1;
                $src_img_h -= 1;
            }

            $tmp_img_w = $data->width;
            $tmp_img_h = $data->height;
            $dst_img_w = 220;
            $dst_img_h = 220;

            $src_x = $data ->x;
            $src_y = $data ->y;

            if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                $src_x = $src_w = $dst_x = $dst_w = 0;
            } else if ($src_x <= 0) {
                $dst_x = -$src_x;
                $src_x = 0;
                $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
            } else if ($src_x <= $src_img_w) {
                $dst_x = 0;
                $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
            }

            if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                $src_y = $src_h = $dst_y = $dst_h = 0;
            } else if ($src_y <= 0) {
                $dst_y = -$src_y;
                $src_y = 0;
                $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
            } else if ($src_y <= $src_img_h) {
                $dst_y = 0;
                $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
            }

            // Scale to destination position and size
            $ratio = $tmp_img_w / $dst_img_w;
            $dst_x /= $ratio;
            $dst_y /= $ratio;
            $dst_w /= $ratio;
            $dst_h /= $ratio;

            $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

            // Add transparent background to destination image
            imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagesavealpha($dst_img, true);

            $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

            if ($result) {
                if (!imagepng($dst_img, $dst)) {
                    $this->msg = "无法保存裁剪后的文件（错误代码：9）";
                }
            } else {
                $this->msg = "裁剪文件失败（错误代码：8）";
            }

            imagedestroy($src_img);
            imagedestroy($dst_img);
        }
    }

    private function codeToMessage($code) {
        $errors = array(
          UPLOAD_ERR_INI_SIZE =>'上传文件的体积超过了预定的最大值（错误代码：1）',
          UPLOAD_ERR_FORM_SIZE =>'上传图片的体积超过了预定的最大值（错误代码：2）',
          UPLOAD_ERR_PARTIAL =>'文件上传不完整（错误代码：3）',
          UPLOAD_ERR_NO_FILE =>'图片未被上传（错误代码：4）',
          UPLOAD_ERR_NO_TMP_DIR =>'缺少临时文件夹（错误代码：5）',
          UPLOAD_ERR_CANT_WRITE =>'保存图片到磁盘失败（错误代码：6）',
          UPLOAD_ERR_EXTENSION =>'图片上传被一个扩展阻止（错误代码：7）',
        );

        if (array_key_exists($code, $errors)) {
            return $errors[$code];
        }

        return 'Unknown upload error';
    }

    public function getResult() {
        global $wgUser, $wgHuijiPrefix;
        if ($this->isUserAvatar) {
            $uid = $wgUser->getId();
            $avatar = new wAvatar( $uid, 'l' );
        } else {
            $avatar = new wSiteAvatar( $wgHuijiPrefix, 'l' );      
        }
        return $avatar->getAvatarUrlPath();
    }

    public function getMsg() {
        return $this->msg;
    }
    public function createThumbnail( $imageSrc, $imageInfo, $imgDest, $thumbWidth ) {
        global $wgUseImageMagick, $wgImageMagickConvertCommand, $wgUseOss;

        if ( $wgUseImageMagick ) { // ImageMagick is enabled
            list( $origWidth, $origHeight, $typeCode ) = $imageInfo;

            if ( $origWidth < $thumbWidth ) {
                $thumbWidth = $origWidth;
            }
            $thumbHeight = ( $thumbWidth * $origHeight / $origWidth );
            $border = ' -bordercolor white  -border  0x';
            if ( $thumbHeight < $thumbWidth ) {
                $border = ' -bordercolor white  -border  0x' . ( ( $thumbWidth - $thumbHeight ) / 2 );
            }
            if ( $typeCode == 2 ) {
                $dest = $this->avatarUploadDirectory . '/' . $imgDest . '.jpg';
                $ext = 'jpg';
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0   -quality 100 ' . $border . ' ' .
                    $imageSrc . ' ' . $dest
                );
            }
            if ( $typeCode == 1 ) {
                $dest = $this->avatarUploadDirectory . '/' . $imgDest . '.gif';
                $ext = 'gif';
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0 ' . $imageSrc . ' ' . $border . ' ' .
                    $dest
                );
            }
            if ( $typeCode == 3 ) {
                $dest =  $this->avatarUploadDirectory . '/' . $imgDest . '.png';
                $ext = 'png';
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0 ' . $imageSrc . ' ' .
                    $dest
                );
            }
            // Copy the thumb, put it in OSS.
            if ($wgUseOss){
                $bucket = wAvatar::AVATAR_BUCKET;
                $object = $imgDest . '.' . $ext;
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
            list( $origWidth, $origHeight, $typeCode ) = getimagesize( $imageSrc );

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
                $bucket = wAvatar::AVATAR_BUCKET;
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
                copy(
                    $imageSrc,
                    $this->avatarUploadDirectory . '/' . $imgDest . '.' . $ext
                );                
            }

        }
    }
}

// $crop = new CropAvatar(
//   isset($_POST['avatar_src']) ? $_POST['avatar_src'] : null,
//   isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null,
//   isset($_FILES['avatar_file']) ? $_FILES['avatar_file'] : null
// );

// $response = array(
//   'state'  => 200,
//   'message' => $crop -> getMsg(),
//   'result' => $crop -> getResult()
// );

// echo json_encode($response);
