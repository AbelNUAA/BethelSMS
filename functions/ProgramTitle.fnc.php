<?php
/**
 * Program Title function
 *
 * @package BETHELSIS
 * @subpackage functions
 */

/**
 * Get Program Title
 *
 * @example DrawHeader( ProgramTitle() );
 *
 * @global array  $_BETHEL Sets $_BETHEL['HeaderIcon'], uses $_BETHEL['Menu']
 *
 * @param  string $modname  Specify program/modname (optional).
 *
 * @return string Program title or 'BETHELSIS' if not found
 */
function ProgramTitle( $modname = '' )
{
	global $_BETHEL;

	if ( empty( $modname ) )
	{
		$modname = $_REQUEST['modname'];
	}

	if ( $modname === 'misc/Portal.php' )
	{
		$_BETHEL['HeaderIcon'] = 'misc';

		return ParseMLField( Config( 'TITLE' ) );
	}

	// Generate Menu if needed.
	if ( ! isset( $_BETHEL['Menu'] ) )
	{
		require_once 'Menu.php';
	}

	// Loop modules.
	foreach ( (array) $_BETHEL['Menu'] as $modcat => $programs )
	{
		// Modname not in current Module, continue.
		if ( ! isset( $programs[ $modname ] ) )
		{
			continue;
		}

		// Set Header Icon.
		if ( ! isset( $_BETHEL['HeaderIcon'] )
			|| $_BETHEL['HeaderIcon'] !== false )
		{
			// Get right icon for Teacher Programs.
			if ( mb_substr( $modname, 0, 25 ) === 'Users/TeacherPrograms.php' )
			{
				$_BETHEL['HeaderIcon'] = mb_substr( $modname, 34, mb_strpos( $modname, '/', 34 ) - 34 );
			}
			else
				$_BETHEL['HeaderIcon'] = $modcat;
		}

		return $programs[ $modname ];
	}

	// Program not found!
	return 'BETHELSIS';
}
