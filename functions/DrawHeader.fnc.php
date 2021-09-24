<?php
/**
 * Draw Header function
 *
 * @package BETHELSIS
 * @subpackage functions
 */

/**
 * Draw Header
 *
 * The first call draws the Primary Header
 * Next calls draw Secondary Headers
 * unset( $_BETHEL['DrawHeader'] ) to reset
 *
 * @example DrawHeader( ProgramTitle() );
 *
 * @global array  $_BETHEL Sets $_BETHEL['DrawHeader']
 *
 * @param  string $left     Left part of the Header.
 * @param  string $right    Right part of the Header (optional).
 * @param  string $center   Center part of the Header (optional).
 *
 * @return void   outputs Header HTML
 */
function DrawHeader( $left, $right = '', $center = '' )
{
	global $_BETHEL,
		$BETHELCoreModules;

	// Primary Header.
	if ( ! isset( $_BETHEL['DrawHeader'] )
		|| ! $_BETHEL['DrawHeader'] )
	{
		$_BETHEL['DrawHeader'] = 'header1';
	}

	echo '<table class="header"><tr class="st">';

	if ( $left )
	{
		// Add H2 + Module icon to Primary Header.
		if ( $_BETHEL['DrawHeader'] === 'header1' )
		{
			$header_icon = '';

			if ( isset( $_BETHEL['HeaderIcon'] )
				&& $_BETHEL['HeaderIcon'] !== false )
			{
				$header_icon = '<span class="module-icon ' . $_BETHEL['HeaderIcon'] . '"';

				if ( $_BETHEL['HeaderIcon'] !== 'misc'
					&& ! in_array( $_BETHEL['HeaderIcon'], $BETHELCoreModules ) )
				{
					// Modcat is addon module, set custom module icon.
					$header_icon .= ' style="background-image: url(modules/' . $_BETHEL['HeaderIcon'] . '/icon.png);"';
				}

				$header_icon .= '></span> ';
			}

			$left = '<h2>' . $header_icon . $left . '</h2>';
		}

		echo '<td class="' . $_BETHEL['DrawHeader'] . '">' .
			$left .
		'</td>';
	}

	if ( $center )
	{
		echo '<td class="' . $_BETHEL['DrawHeader'] . ' center">' .
			$center .
		'</td>';
	}

	if ( $right )
	{
		echo '<td class="' . $_BETHEL['DrawHeader'] . ' align-right">' .
			$right .
		'</td>';
	}

	echo '</tr></table>';

	// Secondary Headers.
	$_BETHEL['DrawHeader'] = 'header2';
}
