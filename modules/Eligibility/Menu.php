<?php
/**
 * Eligibility module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package BETHELSIS
 * @subpackage modules
 */

$menu['Eligibility']['admin'] = array(
	'title' => _( 'Activities' ),
	'default' => 'Eligibility/Student.php',
	'Eligibility/Student.php' => _( 'Student Screen' ),
	'Eligibility/AddActivity.php' => _( 'Add Activity' ),
	1 => _( 'Reports' ),
	'Eligibility/StudentList.php' => _( 'Student List' ),
	'Eligibility/TeacherCompletion.php' => _( 'Teacher Completion' ),
	2 => _( 'Setup' ),
	'Eligibility/Activities.php' => _( 'Activities' ),
	'Eligibility/EntryTimes.php' => _( 'Entry Times' )
);

$menu['Eligibility']['teacher'] = array(
	'title' => _( 'Activities' ),
	'default' => 'Eligibility/EnterEligibility.php',
	'Eligibility/EnterEligibility.php' => _( 'Enter Eligibility' )
);

$menu['Eligibility']['parent'] = array(
	'title' => _( 'Activities' ),
	'default' => 'Eligibility/Student.php',
	'Eligibility/Student.php' => _( 'Student Screen' ),
	'Eligibility/StudentList.php' => _( 'Student List' )
);

if ( $BETHELModules['Users'] )
{
	$menu['Users']['admin'] += array(
		'Users/TeacherPrograms.php&include=Eligibility/EnterEligibility.php' => _( 'Enter Eligibility' )
	);
}

$exceptions['Eligibility'] = array(
	'Eligibility/AddActivity.php' => true
);

if ( $BETHELModules['Users'] )
{
	$exceptions['Users'] += array(
		'Users/TeacherPrograms.php&include=Eligibility/EnterEligibility.php' => true
	);
}
