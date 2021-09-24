<?php
/**
 * Dashboard
 *
 * @package BETHELSIS
 * @subpackage ProgramFunctions
 */

/**
 * Dashboard build
 *
 * Calls, for each active and user module
 * the `Dashboard[Module_Name]` function.
 *
 * Place your add-on module `Dashboard[Module_Name]` function in the functions.php file.
 *
 * @uses $_REQUEST['_BETHEL_DASHBOARD']
 *
 * @todo For example, set $_REQUEST['_BETHEL_DASHBOARD']['export'] to 1 to export data.
 * In URL: &_BETHEL_DASHBOARD[export]=1
 *
 * @global $BETHELModules
 * @global $_BETHEL
 * @see DashboardModule.fnc.php for default core modules `Dashboard[Module_Name]` functions.
 * @since 4.0
 */
function Dashboard()
{
	global $BETHELModules,
		$_BETHEL;

	require_once 'Menu.php';
	require_once 'ProgramFunctions/DashboardModule.fnc.php';

	if ( ! isset( $_BETHEL['Dashboard'] ) )
	{
		$_BETHEL['Dashboard'] = array();
	}

	if ( ! empty( $_REQUEST['_BETHEL_DASHBOARD'] ) )
	{
		$_BETHEL['Dashboard'] = array_merge_recursive( $_BETHEL['Dashboard'], $_REQUEST['_BETHEL_DASHBOARD'] );
	}

	foreach ( $BETHELModules as $module => $activated )
	{
		if ( ! $activated )
		{
			// Module not activated.
			continue;
		}

		if ( ! function_exists( 'Dashboard' . $module ) )
		{
			// No Dashboard function for module.
			continue;
		}

		if ( empty( $_BETHEL['Menu'][$module] ) )
		{
			// User profile has no access to module.
			continue;
		}

		$dashboard_html = call_user_func( 'Dashboard' . $module );

		DashboardAdd( $module, $dashboard_html, true );
	}
}

/**
 * Dashboard Output HTML
 * Modules HTML inside PopTable
 *
 * @global $_BETHEL
 * @since 4.0
 * @since 7.7 Move Dashboard() call outside.
 *
 * @param integer $rows Number of modules per row, defaults to 4. Optional.
 */
function DashboardOutput( $rows = 4 )
{
	global $_BETHEL;

	if ( empty( $_BETHEL['Dashboard'] ) )
	{
		return;
	}

	echo '<br />';

	PopTable( 'header', _( 'Dashboard' ), 'width="100%"' );

	?>
	<table class="dashboard width-100p valign-top fixed-col"><tr class="st">
	<?php

	if ( $rows < 1 )
	{
		$rows = 4;
	}

	$row = 0;

	// Output Dashboard modules, 4 per row.

	foreach ( $_BETHEL['Dashboard'] as $module => $html ): ?>

		<td><?php echo $html; ?></td>

		<?php

	if ( ++$row % $rows === 0 ): ?>

			</tr><tr class="st">

		<?php endif;
	endforeach;

	?>
	</tr></table>
	<?php

	PopTable( 'footer' );
}

/**
 * Add module HTML to Dashboard
 *
 * @global $_BETHEL Add module HTML to $_BETHEL['Dashboard'][ $module ]
 * @since 4.0
 *
 * @param string  $module Module.
 * @param string  $html   Dashboard HTML.
 * @param boolean $append Append HTML.
 */
function DashboardAdd( $module, $html, $append = true )
{
	global $_BETHEL;

	if ( empty( $html ) )
	{
		return;
	}

	if ( $append
		&& ! empty( $_BETHEL['Dashboard'][$module] ) )
	{
		$_BETHEL['Dashboard'][$module] .= $html;
	}
	else
	{
		$_BETHEL['Dashboard'][$module] = $html;
	}
}
