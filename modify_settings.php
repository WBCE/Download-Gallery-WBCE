<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

require_once '../../config.php';
require_once WB_PATH.'/modules/admin.php';
require_once realpath( dirname(__FILE__).'/info.php' );
require_once realpath( dirname(__FILE__).'/functions.php' );

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/'.$dlgmodname.'/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php';
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
$query_content = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_settings` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
$data['settings'] = $query_content->fetchRow();

// List Extension types
$query_fileext 	= $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_file_ext` WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'");
if($query_fileext->numRows())
{
    while($row = $query_fileext->fetchRow())
    {
        $data['fileext'][] = $row;
    }
}

$data['tpldirs'] = dlg_gettpldirs();

$data = (object) $data;

include dirname(__FILE__).'/templates/default/backend/modify_settings.phtml';

$admin->print_footer();