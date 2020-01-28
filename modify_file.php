<?php

/* 
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 */

require_once '../../config.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

if(LANGUAGE_LOADED) {
	require WB_PATH.'/modules/'.$dlgmodname.'/languages/EN.php';
	if (file_exists (WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php';
	}
}

// STEP 0:	initialize some variables
$page_id    = ( ( isset($_GET['page_id'])    && is_numeric($_GET['page_id']) )    ? (int) $_GET['page_id']    : NULL );
$section_id = ( ( isset($_GET['section_id']) && is_numeric($_GET['section_id']) ) ? (int) $_GET['section_id'] : NULL );
$file_id    = ( ( isset($_GET['file_id'])    && is_numeric($_GET['file_id']) )    ? (int) $_GET['file_id']    : NULL );
if(!$page_id || !$section_id || !$file_id) {
	header("Location: ".ADMIN_URL."/pages/index.php");
}

// Include WB admin wrapper script
require_once WB_PATH.'/modules/admin.php';

// other includes
require_once WB_PATH.'/framework/functions.php';
require_once realpath( dirname(__FILE__).'/functions.php' );

// get file data
$query_content = $database->query(sprintf(
    "SELECT * FROM `%s%s_files` WHERE `file_id`='%s' AND `page_id`='%s'",
    TABLE_PREFIX,$tablename,$file_id,$page_id
));
$fetch_content = $query_content->fetchRow();

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array_merge(
    array(
        'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
        'files'       => array(),
    ),
    $fetch_content
);

if (!defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR=="none" || !file_exists(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
	function show_wysiwyg_editor($name,$id,$content,$width,$height) {
		return '<textarea name="'.$name.'" id="'.$id.'" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
	}
} else {
	$id_list = array("content");
	require_once WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php';
}

// list of existing files
$wbpath      = str_replace('\\','/',WB_PATH);
$basepath    = str_replace('\\','/',WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname);
$folder_list = directory_list($basepath);
array_push($folder_list,$basepath);
sort($folder_list);

foreach($folder_list as $name) {
    // note: the file_list() method returns the full path
	$file_list = file_list($name,array('index.php'));
	sort($file_list);
	foreach($file_list as $filename) {
		$thumb_count = substr_count($filename, '/thumbs/');
		if($thumb_count==0) {
            $data['files'][] = array(
                WB_URL.str_replace($wbpath,'',$filename),
                str_replace($basepath.'/','',$filename)
            );
		}
		$thumb_count="";
	}
}

// list of existing groups
list ( $data['groups'], $data['gr2name'] ) = dlg_getgroups($section_id,false);

$data = (object) $data;

include dirname(__FILE__).'/templates/default/backend/modify_file.phtml';

// Print admin footer
$admin->print_footer();