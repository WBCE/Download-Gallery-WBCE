<?php

/**
 * CMS module: Download Gallery WBCE
 * Copyright and more information see file info.php
 **/

if (!defined('WB_PATH')) die(header('Location: index.php'));

require realpath( dirname(__FILE__).'/info.php' );
require realpath( dirname(__FILE__).'/functions.php' );

require_once WB_PATH .'/framework/module.functions.php';
require_once realpath( dirname(__FILE__).'/info.php' );

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/download_gallery/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php';
	}
}

// STEP 0:	initialize some variables
$page_id    = (int) $page_id;
$section_id = (int) $section_id;

// delete empty records
$database->query("DELETE FROM `".TABLE_PREFIX."mod_download_gallery_files`  WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `title`=''");
$database->query("DELETE FROM `".TABLE_PREFIX."mod_download_gallery_groups` WHERE `page_id` = '$page_id' AND `section_id` = '$section_id' AND `title`=''");

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
);

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
        $database->query("UPDATE `".TABLE_PREFIX."mod_download_gallery_".$table."` SET `active` = '$status' WHERE `".$field."` ='$id'");
    }
}

// get groups
list ( $data['groups'], $data['gr2name'] ) = dlg_getgroups($section_id,false);

// get files
$query_files = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_download_gallery_files` WHERE `section_id` = '$section_id'");
if($query_files->numRows() > 0) {
	$data['num_files'] = $query_files->numRows();
    while($file = $query_files->fetchRow(MYSQL_ASSOC)) {
        $data['files'][] = $file;
        if(!isset($data['grfiles'][$file['group_id']])) $data['grfiles'][$file['group_id']] = 0;
        if(!isset($data['dlpergroup'][$file['group_id']])) $data['dlpergroup'][$file['group_id']] = 0;
        $data['grfiles'][$file['group_id']]++;
        $data['dlpergroup'][$file['group_id']] += $file['dlcount'];
    }
}

// sort files by group and position
$data['files'] = dlg_array_orderby($data['files'], 'group_id', SORT_ASC, 'position', SORT_ASC);

$data = (object) $data;

require dirname(__FILE__).'/templates/default/backend/modify.phtml';

if(!defined('CAT_PATH'))
    $admin->print_footer();