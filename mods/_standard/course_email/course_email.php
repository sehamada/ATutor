<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2010                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
authenticate(AT_PRIV_COURSE_EMAIL);

$course = intval($_GET['course']);

if ($course == 0) {
	$course = $_SESSION['course_id'];
}

if (isset($_POST['cancel'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: '.$_base_href.'tools/index.php');
	exit;
} else if (isset($_POST['submit'])) {
	$missing_fields = array();

	$_POST['to_enrolled']   = trim($_POST['to_enrolled']);
	$_POST['to_unenrolled'] = trim($_POST['to_unenrolled']);
	$_POST['to_alumni']     = trim($_POST['to_alumni']);
	$_POST['to_assistants'] = trim($_POST['to_assistants']);

	$_POST['subject'] = trim($_POST['subject']);
	$_POST['body'] = trim($_POST['body']);

	if ( ($_POST['to_enrolled']   == '') &&
		 ($_POST['to_unenrolled'] == '') &&
		 ($_POST['to_alumni']     == '') &&
		 ($_POST['to_assistants'] == '') &&
		 ($_POST['groups']        == '')
		) {
			$missing_fields[] = _AT('to');
	}

	if ($_POST['subject'] == '') {
		$missing_fields[] = _AT('subject');
	}

	if ($_POST['body'] == '') {
		$missing_fields[] = _AT('body');
	}

	if ($missing_fields) {
		$missing_fields = implode(', ', $missing_fields);
		$msg->addError(array('EMPTY_FIELDS', $missing_fields));
	}

	if (!$msg->containsErrors()) {
		$email_sql	= "SELECT email, first_name, last_name, login, password  FROM ".TABLE_PREFIX."course_enrollment C INNER JOIN ".TABLE_PREFIX."members M USING (member_id) WHERE C.course_id=$course AND (";
		
		if ($_POST['to_unenrolled']) {
			// choose all unenrolled
			$email_sql .= "C.approved='n' OR ";
		}
		
		if ($_POST['to_alumni']) {
			// choose all alumni
			$email_sql 	.= "C.approved='a' OR ";
		}

		if ($_POST['to_assistants']){
			// choose all assistants
			$email_sql	.= "C.privileges<>0 OR ";
		}

		if ($_POST['groups']) {
			// specific groups
			$groups = implode(',', $_POST['groups']);

			$group_members = array();
			$sql = "SELECT member_id FROM %sgroups_members WHERE group_id IN (%s)";
			$rows_members = queryDB($sql, array(TABLE_PREFIX, $groups));
			
			foreach($rows_members as $row){
				$group_members[] = $row['member_id'];
			}
			$group_members = implode(',', $group_members);
			if (!empty($group_members)){
				$email_sql .= "M.member_id IN ($group_members) OR ";
			} else {
				$email_sql .= "M.member_id IN (-1) OR ";
			}
		} else if ($_POST['to_enrolled']) {
			// includes instructor
			$email_sql 	.= "(C.approved='y' AND C.privileges=0) OR ";
		}

		$email_sql = substr_replace($email_sql, '', -4). ')'; // strip off the last ' OR '
		$rows_emails = queryDB($email_sql, array());
		require(AT_INCLUDE_PATH . 'classes/phpmailer/atutormailer.class.php');

		// generate email recipients
		$mail_list = array();
		foreach($rows_emails as $row){
			$mail_list[]=$row['email'];
			$fname_list[$row['email']] = $row['first_name'];
			$lname_list[$row['email']] = $row['last_name'];
			$login_list[$row['email']] = $row['login'];
		}

		// Get instructor ID.
		$sql = "SELECT member_id FROM %scourses WHERE course_id=%d";
		$row = queryDB($sql, array(TABLE_PREFIX, $course), TRUE);
		$instructor_id = $row['member_id'];

		// Add instructor to email list if he is not the one sending email.
		if ($instructor_id != $_SESSION['member_id']) {
			$sql = "SELECT email FROM %smembers WHERE member_id=$instructor_id";
			$row = queryDB($sql, array(TABLE_PREFIX, $instructor_id), TRUE);

			$mail_list[]= $row['email'];
		}

		// Get the sender.		
		$sql = "SELECT email, first_name, last_name,login,password FROM %smembers WHERE member_id=%d";
		$row = queryDB($sql, array(TABLE_PREFIX, $_SESSION['member_id']), TRUE);
		$mail_list[] = $row['email'];
    $recipient_list = "";
	// Prep the mailer.
		// set some user specific variables for the body (
		// Added by Thomas Taennier (ipool)
		foreach ($mail_list as $recip) {
      $recipient_list.= "<li>".$recip."</li>";
			$subject = $_POST['subject'];
			$body = $_POST['body'];
			$mail = new ATutorMailer;
			$mail->From     = $row['email'];
			$mail->FromName = $row['first_name'] . ' ' . $row['last_name'];
			$subject = str_replace('{AT_FNAME}', $fname_list[$recip],$subject);
			$subject = str_replace('{AT_LNAME}', $lname_list[$recip],$subject);
			$body = str_replace('{AT_FNAME}', $fname_list[$recip],$body);
			$body = str_replace('{AT_LNAME}', $lname_list[$recip],$body);
			$body = str_replace('{AT_EMAIL}', $recip,$body);
			$body = str_replace('{AT_USER}', $login_list[$recip],$body);

			$mail->Subject = $subject;
			$mail->AddAddress($recip);
			$mail->Body    = $body;
			if(!$mail->Send()) {
		   		$msg->addError('SENDING_ERROR');
				header('Location: '.$_SERVER['PHP_SELF']);
		  		exit;
			}
			unset($mail);
		}

    $list_feedback = array('COURSE_EMAIL_RECIPIENT_LIST', $recipient_list);
    $msg->addFeedback($list_feedback);
		header('Location: '.$_base_href.'tools/index.php');
		exit;
	}
}

require(AT_INCLUDE_PATH.'header.inc.php');

$sql	= "SELECT COUNT(*) AS cnt FROM %scourse_enrollment C, %smembers M WHERE C.course_id=%d AND C.member_id=M.member_id AND M.member_id<>%d ORDER BY C.approved, M.login";
$row = queryDB($sql, array(TABLE_PREFIX, TABLE_PREFIX, $course, $_SESSION['member_id']), TRUE);

if ($row['cnt'] == 0) {
	$msg->printInfos('NO_STUDENTS');
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

//fetch groups names, same as first out of loop query. 
$sql = "SELECT type_id, title FROM %sgroups_types WHERE course_id=%d ORDER BY title";
$rows_types = queryDB($sql, array(TABLE_PREFIX, $_SESSION['course_id']));


$group_type_rows = array(); 

foreach($rows_types as $row){
    $group_type_rows[$row['type_id']] = $row; 
    //save the first SQL result set ($row) into $group_type_rows. Use type_id as the key to map each row
    $sql = "SELECT group_id, title FROM %sgroups WHERE type_id=%d ORDER BY title";
    $rows_groups = queryDB($sql, array(TABLE_PREFIX, $row['type_id']));
    
    //second loop adds a child array to our $group_type_rows created above to store the data
    foreach($rows_groups as $group_rows){
        $group_type_rows[$row['type_id']]['group_type_row'][] = $group_rows;
    }

}

$savant->assign('group_type_rows', $group_type_rows);
$savant->display('instructor/course_email/course_email.tmpl.php');
?>

<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>
