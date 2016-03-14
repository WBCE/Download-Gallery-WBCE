<?php

/**
 * CMS module: Download Gallery WBCE
 * Copyright and more information see file info.php
 **/

require_once '../../config.php';
require realpath( dirname(__FILE__).'/info.php' );
require realpath( dirname(__FILE__).'/functions.php' );

// check if this file was invoked by the expected module file
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (
       $referer
    && (
           strpos($referer, WB_URL . '/modules/download_gallery/modify_settings.php') === false
        && strpos($referer, WB_URL . '/modules/download_gallery/modify_extensions.php') === false
       )
) {
	die(header('Location: ../../index.php'));
}

// include the admin wrapper script
$update_when_modified = true;
require WB_PATH.'/modules/admin.php';
$admin = new admin('Pages', '', false, false);

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/download_gallery/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php';
	}
}

require_once WB_PATH.'/framework/functions.php';

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array(
    'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    'heading'     => "$module_name - ".$TEXT['PAGE']." $page_id",
    'modify_link' => ADMIN_URL.'/pages/modify.php',
    'self_link'   => WB_URL.'/modules/'.$dir,
    'mod_version' => $module_version,
);

if (isset($_REQUEST['fileext_id'])) {
	$fileext_id = (int) $_REQUEST['fileext_id'];
}
else {
    $admin->print_error($TEXT['ERROR']);
}

if(isset($_POST['file_ext'])) {
    $checkOK	= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789$, ";
	$checkStr	= $_POST['file_ext'];
	$allValid	= true;
	//Loop through string and see if illegal chars are used
	for ($i = 0;  $i < strlen($checkStr);  $i++) {
		$ch = substr($checkStr, $i, 1);
		if (strpos($checkOK, $ch)===FALSE) {
			$allValid = false;
            $data['infotext'] = $DGTEXT['FILE_TYPE_EXT_ERROR'];
			break;
		}
	}
    if($allValid) {
        //Remove the spaces
		$checkStr = str_replace(" ","", $checkStr);
		//Update the database
		$database->query("UPDATE `".TABLE_PREFIX."mod_download_gallery_file_ext` "
			. " SET `extensions` = '$checkStr' "
			. " WHERE `fileext_id` = '$fileext_id' AND `page_id` = '$page_id'");
		$data['infotext'] = $DGTEXT['FILE_STORED'];
    }
}

// load current file extensions data
$data['extdetails'] = dlg_getfileext($fileext_id,$section_id);

$data = (object) $data;

require dirname(__FILE__).'/templates/default/backend/modify_extensions.phtml';