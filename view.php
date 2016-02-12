<?php

/*
 * CMS module: Download Gallery 2
 * Copyright and more information see file info.php
 */

require_once WB_PATH.'/modules/download_gallery/functions.php';
require_once WB_PATH.'/modules/download_gallery/info.php'; // allows to print the module version

if(LANGUAGE_LOADED) {
	if(!file_exists(WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php')) {
		require WB_PATH.'/modules/download_gallery/languages/EN.php';
	} else {
		require WB_PATH.'/modules/download_gallery/languages/'.LANGUAGE.'.php';
	}
}

// build current link, should be secure against xss:
if ((isset($_SERVER['HTTPS'])) and ($_SERVER['HTTPS']=="on")) {
    $selflink = "https://";
} else {
    $selflink = "http://";
}
$selflink .= $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array(
    'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    'self_link'   => $selflink,
    'mod_version' => $module_version,
    'groups'      => array(), // list of groups
    'gr2name'     => array(), // maps group_id to group_name
    'ext2img'     => dlg_ext2img($section_id), // maps file extension to icon
    'filecount'   => dlg_getfilescount($section_id),
    'currcount'   => 0,
);

// get settings
$data['settings'] = dlg_getsettings($section_id);

// get groups
list ( $data['groups'], $data['gr2name'] ) = dlg_getgroups($section_id);

// Get user's username, display name, email, and id - needed for insertion into download info
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

// fix settings
if(!count($data['settings'])) {
    // initialize vars that will be used later, but may not be set if the
    // DB statement fails or has no data
    include_once(WB_PATH.'/modules/download_gallery/functions.php');
    $data['settings']['ordering'] = 0;
	$data['settings']['extordering'] = 0;
	$data['settings']['files_per_page'] = 0;
	$data['settings']['file_size_decimals'] = 0;
	$data['settings']['file_size_roundup'] = 0;
	$data['settings']['ordering'] = 0;
	$data['settings']['userupload'] = 0;
}

if($data['settings']['ordering'] == '2' or $data['settings']['ordering'] == '3') {
	$orderby = TABLE_PREFIX."mod_download_gallery_files.title";
} else {
	$orderby = TABLE_PREFIX."mod_download_gallery_files.position";
}

if ($data['settings']['ordering'] == '0' or $data['settings']['ordering'] == '2') {
	$ordering = "ASC";
} else {
	$ordering = "DESC";
}

// begin checking user input

// Get total number of available download entries


// limit results?
if($data['settings']['files_per_page'] != 0) {
	$limit_sql = " LIMIT $position, ".$data['settings']['files_per_page'];
} else {
	$limit_sql = "";
}

                // Query for search results
                $searchfor = '';
                $dlsearch = '';
                if ($searchfor!="") {
                    $dlsearch = " AND (`".TABLE_PREFIX."mod_download_gallery_files`.`title`       LIKE '%$searchfor%' "
                			  . "  OR  `".TABLE_PREFIX."mod_download_gallery_files`.`description` LIKE '%$searchfor%')";
                    $query_filter_num = $database->query("SELECT `file_id` FROM `".TABLE_PREFIX."mod_download_gallery_files` WHERE `section_id` = '$section_id' AND `active` = '1' AND `title` != '' " .$dlsearch);
                    $search_num = $query_filter_num->numRows();
                }

// Query files (for this page)
$query_files = $database->query(
    "SELECT
        `file_id`, `".TABLE_PREFIX."mod_download_gallery_files`.`title`,`link`,`description`,`modified_by`,
	   `modified_when`,`filename`,`extension`,`dlcount`,`size`,`released`,`".TABLE_PREFIX."mod_download_gallery_files`.`group_id`
	FROM `".TABLE_PREFIX."mod_download_gallery_files`
	LEFT JOIN `".TABLE_PREFIX."mod_download_gallery_groups`
	    ON (`".TABLE_PREFIX."mod_download_gallery_files`.`group_id` = `".TABLE_PREFIX."mod_download_gallery_groups`.`group_id`)
	WHERE `".TABLE_PREFIX."mod_download_gallery_files`.`section_id` = '$section_id'
	    AND `".TABLE_PREFIX."mod_download_gallery_files`.`active` = '1'
	    AND `".TABLE_PREFIX."mod_download_gallery_files`.`title` != ''
        AND `".TABLE_PREFIX."mod_download_gallery_groups`.`active`=1
	    ".$dlsearch."
	ORDER BY `".TABLE_PREFIX."mod_download_gallery_groups`.`position`, $orderby $ordering " . $limit_sql
);
if($query_files->numRows() > 0) {
	$data['num_files'] = $query_files->numRows();
    while($file = $query_files->fetchRow(MYSQL_ASSOC)) {
        $dldescription=$file['description'];
		$wb->preprocess($dldescription);
        $file['description'] = $dldescription;
        $file['released']    = ($file['released'] > 0) ? (date('d.m.Y', $file['released'])) : '';
        $data['files'][] = $file;
    }
}

$data['currcount'] = ( $data['settings']['files_per_page'] != 0 )
    ? $data['settings']['files_per_page']
    : $data['num_files'];

$DGTEXT['SHOWING'] = str_replace(
    array('{{count}}','{{sum}}'),
    array($data['currcount'],$data['filecount']),
    $DGTEXT['SHOWING']
);

$data = (object) $data;
include dirname(__FILE__).'/templates/frontend/'.$data->settings['tpldir'].'/view.phtml';
