<?php
/**
 * User edit / usage rights check functions
 * Determined by profiles / user permissions
 *
 * @see Users > User Profiles & User Permissions
 *
 * @package BETHELSIS
 * @subpackage functions
 */

/**
 * Can Edit program check
 *
 * Always perform `AllowEdit()` check:
 * before displaying fields / options to edit data
 * AND before saving or updating data
 *
 * @global array   $_BETHEL Sets $_BETHEL['allow_edit']
 *
 * @param  string $modname Specify program name (optional) defaults to current program.
 *
 * @return boolean false if not allowed, true if allowed
 */
function AllowEdit( $modname = false )
{
	global $_BETHEL;

	if ( User( 'PROFILE' ) !== 'admin' )
	{
		return ! empty( $_BETHEL['allow_edit'] );
	}

	if ( ! $modname
		&& isset( $_BETHEL['allow_edit'] ) )
	{
		return $_BETHEL['allow_edit'];
	}

	if ( ! $modname )
	{
		if ( ! isset( $_REQUEST['modname'] ) )
		{
			return false;
		}

		$modname = $_REQUEST['modname'];
	}

	// Student / User Info tabs.
	if ( ( $modname === 'Students/Student.php'
		|| $modname === 'Users/User.php' )
		&& isset( $_REQUEST['category_id'] ) )
	{
		$modname = $modname . '&category_id=' . $_REQUEST['category_id'];
	}

	// Get CAN_EDIT programs from database
	if ( ! isset( $_BETHEL['AllowEdit'] ) )
	{
		$from_where_sql = User( 'PROFILE_ID' ) ?
			"FROM PROFILE_EXCEPTIONS
			WHERE PROFILE_ID='" . User( 'PROFILE_ID' ) . "'" :
			"FROM STAFF_EXCEPTIONS
			WHERE USER_ID='" . User( 'STAFF_ID' ) . "'";

		$_BETHEL['AllowEdit'] = DBGet( "SELECT MODNAME " .
			$from_where_sql .
			" AND CAN_EDIT='Y'", array(), array( 'MODNAME' ) );
	}

	return isset( $_BETHEL['AllowEdit'][ $modname ] );
}


/**
 * Can Use program check
 *
 * @global array   $_BETHEL Sets $_BETHEL['AllowUse']
 *
 * @param  string $modname Specify program name (optional) defaults to current program.
 *
 * @return boolean false if not allowed, true if allowed
 */
function AllowUse( $modname = false )
{
	global $_BETHEL;

	if ( ! $modname )
	{
		$modname = $_REQUEST['modname'];
	}

	// Student / User Info tabs.
	if ( ( $modname === 'Students/Student.php'
			|| $modname ==='Users/User.php' )
		&& isset( $_REQUEST['category_id'] ) )
	{
		$modname = $modname . '&category_id=' . $_REQUEST['category_id'];
	}

	// Get CAN_USE programs from database.
	if ( ! isset( $_BETHEL['AllowUse'] ) )
	{
		$from_where_sql = User( 'PROFILE_ID' ) != '' ? // Beware, '0' is student!
			"FROM PROFILE_EXCEPTIONS
			WHERE PROFILE_ID='" . User( 'PROFILE_ID' ) . "'" :
			"FROM STAFF_EXCEPTIONS
			WHERE USER_ID='" . User( 'STAFF_ID' ) . "'";

		$_BETHEL['AllowUse'] = DBGet( "SELECT MODNAME " . $from_where_sql .
			" AND CAN_USE='Y'", array(), array( 'MODNAME' ) );
	}

	return isset( $_BETHEL['AllowUse'][ $modname ] );
}
