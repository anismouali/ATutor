<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2008                                      */
/* Written by Greg Gay, Joel Kronenberg & Chris Ridpath         */
/* Inclusive Design Institute                                   */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$
define('AT_INCLUDE_PATH', '../../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
authenticate(AT_PRIV_READING_LIST);

// initial values for form
$id = intval($_REQUEST['id']);
$title = "";
$author = "";
$publisher = ""; 
$date = ""; 
$comments = "";
$isbn = "";
$page_return = $_GET['page_return'];

// check if user has submitted form
if (isset($_POST['cancel'])) {
	$msg->addFeedback('CANCELLED');

	header('Location: display_resources.php');
	exit;
} else if (isset($_POST['submit'])) {
	$missing_fields = array();

	if (trim($_POST['title']) == '') {
		$missing_fields[] = _AT('title');
	}
	if (trim($_POST['author']) == '') {
		$missing_fields[] = _AT('author');
	}

	if ($missing_fields) {
		$missing_fields = implode(', ', $missing_fields);
		$msg->addError(array('EMPTY_FIELDS', $missing_fields));
	}


	if (!$msg->containsErrors()) {
		$_POST['title'] = $addslashes(validate_length($_POST['title'], 255));
		$_POST['author'] = $addslashes(validate_length($_POST['author'], 150));
		$_POST['publisher'] = $addslashes(validate_length($_POST['publisher'], 150));
		$_POST['date']      = $addslashes($_POST['date']);
		$_POST['comments'] = $addslashes(validate_length($_POST['comments'], 255));
		$_POST['isbn']      = $addslashes($_POST['isbn']);
		
		if ($id == 0){ // creating a new book resource

			$sql = "INSERT INTO %sexternal_resources VALUES (NULL, %d,
				%d, 
				'$_POST[title]', 
				'$_POST[author]', 
				'$_POST[publisher]', 
				'$_POST[date]', 
				'$_POST[comments]',
				'$_POST[isbn]',
				'')";
			$result = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id'], RL_TYPE_BOOK));
			
			// index to new book resource
			$id_new = at_insert_id();
			
			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		} else { // modifying an existing book resource

			$sql = "UPDATE %sexternal_resources SET title='%s', author='%s', publisher='%s', date='%s', comments='%s', id='%s' WHERE resource_id=%d AND course_id=%d";
			$result = queryDB($sql, array(TABLE_PREFIX, $_POST['title'], $_POST['author'], $_POST['publisher'], $_POST['date'], $_POST['comments'], $_POST['isbn'], $id, $_SESSION['course_id']));

			// index to book resource
			$id_new = $id;

			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		}

		if (trim($_POST['page_return']) != ''){
			header('Location: '. $_POST['page_return']. '?existingbook='. $id_new);
		}
		else {
			header('Location: index_instructor.php');
		}
		exit;
	} else { // submission contained an error, update form values for redisplay
		$title       = $stripslashes($_POST['title']);
		$author      = $stripslashes($_POST['author']);
		$publisher   = $stripslashes($_POST['publisher']);
		$date        = $stripslashes($_POST['date']);
		$comments    = $stripslashes($_POST['comments']);
		$isbn        = $stripslashes($_POST['isbn']);
		$page_return = $stripslashes($_POST['page_return']);
	}
}

// is user modifying an existing book resource?
if ($id && !isset($_POST['submit'])){
	// yes, get resource from database

	$sql = "SELECT * FROM %sexternal_resources WHERE course_id=%d AND resource_id=%d";
	$row = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id'], $id), TRUE);
	
	if(count($row) > 0){
		$title     = AT_print($row['title'], 'input.text');
		$author    = AT_print($row['author'], 'input.text');
		$publisher = AT_print($row['publisher'], 'input.text');
		$date      = AT_print($row['date'], 'input.text'); 
		$comments  = AT_print($row['comments'], 'input.text');
		$isbn      = AT_print($row['id'], 'input.text');
	}
	// change title of page to 'edit book resource' (default is 'add book resource')
	$_pages['mods/_standard/reading_list/add_resource_book.php']['title_var'] = 'rl_edit_resource_book';
} else if ($id) {
	$_pages['mods/_standard/reading_list/add_resource_book.php']['title_var'] = 'rl_edit_resource_book';
}

$onload = 'document.form.name.focus();';

require(AT_INCLUDE_PATH.'header.inc.php');
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
<input type="hidden" name="id" value="<?php echo $id ?>" />
<input type="hidden" name="page_return" value="<?php echo $page_return ?>" />

<div class="input-form">	
	<fieldset class="group_form"><legend class="group_form"><?php echo _AT('rl_add_resource_book'); ?></legend>
	<div class="row">
		<span class="required" title="<?php echo _AT('required_field'); ?>">*</span><label for="title"><?php  echo _AT('title'); ?></label><br />
		<input type="text" name="title" size="35" id="title" value="<?php echo $title; ?>" />
	</div>

	<div class="row">
		<span class="required" title="<?php echo _AT('required_field'); ?>">*</span><label for="author"><?php  echo _AT('author'); ?></label><br />
		<input type="text" name="author" size="25" id="author" value="<?php echo $author; ?>" />
	</div>

	<div class="row">
		<label for="date"><?php  echo _AT('rl_year_written'); ?></label><br />
		<input type="text" name="date" size="6" id="date" value="<?php echo $date; ?>" />
	</div>

	<div class="row">
		<label for="publisher"><?php  echo _AT('rl_publisher'); ?></label><br />
		<input type="text" name="publisher" size="20" id="publisher" value="<?php echo $publisher; ?>" />
	</div>

	<div class="row">
		<label for="isbn"><?php  echo _AT('rl_isbn_number'); ?></label><br />
		<input type="text" name="isbn" size="15" id="isbn" value="<?php echo $isbn; ?>" />
	</div>

	<div class="row">
		<label for="comments"><?php  echo _AT('comment'); ?></label><br />
		<textarea name="comments" cols="30" rows="2" id="comments"><?php echo $comments; ?></textarea>
	</div>

	<div class="row buttons">
		<input type="submit" name="submit" value="<?php echo _AT('save'); ?>" accesskey="s" />
		<input type="submit" name="cancel" value="<?php echo _AT('cancel'); ?>" />
	</div>
	</fieldset>
</div>
</form>

<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>