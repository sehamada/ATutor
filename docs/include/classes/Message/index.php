<?php
define('AT_INCLUDE_PATH', '../../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');

require('Message.class.php');

echo '<br />';

echo '<html><body>';
global $savant;
$msg =& new Message($savant, $_base_href);

$msg->addError('FORUM_NOT_FOUND');
$msg->addWarning('SAVE_YOUR_WORK');
$msg->addInfo('NO_SEARCH_RESULTS');
$msg->addFeedback('FORUM_ADDED');

debug($_SESSION);

$msg->printErrors(); ?><br /><?php
$msg->printWarnings(); ?><br /><?php
$msg->printInfos(); ?><br /><?php
$msg->printFeedbacks(); ?><br /><?php

debug($_SESSION);

$feedback=array('FORUM_ADDED', 'ac_access_groups');
$msg->addFeedback($feedback);

$feedback2=array('FORUM_ADDED', 'about_atutor_help_text');
$msg->addFeedback($feedback2);

debug($_SESSION);
$msg->printFeedbacks();

debug($_SESSION);

echo '</body></html>';
?>