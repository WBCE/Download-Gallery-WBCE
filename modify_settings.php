<?php

/**
 * CMS module: Download Gallery WBCE
 * Copyright and more information see file info.php
 **/

require '../../config.php';
require WB_PATH.'/modules/admin.php';
require realpath( dirname(__FILE__).'/info.php' );

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/download_gallery/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php';
	}
}

// STEP 0:	initialize some variables
$page_id    = (int) $page_id;
$section_id = (int) $section_id;

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array(
    'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    'heading'     => "$module_name - ".$TEXT['PAGE']." $page_id",
    'modify_link' => ADMIN_URL.'/pages/modify.php',
    'self_link'   => WB_URL.'/modules/'.$dir,
    'mod_version' => $module_version,
    'settings'    => array(),
    'fileext'     => array(),
    'tpldirs'     => array(),
);

// Get General Settings
$query_content = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_settings` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
$data['settings'] = $query_content->fetchRow(MYSQL_ASSOC);

// List Extension types
$query_fileext 	= $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_file_ext` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
if($query_fileext->numRows())
{
    while($row = $query_fileext->fetchRow(MYSQL_ASSOC))
    {
        $data['fileext'][] = $row;
    }
}

// get template subdirs
$tplbase = realpath(dirname(__FILE__).'/templates/frontend');
$dh      = opendir($tplbase);
while (false !== ($filename = readdir($dh))) {
    if(substr($filename,0,1)!='.' && is_dir($tplbase.'/'.$filename)) {
        $data['tpldirs'][] = $filename;
    }
}

$data = (object) $data;

include dirname(__FILE__).'/templates/modify_settings.phtml';

$admin->print_footer();