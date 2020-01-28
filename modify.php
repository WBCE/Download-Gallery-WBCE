<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

if (!defined('WB_PATH')) die(header('Location: index.php'));

require_once realpath( dirname(__FILE__).'/info.php' );
require_once realpath( dirname(__FILE__).'/functions.php' );

require_once WB_PATH .'/framework/module.functions.php';
require_once realpath( dirname(__FILE__).'/info.php' );

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/'.$dlgmodname.'/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php';
	}
}

// STEP 0:	initialize some variables
$page_id    = (int) $page_id;
$section_id = (int) $section_id;

// delete empty records
$database->query("DELETE FROM `".TABLE_PREFIX.$tablename."_files`  WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `title`=''");
$database->query("DELETE FROM `".TABLE_PREFIX.$tablename."_groups` WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `title`=''");

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array(
    'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    'heading'     => "$module_name - ".$TEXT['PAGE']." $page_id",
    'modify_link' => ADMIN_URL.'/pages/modify.php',
    'self_link'   => WB_URL.'/modules/'.$dir,
    'mod_version' => $module_version,
    'groups'      => array(),
    'files'       => array(),
    'gr2name'     => array(),
    'ext2img'     => dlg_ext2img($section_id),
    'grfiles'     => array(),
    'dlpergroup'  => array(),
    'dlsum'       => dlg_getdlsum($section_id),
);

// Get General Settings
$data['settings'] = dlg_getsettings($section_id);

// actions
// toggle active state
if(isset($_GET['status']) && is_numeric($_GET['status'])) {
    $status = (($_GET['status']==1)?0:1);
    // sanitize input
    if(isset($_GET['file_id']) && is_numeric($_GET['file_id'])) {
        $table = 'files';
        $field = 'file_id';
        $id    = $_GET['file_id'];
    }
    elseif(isset($_GET['group_id']) && is_numeric($_GET['group_id'])) {
        $table = 'groups';
        $field = 'group_id';
        $id    = $_GET['group_id'];
    }
    else {
        // do nothing (invalid data)
    }
    if(isset($table) && isset($field)) {
        $database->query(sprintf(
            "UPDATE `%s%s_%s` SET `active` = '%s' WHERE `%s`='%s'",
            TABLE_PREFIX,$tablename,$table,$status,$field,$id
        ));
    }
}

// get groups
list ( $data['groups'], $data['gr2name'] ) = dlg_getgroups($section_id,false,$data['settings']['ordering']);
dlg_getfiles($section_id,$data,false);

$data = (object) $data;

require dirname(__FILE__).'/templates/default/backend/modify.phtml';
