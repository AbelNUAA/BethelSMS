<?php
/**
 * Discipline module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 * 
 * @package BETHELSIS
 * @subpackage modules
 */

$menu['Discipline']['admin'] = array(
	'title' => _( 'Discipline' ),
	'default' => 'Discipline/Referrals.php',
	'Discipline/MakeReferral.php' => _( 'Add Referral' ),
	'Discipline/Referrals.php' => _( 'Referrals' ),
	1 => _( 'Reports' ),
	'Discipline/CategoryBreakdown.php' => _( 'Category Breakdown' ),
	'Discipline/CategoryBreakdownTime.php' => _( 'Category Breakdown over Time' ),
	'Discipline/StudentFieldBreakdown.php' => _( 'Breakdown by Student Field' ),
	'Discipline/ReferralLog.php' => _( 'Discipline Log' ),
	2 => _( 'Setup' ),
	'Discipline/DisciplineForm.php' => _( 'Referral Form' ),
);

$menu['Discipline']['teacher'] = array(
	'title' => _( 'Discipline' ),
	'default' => 'Discipline/Referrals.php',
	'Discipline/MakeReferral.php' => _( 'Add Referral' ),
	'Discipline/Referrals.php' => _( 'Referrals' ),
);

$menu['Discipline']['parent'] = array(
	'title' => _( 'Discipline' ),
	'default' => 'Discipline/Referrals.php',
	'Discipline/Referrals.php' => _( 'Referrals' ),
);

$exceptions['Discipline'] = array(
);
