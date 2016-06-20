<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

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
$group_id   = ( ( isset($_GET['group_id'])   && is_numeric($_GET['group_id']) )   ? (int) $_GET['group_id']   : NULL );
if(!$page_id || !$section_id || !$group_id) {
	header("Location: ".ADMIN_URL."/pages/index.php");
}

// Include WB admin wrapper script
require_once WB_PATH.'/modules/admin.php';

$query_content = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_groups` WHERE `group_id` = '$group_id' AND `page_id` = '$page_id'");
$fetch_content = $query_content->fetchRow(MYSQL_ASSOC);

// initialize template data
$dir  = pathinfo(dirname(__FILE__),PATHINFO_BASENAME);
$data = array_merge(
    array(
        'FTAN'        => (method_exists($admin,'getFTAN') ? $admin->getFTAN() : ''),
    ),
    $fetch_content
);

$data = (object) $data;

include dirname(__FILE__).'/templates/default/backend/modify_group.phtml';

// Print admin footer
$admin->print_footer();
