<?php

/**
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 **/

require realpath(dirname(__FILE__).'/../../config.php');

// check permissions
require_once WB_PATH.'/framework/class.admin.php';
$admin = new admin('Modules', 'module_view', false, false);
$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

if (!($admin->is_authenticated() && $admin->get_permission($dlgmodname, 'module')))
{
    header('Location: ../../index.php');
}

require_once WB_PATH.'/framework/class.order.php';

// if there's no item_id, it should be a group
if(!isset($_POST['item_id'])) {
    if(!isset($_POST['group_id'])) {

    }
    else {
        $group_id = ( is_numeric($_POST['group_id']) ? $_POST['group_id'] : NULL );
        $prev_id  = ( (isset($_POST['prev_id']) && is_numeric($_POST['prev_id'])) ? $_POST['prev_id']  : NULL ); // new position
        $o        = new order(TABLE_PREFIX.$tablename.'_groups', 'position', 'group_id', 'section_id');
        if($group_id) {
            if($prev_id) {
            $pos = $database->get_one('SELECT `position` FROM `'.TABLE_PREFIX.$tablename."_groups` WHERE `group_id` = '".$prev_id."'");
            } else {
                $pos = 0;
            }
            $database->query("UPDATE `".TABLE_PREFIX.$tablename."_groups` SET `position` = '".($pos++)."' WHERE `group_id` = '".$group_id."'");
            $section_id = $database->get_one('SELECT `section_id` FROM `'.TABLE_PREFIX.$tablename."_groups` WHERE `group_id` = '".$group_id."'");
            $o->clean($section_id);
        }
    }
}
else {
    $item_id  = ( is_numeric($_POST['item_id'])  ? $_POST['item_id']  : NULL );
    $group_id = ( is_numeric($_POST['group_id']) ? $_POST['group_id'] : NULL ); // new group
    $prev_id  = ( (isset($_POST['prev_id']) && is_numeric($_POST['prev_id'])) ? $_POST['prev_id'] : NULL ); // new position
    $o        = new order(TABLE_PREFIX.$tablename.'_files', 'position', 'file_id', 'group_id');
    if($item_id) {
        // group changed
        if($group_id) {
            $database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `group_id` = '".$group_id."' WHERE `file_id` = '".$item_id."'");
        }
        // get prev item id
        if($prev_id) {
        $pos = $database->get_one('SELECT `position` FROM `'.TABLE_PREFIX.$tablename."_files` WHERE `file_id` = '".$prev_id."'");
        } else {
            $pos = 0;
        }
        $database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `position` = '".($pos++)."' WHERE `file_id` = '".$item_id."'");
        $o->clean($group_id);
    }
}