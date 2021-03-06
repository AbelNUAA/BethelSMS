<?php
/**
 * Custom module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 *
 * @package BETHELSIS
 * @subpackage modules
 */

// Custom Students programs
if ( $BETHELModules['Students'] )
{
	$menu['Students']['admin'] += array(
		3 => _( 'Utilities' ),
		'Custom/MyReport.php' => _( 'My Report' ),
		'Custom/CreateParents.php' => _( 'Create Parent Users' ),
		// @since 6.6 Add Registration program for Administrators.
		'Custom/Registration.php' => _( 'Registration' ),
		'Custom/RemoveAccess.php' => _( 'Remove Access' ),
	);

	$exceptions['Students'] += array(
		'Custom/CreateParents.php' => true,
	);

	$menu['Students']['parent'] += array(
		'Custom/Registration.php' => _( 'Registration' ),
	);
}

// Custom Users programs
if ( $BETHELModules['Users'] )
{
	$menu['Users']['admin'] += array(
		3 => _( 'Utilities' ),
		'Custom/NotifyParents.php' => _( 'Notify Parents' ),
	);

	$exceptions['Users'] += array(
		'Custom/NotifyParents.php' => true,
	);
}

// Custom Attendance programs
if ( $BETHELModules['Attendance'] )
{
	// Place Attendance Summary program before Utilities separator.
	$utilities_pos = array_search( 2, array_keys( $menu['Attendance']['admin'] ) );

	$menu['Attendance']['admin'] = array_merge(
	    array_slice( $menu['Attendance']['admin'], 0, $utilities_pos ),
	    array( 'Custom/AttendanceSummary.php' => _( 'Attendance Summary' ) ),
	    array_slice( $menu['Attendance']['admin'], $utilities_pos )
	);
}
