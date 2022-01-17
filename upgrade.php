<?php
/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
defined('WB_PATH') or die(header('Location: index.php'));

require_once WB_PATH.'/framework/functions.php';

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// Remove old search entries
$database->query(sprintf(
    "DELETE FROM `%ssearch` WHERE `name` = 'module' AND `value` = '%s'",
        TABLE_PREFIX, $dlgmodname
));
$database->query(sprintf(
    "DELETE FROM `%ssearch` WHERE `extra` = '%s'",
        TABLE_PREFIX, $dlgmodname
));

// Add new search entries
// Module query info
$field_info = array();
$field_info['page_id'] = 'page_id';
$field_info['title'] = 'page_title';
$field_info['link'] = 'link';
$field_info['description'] = 'description';
$field_info['modified_when'] = 'modified_when';
$field_info['modified_by'] = 'modified_by';
$field_info = serialize($field_info);

$database->query(sprintf(
    "INSERT INTO `%ssearch` (`name`,`value`,`extra`) VALUES ('module', '%s', '%s')",
        TABLE_PREFIX, $dlgmodname, $field_info
));

// Search query start
$query_start_code = "SELECT [TP]pages.page_id
, [TP]pages.page_title
, [TP]pages.link
, [TP]pages.description
, [TP]pages.modified_when
, [TP]pages.modified_by 
FROM [TP]".$tablename."_files,[TP]".$tablename."_groups, [TP]pages
WHERE 
";
$database->query(sprintf(
    "INSERT INTO `%ssearch` (`name`,`value`,`extra`) VALUES ('query_start', '%s', '%s')",
        TABLE_PREFIX, $query_start_code, $dlgmodname
));

// Search query body
$query_body_code = " [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.title [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.description [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_groups.page_id AND [TP]".$tablename."_groups.title [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\' OR
					 [TP]pages.page_id = [TP]".$tablename."_files.page_id AND [TP]".$tablename."_files.filename [O] \'[W][STRING][W]\' AND [TP]pages.searching = \'1\'
				   ";	
$database->query(sprintf(
    "INSERT INTO `%ssearch` (`name`,`value`,`extra`) VALUES ('query_body', '%s', '%s')",
        TABLE_PREFIX, $query_body_code, $dlgmodname
));

// Search query end
$query_end_code = "";	
$database->query(sprintf(
    "INSERT INTO `%ssearch` (`name`,`value`,`extra`) VALUES ('query_end', '%s', '%s')",
        TABLE_PREFIX, $query_end_code, $dlgmodname
));

// Insert blank row (there needs to be at least on row for the search to work)
$query_0=$database->query(sprintf(
    "SELECT * FROM `%s%s_files` WHERE `section_id`='0' AND `page_id`='0'",
        TABLE_PREFIX, $tablename
));
if($query_0->numRows() == 0) {
    $database->query(sprintf(
        "INSERT INTO `%s%s_files` (`section_id`,`page_id`) VALUES ('0', '0')",
            TABLE_PREFIX, $tablename
    ));
}
	
$query_0=$database->query(sprintf(
    "SELECT * FROM `%s%s_settings` WHERE `section_id`='0' AND `page_id`='0'",
        TABLE_PREFIX, $tablename
));
if($query_0->numRows() == 0) {
    $database->query(sprintf(
        "INSERT INTO `%s%s_settings` (`section_id`,`page_id`) VALUES ('0', '0')",
            TABLE_PREFIX, $tablename
    ));
}

$query_0=$database->query(sprintf(
    "SELECT * FROM `%s%s_groups` WHERE `section_id`='0' AND `page_id`='0'",
        TABLE_PREFIX, $tablename
));
if($query_0->numRows() == 0) {
    $database->query(sprintf(
        "INSERT INTO `%s%s_groups` (`section_id`,`page_id`) VALUES ('0', '0')",
            TABLE_PREFIX, $tablename
    ));
}

try {
    $database->query(sprintf("ALTER TABLE `%s%s_settings` ADD COLUMN `position` INT(11) NOT NULL DEFAULT 0 AFTER `ordering`",TABLE_PREFIX, $tablename));
} catch (\Exception $ex) {}
try {
    $database->query(sprintf("ALTER TABLE `%s%s_settings` DROP COLUMN `extposition`",TABLE_PREFIX, $tablename));
} catch (\Exception $ex) {}
try {
    $database->query(sprintf("ALTER TABLE `%s%s_settings` ADD COLUMN `use_dir` ENUM('Y','N') NOT NULL DEFAULT 'Y' AFTER `ordering`", TABLE_PREFIX, $tablename));
} catch (\Exception $ex) {
    echo $ex->getMessage();
}
try {
    $database->query(sprintf("ALTER TABLE `%s%s_settings` ADD COLUMN `offer_download` ENUM('Y','N') NOT NULL DEFAULT 'Y' AFTER `use_dir`", TABLE_PREFIX, $tablename));
} catch (\Exception $ex) {}

// update .htaccess file in /media/download_gallery folder 
include_once WB_PATH.'/modules/'.$dlgmodname.'/functions.php';
make_dl_dir();
