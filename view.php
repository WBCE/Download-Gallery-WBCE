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

// fix for root page
global $page_id;
if(empty($page_id)) { $page_id = PAGE_ID; }

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

// get settings
$data['settings'] = dlg_getsettings($section_id);

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

// fix settings
if(!count($data['settings'])) {
    // initialize vars that will be used later, but may not be set if the
    // DB statement fails or has no data
    include_once(WB_PATH.'/modules/'.$dlgmodname.'/functions.php');
    $data['settings']['ordering'] = 0;
	$data['settings']['extordering'] = 0;
	$data['settings']['files_per_page'] = 0;
	$data['settings']['file_size_decimals'] = 0;
	$data['settings']['file_size_roundup'] = 0;
	$data['settings']['ordering'] = 0;
}

if($data['settings']['ordering'] == '2' or $data['settings']['ordering'] == '3') {
	$orderby = '`t1`.`title`';
} else {
	$orderby = '`t1`.`position`';
}

if ($data['settings']['ordering'] == '0' or $data['settings']['ordering'] == '2') {
	$ordering = "ASC";
} else {
	$ordering = "DESC";
}

// begin checking user input
$offset = 0;
$page   = 1;
if(isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page   = $_GET['page'];
    $offset = $data['settings']['files_per_page'] * $_GET['page'] - 1;
}

// search
$dlsearch = $data['searchfor'] = NULL;
if(isset($_POST['dlg_search_'.$section_id])) {
    // Query for search results
    $data['searchfor'] = htmlentities($_POST['dlg_search_'.$section_id], ENT_QUOTES, 'UTF-8');
    $dlsearch = " AND (`t1`.`title` LIKE '%%".$data['searchfor']."%%' OR `t1`.`description` LIKE '%%".$data['searchfor']."%%')";
}

// limit results? no limit for search!
if($data['settings']['files_per_page'] != 0 && $dlsearch == '') {
	$limit_sql = " LIMIT $offset, ".$data['settings']['files_per_page'];
} else {
	$limit_sql = "";
}

// Query files (for this page)
$query =
"SELECT
    `file_id`,
    `t1`.`group_id`,
    `t1`.`title`,
    `link`,
    `description`,
    `modified_by`,
    `modified_when`,
    `filename`,
    `extension`,
    `dlcount`,
    `size`,
    `released`
FROM `%s%s_files` AS t1
LEFT OUTER JOIN `%s%s_groups` AS t2
ON `t1`.`group_id` = `t2`.`group_id`
WHERE `t1`.`section_id` = '$section_id'
    AND `t1`.`active` = '1'
    AND `t1`.`title` != ''
    AND ( `t1`.`group_id`=0 OR `t2`.`active`=1 ) ".$dlsearch."
ORDER BY `t2`.`position`, $orderby $ordering ".$limit_sql;

$query_files = $database->query(sprintf($query,TABLE_PREFIX,$tablename,TABLE_PREFIX,$tablename));

if(is_object($query_files) && $query_files->numRows() > 0) {
	$data['num_files'] = $query_files->numRows();
    while($file = $query_files->fetchRow()) {
        $dldescription=$file['description'];
		$wb->preprocess($dldescription);
        $file['description'] = $dldescription;
        $data['files'][] = $file;
    }
}

$DGTEXT['SHOWING'] = str_replace(
    array('{{section}}','{{count}}','{{sum}}'),
    array($section_id,$data['num_files'],$data['filecount']),
    $DGTEXT['SHOWING']
);

// pagination
$number_of_pages = 1;
$data['nav_pages'] = array();
if($data['filecount'] > 0 && $data['num_files'] > 0 && $data['filecount'] > $data['num_files']  && $dlsearch == '' ) {
    $number_of_pages = ceil( $data['filecount'] / $data['num_files'] );
    for($i=1;$i<=$number_of_pages;$i++) {
        $data['nav_pages'][$i] = $i;
    }
    $data['prev'] = ( $page > 1                ? $page - 1 : NULL );
    $data['next'] = ( $page < $number_of_pages ? $page + 1 : NULL );
    $data['page'] = $page;
}

$data = (object) $data;
include dirname(__FILE__).'/templates/default/frontend/'.$data->settings['tpldir'].'/view.phtml';
