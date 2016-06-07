<?php
/*
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: index.php'));

$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

//Remove all table entries and drop some tables.
$database->query("DELETE FROM `".TABLE_PREFIX."search` WHERE `name` = 'module' AND `value` = '$dlgmodname'");
$database->query("DELETE FROM `".TABLE_PREFIX."search` WHERE `extra` = '$dlgmodname'");
$database->query("DROP TABLE `".TABLE_PREFIX.$tablename."_files`");
$database->query("DROP TABLE `".TABLE_PREFIX.$tablename."_settings`");
$database->query("DROP TABLE `".TABLE_PREFIX.$tablename."_groups`");
$database->query("DROP TABLE `".TABLE_PREFIX.$tablename."_file_ext`");

//Remove the download_gallery folder in the media dir
require_once WB_PATH.'/framework/functions.php';
rm_full_dir(WB_PATH . MEDIA_DIRECTORY . '/' . $dlgmodname);
