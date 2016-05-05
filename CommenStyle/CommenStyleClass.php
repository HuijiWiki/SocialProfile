<?php
/**
 * This Class manages Commen style
 */
class CommenStyle{
	function __construct() {

	}
	//insert file
	public static function insertSiteCss( $fileName ){
		$dbw = wfGetDB( DB_MASTER );
		$dbw -> upsert(
				'commen_css',
				array(
					'css_filename' => $fileName
				),
				array(
					'css_filename' => $fileName
				),
				array(
					'css_filename' => $fileName
				),
				 __METHOD__
			);
		if ( $dbw->insertId() ) {
			return $dbw->insertId();
		}
	}

	//check is exist css file
	public static function checkCssFile( $fileName ){
		$result = false;
		if ( $fileName != null ) {
			$dbr = wfGetDB( DB_SLAVE );
			$dbr -> select(
					'commen_css',
					array( 'status' ),
					array(
						'css_filename' => $fileName
					),
					__METHOD__
				);
			foreach ($dbr as $key => $value) {
				if ( $value->status > 0 ) {
					$result = true;
				}
			}
		}
		return $result;
	}

	//open css style
	public static function openCssStyle( $fileName ){
		$result = false;
		if ( $fileName != null ) {
			$res = $dbw -> update(
						'commen_css',
						array(
							'status' => 2
						),
						array(
							'css_filename' => $fileName
						),
						__METHOD__
					);
			if ( $res ) {
				$req = $dbw -> update(
							'commen_css',
							array(
								'status' => 1
							),
							array(
								'css_filename' => $fileName
							),
							__METHOD__
						);
				if ( $req ) {
					$result = true;
				}
			}
		}
		return $result;
	}

	//get current css 
	public static function getCurrentCssStyle(){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr -> select(
				'commen_css',
				'css_filename',
				array(
					'status' => 1
				),
				__METHOD__,
				array(
					'LIMIT' => 1
				)
			);
		$cssFilename = '';
		if ($res) {
			foreach ($res as $value) {
				$cssFilename = $value->css_filename;
			}
		}
		return $cssFilename;
	}
}