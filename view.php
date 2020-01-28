<?php

/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 */

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

require_once WB_PATH.'/modules/'.$dlgmodname.'/functions.php';
require_once WB_PATH.'/modules/'.$dlgmodname.'/info.php'; // allows to print the module version

if(LANGUAGE_LOADED) {
	if(!file_exists(WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/EN.php';
	} else {
		require WB_PATH.'/modules/'.$dlgmodname.'/languages/'.LANGUAGE.'.php';
	}
}

// handle download
if(isset($_REQUEST['dl']))
{
    header_remove();
    // remove any output buffers before sending the file
    while (ob_get_level() > 0)
        ob_end_clean();
    // send file and exit
    dlg_download($_REQUEST['dl'],$section_id);
    exit;
}

// WBCE fix for root page
global $page_id;
if(empty($page_id) && !empty(PAGE_ID)) {
    $page_id = PAGE_ID;
}

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array(
    'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    'self_link'   => $_SERVER['SCRIPT_NAME'],
    'mod_version' => $module_version,
    'groups'      => array(), // list of groups
    'gr2name'     => array(), // maps group_id to group_name
    'ext2img'     => dlg_ext2img($section_id), // maps file extension to icon
    'filecount'   => dlg_getfilescount($section_id),
    'num_files'   => 0,
    'page'        => 1,
    'prev'        => NULL,
    'next'        => NULL,
);

// get groups
list ( $data['groups'], $data['gr2name'] ) = dlg_getgroups($section_id);

// Get user's username, display name, email, and id - needed for download info
$users = array();
$query_users = $database->query("SELECT `user_id`,`username`,`display_name`,`email` FROM `".TABLE_PREFIX."users`");
if($query_users->numRows() > 0) {
	while($user = $query_users->fetchRow()) {
		// Insert user info into users array
		$user_id = $user['user_id'];
		$users[$user_id]['username'] = $user['username'];
		$users[$user_id]['display_name'] = $user['display_name'];
		$users[$user_id]['email'] = $user['email'];
	}
}

// quote search string
$data['searchfor'] = null;
if(isset($_POST['dlg_search_'.$section_id])) {
    $data['searchfor'] = htmlentities($_POST['dlg_search_'.$section_id], ENT_QUOTES, 'UTF-8');
}

// get files
dlg_getfiles($section_id,$data,true);

// get total number of files (for paging)
$data['filecount'] = count($data['files']);

// get settings
$data['settings'] = dlg_getsettings($section_id);

// pagination
if($data['settings']['files_per_page'] > 0 && $data['filecount'] > 0 ) {
    // total pages
    $total_number_of_pages = 1;
    // current page
    $current_page = 1;
    if(isset($_GET['page']) && intval($_GET['page']>0)) {
        $current_page = intval($_GET['page']);
    }
    $offset = 0;
    // list of page links
    $data['nav_pages'] = array();
    $offset = ($current_page-1) * $data['settings']['files_per_page'];
    $total_number_of_pages = ceil($data['filecount']/$data['settings']['files_per_page']);
    for($p=1;$p<=$total_number_of_pages;$p++) {
        $data['nav_pages'][$p] = $p;
        $data['prev'] = (($current_page>1)                    ? $current_page - 1 : NULL );
        $data['next'] = ($current_page<$total_number_of_pages ? $current_page + 1 : NULL );
        $data['page'] = $current_page;
    }
    // extract files from array
    $data['files'] = array_slice($data['files'],$offset,$data['settings']['files_per_page']);
    
    // at least one file in this group
    foreach($data['files'] as $i => $file) {
        if(!isset($data['files_in_this_group'][$file['group_id']])) {
            $data['files_in_this_group'][$file['group_id']] = 0;
        }
        $data['files_in_this_group'][$file['group_id']]++;
    }

    $DGTEXT['SHOWING'] = str_replace(
        array('{{section}}','{{count}}','{{sum}}'),
        array($section_id,count($data['files']),$data['filecount']),
        $DGTEXT['SHOWING']
    );

} else {
    foreach($data['grfiles'] as $id => $num) {
        $data['files_in_this_group'][$id] = $num;
    }
}

$data['num_files'] = count($data['files']);

$data = (object) $data;

include dirname(__FILE__).'/templates/default/frontend/'.$data->settings['tpldir'].'/view.phtml';
