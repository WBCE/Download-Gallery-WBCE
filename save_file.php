<?php
/* 
 * CMS module: Download Gallery 3
 * Copyright and more information see file info.php
 */

require_once '../../config.php';
require_once WB_PATH.'/framework/functions.php';
require_once WB_PATH.'/modules/admin.php';
require_once realpath( dirname(__FILE__).'/functions.php' );

$update_when_modified = true; 

// Get id
$file_id = '';
if (
       !isset($_POST['file_id'])
    || !is_numeric($_POST['file_id'])
    || !isset($_POST['active'])
    || !is_numeric($_POST['active'])
) {
	header("Location: ".ADMIN_URL."/pages/index.php");
} else {
	$file_id = (int) $_POST['file_id'];
	$active  = $admin->get_post('active');
}

// STEP 0:	initialize some variables
$filename   = '';
$fname      = '';
$fileext    = '';
$file_link  = '';
$dlgmodname = str_replace(str_replace('\\','/',WB_PATH).'/modules/','',str_replace('\\','/',dirname(__FILE__)));
$tablename  = 'mod_'.$dlgmodname;

// get settings
$settings = dlg_getsettings($section_id);

// Validate all fields
$title        = $admin->add_slashes(htmlspecialchars($admin->get_post('title')));
$description  = $admin->add_slashes($admin->get_post('description'));
$old_link     = $admin->add_slashes($admin->get_post('link'));
$existingfile = $admin->add_slashes($admin->get_post('existingfile'));
$group        = $admin->add_slashes($admin->get_post('group'));
$overwrite    =	$admin->add_slashes($admin->get_post('overwrite'));
$remotelink   =	$admin->add_slashes($admin->get_post('remote_link'));
$released     =	$admin->add_slashes($admin->get_post('released'));
if(($existingfile=="") && ($remotelink=="")) $existingfile = $old_link;

// Get page link URL
$query_page = $database->query(sprintf(
    "SELECT `level`,`link` FROM `%spages` WHERE `page_id`=%d",
    TABLE_PREFIX, intval($page_id)
));
if(!$query_page->numRows())
{
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id);
}
$page       = $query_page->fetchRow();
$page_level = $page['level'];
$page_link  = $page['link'];

// Check if the user uploaded a file or wants to delete one
if (
       isset($_FILES['file'])
    && isset($_FILES['file']['error']) // error is always there, 0 on success
    && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE
    && ($existingfile == '')
) {
    // check for upload error
    if($_FILES['file']['error'] != 0) {
        $admin->print_error(
            dlg_get_upload_error($_FILES['file']['error']),
            WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id
        );
    }

	// Get real filename and set new filename
    if(!defined('CAT_PATH') && !function_exists('media_filename')) {
        require_once __DIR__.'/../../framework/functions.php';
    }
    $filename     = media_filename(trim($_FILES['file']['name']));
	$path_parts   = pathinfo($filename);
	$fileext      = $path_parts['extension'];
	$new_filename = WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/'.$filename;

	// create link
	$file_link = WB_URL.MEDIA_DIRECTORY.'/'.$dlgmodname.'/'.$filename;
	if($overwrite=="yes" || !file_exists($new_filename)) {
		move_uploaded_file($_FILES['file']['tmp_name'], $new_filename);
		change_mode($new_filename);
	}
    else {
        $admin->print_error($MESSAGE['MEDIA_FILE_EXISTS'],WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
    }

    $size = filesize($new_filename);

	// update file information in the database
	$database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `extension` = '$fileext', `filename` = '$filename', `link` = '$file_link', `size` = '$size' WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
    if($database->is_error()) {
        $admin->print_error($TEXT['DATABASE'].' '.$TEXT['ERROR'].': '.$database->get_error(),WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id);
    }
}

// Check if the user provided a remote link
if ((isset($_POST['remote_link'])) && ($_POST['remote_link'] != '') && ($filename=='')) {
	// Get real filename and set new filename
	$remotelink = trim($remotelink);
    $filename = pathinfo($remotelink,PATHINFO_BASENAME);
	$fileext = pathinfo($remotelink,PATHINFO_EXTENSION);
	$new_filename = $filename;
    $size = dlg_curl_get_file_size($remotelink);

    if(substr_count($fileext,'?')>0) {
        list($fileext,$rest) = explode('?',$fileext,2);
    }

	// update file information in the database
	$database->query(sprintf(
        "UPDATE `%s%s_files` SET `link`='%s', `extension`='%s',`filename`='%s',`size`='%s' WHERE `file_id`=%d AND `page_id`=%d",
        TABLE_PREFIX, $tablename, $remotelink, $fileext, $remotelink, $size, intval($file_id), intval($page_id)
    ));
    if($database->is_error()) {
        $admin->print_error($TEXT['DATABASE'].' '.$TEXT['ERROR'].': '.$database->get_error(),WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id);
    }
}

// delete file
if (
       (isset($_POST['delete_file'])  && $_POST['delete_file']  == 'yes')
    || (isset($_POST['delete_file2']) && $_POST['delete_file2'] == 'yes')
) {
	// query the database for the file extension
	$query_content = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_files` WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
	$fetch_content = $query_content->fetchRow();
	$fname         = $fetch_content['filename'];
	$ext           = $fetch_content['extension'];
	// Try unlinking file
	$query_duplicates = $database->query("SELECT * FROM `".TABLE_PREFIX.$tablename."_files` WHERE `filename` = '$fname' AND `extension`='$ext' AND `page_id` = '$page_id'");
	$dups = $query_duplicates->numRows();
	// only delete the file if there is 1 database entry (not used on multiple sections)
	if(($dups==1) && (isset($_POST['delete_file']))) {
		$file = WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/'.$fname;
		if(file_exists($file) && is_writable($file)) {
			unlink($file);
		}
	}
	// reset variables so the fields are cleared so new file can be placed
	$file_link = "";
	$filename = "";
	$active = 0;
}

if (isset($_POST['delete_counter']) && $_POST['delete_counter'] == 'yes') {
	$qs = $database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `dlcount` = 0 WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
}	

if ($released <> '') {
	$rdate = strtotime($released);
	$qs = $database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `released` = $rdate WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
}	

// existing ./media file
if(trim($existingfile!='')) {
    $size = 0;
    $file_link  = $existingfile;
    $path_parts = pathinfo($file_link);
    $fileext    = $path_parts['extension'];
    $filename   = $path_parts['basename'];
	
	if ($settings['use_dir']=='Y') {
		// get subdir
		$subdir = preg_replace('~.*/'.$dlgmodname.'/~i','',$file_link);
		if  ($remotelink == '') {		
			$size = filesize(WB_PATH.MEDIA_DIRECTORY.'/'.$dlgmodname.'/'.$subdir);
		}
	} else {
		
		if  ($remotelink == '') {				
			$size = filesize(str_replace(WB_URL,WB_PATH,$file_link));
		}
	}
    if(
           (isset($_POST['delete_file']) && $_POST['delete_file'] != '')
        or (isset($_POST['delete_file2']) AND $_POST['delete_file2'] != '')
    ) {
		$filename  = "";
		$file_link = "";
		$fileext   = "";
	}
	$database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `extension`='$fileext',`filename`='$filename',`link`='$file_link',`size`='$size' WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");
}

if(!strlen($title)) {
    if($admin->get_post('use_filename')) {
        $title = $filename;
    } else {
        $admin->print_error($MESSAGE['GENERIC_FILL_IN_ALL'],WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
    }
}

// Update other file data
$database->query("UPDATE `".TABLE_PREFIX.$tablename."_files` SET `title`='$title', `group_id` = '$group', `description` = '$description', `active` = '$active', `modified_when` = '".time()."', `modified_by` = '".$admin->get_user_id()."' WHERE `file_id` = '$file_id' AND `page_id` = '$page_id'");

if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
} else {
	if(
           (isset($_POST['delete_file']) && $_POST['delete_file'] != '')
        || (isset($_POST['delete_file2']) AND $_POST['delete_file2'] != '')
    ) {
		$admin->print_success($TEXT['SUCCESS'], WB_URL.'/modules/'.$dlgmodname.'/modify_file.php?page_id='.$page_id.'&section_id='.$section_id.'&file_id='.$file_id);
	} else {
		$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
	}
}

// Print admin footer
$admin->print_footer();
