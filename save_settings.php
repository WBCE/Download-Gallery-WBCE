<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

require_once '../../config.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require WB_PATH.'/modules/admin.php';
include_once WB_PATH.'/modules/'.$dlgmodname.'/functions.php';

// This code removes any php tags and adds slashes
$friendly = array('&lt;', '&gt;', '?php');
$raw      = array('<', '>', '');

// STEP 1: Retrieve settings from POST vars
if (isset($_POST['file_size_decimals']) && is_numeric($_POST['file_size_decimals'])) {
    $file_size_decimals = $_POST['file_size_decimals'];
} else {
    $file_size_decimals = '0';
}
if (isset($_POST['files_per_page']) && is_numeric($_POST['files_per_page'])) {
    $files_per_page = $_POST['files_per_page'];
} else {
    $files_per_page = '0';
}
if (isset($_POST['file_size_round']) && is_numeric($_POST['file_size_round'])) {
    $file_size_roundup = $_POST['file_size_round'];
} else {
    $file_size_roundup = '0';
}
if (isset($_POST['search_filter']) && in_array($_POST['search_filter'],array('Y','N'))) {
    $search_filter = $_POST['search_filter'];
} else {
    $search_filter = 'N';
}
if (isset($_POST['ordering']) && is_numeric($_POST['ordering'])) {
    $ordering = $_POST['ordering'];
} else {
    $ordering = 0;
}
$tpldirs = dlg_gettpldirs();
if(isset($_POST['template_dir']) && in_array($_POST['template_dir'],$tpldirs)) {
    $tpldir = $_POST['template_dir'];
} else {
    $tpldir = 'tableview';
}

$use_default_css = 'Y';
if(!isset($_POST['use_default_css'])) {
    $use_default_css = 'N';
}


// Update settings
/*['ordering']
0 - ascending position
1 - descending position
2 - ascending title
3 - descending title
*/

$query = "UPDATE `".TABLE_PREFIX.$tablename."_settings` SET
	`files_per_page` = '$files_per_page',
	`file_size_roundup` = '$file_size_roundup',
	`file_size_decimals` = '$file_size_decimals',
	`ordering` = '$ordering',
	`search_filter` = '$search_filter',
    `tpldir` = '$tpldir',
    `tplcss` = '$use_default_css'
	WHERE `section_id` = '$section_id' AND `page_id` = '$page_id'";
$database->query($query);

// handle database error
if($database->is_error()) {
	$admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();
