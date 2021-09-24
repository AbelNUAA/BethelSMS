<?php
require_once 'ProgramFunctions/Theme.fnc.php';

DrawHeader( ProgramTitle() );

if ( ! empty( $_REQUEST['values'] )
	&& ! empty( $_POST['values'] ) )
{
	if ( $_REQUEST['tab'] == 'password' )
	{
		$current_password = $_POST['values']['current'];
		$new_password = $_POST['values']['new'];

		//hook
		do_action( 'Users/Preferences.php|update_password_checks' );

		if ( ! $error )
		{
			//FJ enable password change for students

			if ( User( 'PROFILE' ) === 'student' )
			{
				$password = DBGetOne( "SELECT PASSWORD
					FROM STUDENTS
					WHERE STUDENT_ID='" . UserStudentID() . "'" );
			}
			else
			{
				$password = DBGetOne( "SELECT PASSWORD
					FROM STAFF
					WHERE STAFF_ID='" . User( 'STAFF_ID' ) . "'
					AND SYEAR='" . UserSyear() . "'" );
			}

			if ( ! match_password( $password, $current_password ) )
			{
				$error[] = _( 'Your current password was incorrect.' );
			}
			else
			{
				if ( User( 'PROFILE' ) === 'student' )
				{
					DBQuery( "UPDATE STUDENTS
						SET PASSWORD='" . encrypt_password( $new_password ) . "'
						WHERE STUDENT_ID='" . UserStudentID() . "'" );
				}
				else
				{
					DBQuery( "UPDATE STAFF
						SET PASSWORD='" . encrypt_password( $new_password ) . "'
						WHERE STAFF_ID='" . User( 'STAFF_ID' ) . "'
						AND SYEAR='" . UserSyear() . "'" );
				}

				$note[] = _( 'Your new password was saved.' );

				//hook
				do_action( 'Users/Preferences.php|update_password' );
			}
		}
	}
	else
	{
		if ( $_REQUEST['tab'] == 'student_listing' && $_REQUEST['values']['Preferences']['SEARCH'] != 'Y' )
		{
			$_REQUEST['values']['Preferences']['SEARCH'] = 'N';
		}

		if ( $_REQUEST['tab'] == 'student_listing' && User( 'PROFILE' ) === 'admin' && $_REQUEST['values']['Preferences']['DEFAULT_FAMILIES'] != 'Y' )
		{
			$_REQUEST['values']['Preferences']['DEFAULT_FAMILIES'] = 'N';
		}

		if ( $_REQUEST['tab'] == 'student_listing' && User( 'PROFILE' ) === 'admin' && $_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS'] != 'Y' )
		{
			$_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS'] = 'N';
		}

		if ( $_REQUEST['tab'] == 'display_options' && $_REQUEST['values']['Preferences']['HIDE_ALERTS'] != 'Y' )
		{
			$_REQUEST['values']['Preferences']['HIDE_ALERTS'] = 'N';
		}

		foreach ( (array) $_REQUEST['values'] as $program => $values )
		{
			ProgramUserConfig( $program, 0, $values );
		}

		$note[] = button( 'check' ) . '&nbsp;' . _( 'Your preferences were saved.' );

		$old_theme = Preferences( 'THEME' );

		// So Preferences() will get the new values.
		unset( $_BETHEL['Preferences'] );

		// Theme changed? Update it live!
		ThemeLiveUpdate( Preferences( 'THEME' ), $old_theme, false );
	}

	// Unset values & redirect URL.
	RedirectURL( 'values' );
}

// Unset search modfunc & redirect URL.
RedirectURL( 'search_modfunc' );

echo ErrorMessage( $error );

echo ErrorMessage( $note, 'note' );

if ( ! $_REQUEST['modfunc'] )
{
	$current_RET = DBGet( "SELECT TITLE,VALUE,PROGRAM
		FROM PROGRAM_USER_CONFIG
		WHERE USER_ID='" . User( 'STAFF_ID' ) . "'
		AND PROGRAM IN ('Preferences','StudentFieldsSearch','StudentFieldsView',
			'WidgetsSearch','StaffFieldsSearch','StaffFieldsView','StaffWidgetsSearch')",
		array(), array( 'PROGRAM', 'TITLE' ) );

	if ( empty( $_REQUEST['tab'] ) )
	{
		$_REQUEST['tab'] = 'password';
	}

	echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=' . $_REQUEST['tab'] ) . '" method="POST">';

	DrawHeader( '', Buttons( _( 'Save' ) ) );

	echo '<br />';

	if ( User( 'PROFILE' ) === 'admin'
		|| User( 'PROFILE' ) === 'teacher' )
	{
		$_BETHEL['allow_edit'] = true;

		$tabs = array(
			array(
				'title' => _( 'Display Options' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=display_options',
			),
			array(
				'title' => _( 'Print Options' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=print_options',
			),
			array(
				'title' => _( 'Student Listing' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=student_listing',
			),
			array(
				'title' => _( 'Password' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=password',
			),
			array(
				'title' => _( 'Student Fields' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=student_fields',
			),
			array(
				'title' => _( 'Widgets' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=widgets',
			),
		);

		if ( User( 'PROFILE' ) === 'admin' )
		{
			$tabs[] = array(
				'title' => _( 'User Fields' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=staff_fields',
			);

			$tabs[] = array(
				'title' => _( 'User Widgets' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=staff_widgets',
			);
		}
	}
	elseif ( User( 'PROFILE' ) === 'parent' )
	{
		$_BETHEL['allow_edit'] = true;

		$tabs = array(
			array(
				'title' => _( 'Display Options' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=display_options',
			),
			array(
				'title' => _( 'Print Options' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=print_options',
			),
			array(
				'title' => _( 'Password' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=password',
			),
			array(
				'title' => _( 'Student Fields' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=student_fields',
			),
		);
	}

	// FJ enable password change for students.
	else
	{
		$tabs = array(
			array(
				'title' => _( 'Password' ),
				'link' => 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=password',
			),
		);
	}

	$_BETHEL['selected_tab'] = 'Modules.php?modname=' . $_REQUEST['modname'] . '&tab=' . $_REQUEST['tab'];

	$LO_options = array(
		'responsive' => false,
		'search' => false,
		'save' => false,
	);

	if ( ! in_array( $_REQUEST['tab'], array( 'student_fields', 'staff_fields' ) ) )
	{
		PopTable( 'header', $tabs );
	}
	else
	{
		// FJ Responsive student/staff fields preferences.
		$LO_options['header'] = WrapTabs( $tabs, $_BETHEL['selected_tab'] );
	}

	// Inputs param defaults.
	$allow_na = $div = false;

	$new = true;

	$extra = '';

	// Student Listing tab

	if ( $_REQUEST['tab'] === 'student_listing' )
	{
		echo '<table class="cellpadding-5"><tr><td>';

		// Student Sorting.
		echo SelectInput(
			Preferences( 'SORT' ),
			'values[Preferences][SORT]',
			_( 'Student Sorting' ),
			array( 'Name' => _( 'Name' ), 'Grade' => _( 'Grade Level' ) . ', ' . _( 'Name' ) ),
			$allow_na,
			$extra,
			$div
		);

		echo '</td></tr><tr><td>';

		// File Export Type.
		echo SelectInput(
			Preferences( 'DELIMITER' ),
			'values[Preferences][DELIMITER]',
			_( 'File Export Type' ),
			array(
				'Tab' => _( 'Excel' ),
				'CSV' => 'CSV (OpenOffice / LibreOffice) (UTF-8)', // Do not Translate
				'XML' => 'XML', // Do not Translate
			),
			$allow_na,
			$extra,
			$div
		);

		echo '</td></tr><tr><td>';

		// Date Export Format.
		echo SelectInput(
			Preferences( 'E_DATE' ),
			'values[Preferences][E_DATE]',
			_( 'Date Export Format' ),
			array(
				'' => _( 'Display Options Format' ),
				'MM/DD/YYYY' => 'MM/DD/YYYY', // Do not Translate
				// @since 7.1 Export (Excel) date to YYYY-MM-DD format (ISO).
				'YYYY-MM-DD' => 'YYYY-MM-DD', // Do not Translate
			),
			$allow_na,
			$extra,
			$div
		);

		echo '</td></tr>';

		if ( User( 'PROFILE' ) === 'admin'
			|| User( 'PROFILE' ) === 'teacher' )
		{
			// Display student search screen.
			echo '<tr><td>' . CheckboxInput(
				Preferences( 'SEARCH' ),
				'values[Preferences][SEARCH]',
				_( 'Display student search screen' ),
				'',
				$new
			) . '</td></tr>';
		}

		if ( User( 'PROFILE' ) === 'admin' )
		{
			// Group by family by default.
			echo '<tr><td>' . CheckboxInput(
				Preferences( 'DEFAULT_FAMILIES' ),
				'values[Preferences][DEFAULT_FAMILIES]',
				_( 'Group by family by default' ),
				'',
				$new
			) . '</td></tr>';

			// FJ if only one school, no Search All Schools option.
			// Restrict Search All Schools to user schools.

			if ( SchoolInfo( 'SCHOOLS_NB' ) > 1
				&& ( ! trim( User( 'SCHOOLS' ), ',' )
					|| mb_substr_count( User( 'SCHOOLS' ), ',' ) > 2 ) )
			{
				// Search all schools by default.
				echo '<tr><td>' . CheckboxInput(
					Preferences( 'DEFAULT_ALL_SCHOOLS' ),
					'values[Preferences][DEFAULT_ALL_SCHOOLS]',
					_( 'Search all schools by default' ),
					'',
					$new
				) . '</td></tr>';
			}
		}

		echo '</table>';
	}

	// Display Options tab.

	if ( $_REQUEST['tab'] === 'display_options' )
	{
		echo '<table class="cellpadding-5"><tr><td>';

		$theme_options = array();

		$themes = glob( 'assets/themes/*', GLOB_ONLYDIR );

		foreach ( (array) $themes as $theme )
		{
			$theme_name = str_replace( 'assets/themes/', '', $theme );

			$theme_options[$theme_name] = $theme_name;
		}

		$theme_value = Preferences( 'THEME' );

		// http://stackoverflow.com/questions/1479233/why-doesnt-firefox-show-the-correct-default-select-option
		$extra = 'autocomplete="off"';

		if ( Config( 'THEME_FORCE' ) )
		{
			// Theme forced, we should not be able to change it.
			$extra = 'disabled';

			$theme_value = Config( 'THEME' );
		}

		// Theme.
		echo SelectInput(
			$theme_value,
			'values[Preferences][THEME]',
			_( 'Theme' ),
			$theme_options,
			$allow_na,
			$extra,
			$div
		);

		$extra = '';

		echo '</td></tr><tr><td>';

		echo ColorInput(
			Preferences( 'HIGHLIGHT' ),
			'values[Preferences][HIGHLIGHT]',
			_( 'Highlight Color' ),
			$extra,
			$div
		);

		echo '</td></tr><tr><td>';

		// @since 7.1 Select Date Format: Add Preferences( 'DATE' ).
		// @link https://en.wikipedia.org/wiki/Date_format_by_country
		// @link https://www.php.net/strftime
		$date_options = array(
			'%B %d %Y' => ucfirst( strftime( '%B %d %Y' ) ), // August 18 2020.
			'%b %d %Y' => ucfirst( strftime( '%b %d %y' ) ), // Aug 18 20.
			'%d %B %Y' => strftime( '%d %B %Y' ), // 18 August 2020.
			'%d %b %Y' => strftime( '%d %b %y' ), // 18 Aug 20.
			'%m/%d/%Y' => strftime( '%m/%d/%Y' ), // 08/18/2020.
			'%d/%m/%Y' => strftime( '%d/%m/%Y' ), // 18/08/2020.
			'%Y/%m/%d' => strftime( '%Y/%m/%d' ), // 2020/08/18.
			'%d.%m.%Y' => strftime( '%d.%m.%Y' ), // 18.08.2020.
			'%d-%m-%Y' => strftime( '%d-%m-%Y' ), // 18-08-2020.
			'%Y-%m-%d' => strftime( '%Y-%m-%d' ), // 2020-08-18.
		);

		echo SelectInput(
			Preferences( 'DATE' ),
			'values[Preferences][DATE]',
			_( 'Date Format' ),
			$date_options,
			$allow_na,
			$extra,
			$div
		);

		echo '</td></tr><tr><td>';

		// Disable login alerts
		echo CheckboxInput(
			Preferences( 'HIDE_ALERTS' ),
			'values[Preferences][HIDE_ALERTS]',
			_( 'Disable login alerts' ),
			'',
			$new
		);

		echo '</td></tr></table>';
	}

	// Pint Options tab

	if ( $_REQUEST['tab'] === 'print_options' )
	{
		echo '<table class="cellpadding-5"><tr><td>';

		// Page Size
		echo SelectInput(
			Preferences( 'PAGE_SIZE' ),
			'values[Preferences][PAGE_SIZE]',
			_( 'Page Size' ),
			array( 'A4' => 'A4', 'LETTER' => _( 'US Letter' ) ),
			$allow_na,
			$extra,
			$div
		);

		echo '</td></tr><tr><td>';

		echo ColorInput(
			Preferences( 'HEADER' ),
			'values[Preferences][HEADER]',
			_( 'PDF List Header Color' ),
			$extra,
			$div
		);

		echo '</td></tr><tr><td></table>';
	}

	if ( $_REQUEST['tab'] === 'password' )
	{
		$allow_edit_tmp = AllowEdit();

		$_BETHEL['allow_edit'] = true;

		//FJ password fields are required
		echo '<table class="cellpadding-5"><tr><td>';

		// Current Password
		echo PasswordInput( '', 'values[current]', _( 'Current Password' ), 'required' );

		echo '</td></tr><tr><td>';

		// New Password.
		echo PasswordInput( '', 'values[new]', _( 'New Password' ), 'required strength' );

		echo '</td></tr></table>';

		$_BETHEL['allow_edit'] = $allow_edit_tmp;
	}

	// Student Fields tab.

	if ( $_REQUEST['tab'] === 'student_fields' )
	{
		$custom_fields_sql = "SELECT sfc.TITLE AS CATEGORY,cf.ID,cf.TITLE,cf.TYPE,
				'' AS SEARCH,'' AS DISPLAY
			FROM CUSTOM_FIELDS cf,STUDENT_FIELD_CATEGORIES sfc
			WHERE sfc.ID=cf.CATEGORY_ID
			AND (SELECT CAN_USE FROM " .
			( User( 'PROFILE_ID' ) ?
			"PROFILE_EXCEPTIONS WHERE PROFILE_ID='" . User( 'PROFILE_ID' ) . "'" :
			"STAFF_EXCEPTIONS WHERE USER_ID='" . User( 'STAFF_ID' ) . "'" ) .
			" AND MODNAME='Students/Student.php&category_id='||cf.CATEGORY_ID
			LIMIT 1)='Y'
			AND cf.TYPE<>'files'
			ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE";

		$custom_fields_RET = DBGet(
			DBQuery( $custom_fields_sql ),
			array( 'SEARCH' => '_make', 'DISPLAY' => '_make' ),
			array( 'CATEGORY' )
		);

		foreach ( $custom_fields_RET as &$category_RET )
		{
			foreach ( $category_RET as &$field )
			{
				$field['CATEGORY'] = '<b>' . ParseMLField( $field['CATEGORY'] ) . '</b>';
				$field['TITLE'] = ParseMLField( $field['TITLE'] );
			}
		}

		// Student Fields: search Username.
		$general_info_category_title = DBGetOne( "SELECT sfc.TITLE
			FROM STUDENT_FIELD_CATEGORIES sfc
			WHERE sfc.ID=1" );

		if ( ! isset( $custom_fields_RET[$general_info_category_title] ) )
		{
			// Empty General Info category.
			$custom_fields_RET[$general_info_category_title] = array();
		}

		$THIS_RET['ID'] = 'USERNAME';
		$username_field = array(
			'CATEGORY' => '<b>' . ParseMLField( $general_info_category_title ) . '</b>',
			'ID' => 'USERNAME',
			'TITLE' => _( 'Username' ),
			'SEARCH' => _make( 'USERNAME', 'SEARCH' ),
			'DISPLAY' => _make( 'USERNAME', 'DISPLAY' ),
		);

		// Add Username to General Info fields.
		$custom_fields_RET[$general_info_category_title] = array_merge(
			array( $username_field ),
			$custom_fields_RET[$general_info_category_title]
		);

		$THIS_RET['ID'] = 'CONTACT_INFO';
		$custom_fields_RET[-1][1] = array(
			'CATEGORY' => '<b>' . _( 'Contact Information' ) . '</b>',
			'ID' => 'CONTACT_INFO',
			'TITLE' => button( 'down_phone', '', '', 'bigger' ) . ' ' . _( 'Contact Information' ),
			'DISPLAY' => _make( '', 'DISPLAY' ),
		);

		$THIS_RET['ID'] = 'HOME_PHONE';
		$custom_fields_RET[-1][] = array(
			'CATEGORY' => '<b>' . _( 'Contact Information' ) . '</b>',
			'ID' => 'HOME_PHONE',
			'TITLE' => _( 'Home Phone Number' ),
			'DISPLAY' => _make( '', 'DISPLAY' ),
		);

		$THIS_RET['ID'] = 'GUARDIANS';
		$custom_fields_RET[-1][] = array(
			'CATEGORY' => '<b>' . _( 'Contact Information' ) . '</b>',
			'ID' => 'GUARDIANS',
			'TITLE' => _( 'Guardians' ),
			'DISPLAY' => _make( '', 'DISPLAY' ),
		);

		$THIS_RET['ID'] = 'ALL_CONTACTS';
		$custom_fields_RET[-1][] = array(
			'CATEGORY' => '<b>' . _( 'Contact Information' ) . '</b>',
			'ID' => 'ALL_CONTACTS',
			'TITLE' => _( 'All Contacts' ),
			'DISPLAY' => _make( '', 'DISPLAY' ),
		);

		$custom_fields_RET[0][1] = array(
			'CATEGORY' => '<b>' . _( 'Addresses' ) . '</b>',
			'ID' => 'ADDRESS',
			'TITLE' => _( 'None' ),
			'DISPLAY' => _makeAddress( '' ),
		);

		$custom_fields_RET[0][] = array(
			'CATEGORY' => '<b>' . _( 'Addresses' ) . '</b>',
			'ID' => 'ADDRESS',
			'TITLE' => button( 'house', '', '', 'bigger' ) . ' ' . _( 'Residence' ),
			'DISPLAY' => _makeAddress( 'RESIDENCE' ),
		);

		//FJ disable mailing address display

		if ( Config( 'STUDENTS_USE_MAILING' ) )
		{
			$custom_fields_RET[0][] = array(
				'CATEGORY' => '<b>' . _( 'Addresses' ) . '</b>',
				'ID' => 'ADDRESS',
				'TITLE' => button( 'mailbox', '', '', 'bigger' ) . ' ' . _( 'Mailing' ),
				'DISPLAY' => _makeAddress( 'MAILING' ),
			);
		}

		$custom_fields_RET[0][] = array(
			'CATEGORY' => '<b>' . _( 'Addresses' ) . '</b>',
			'ID' => 'ADDRESS',
			'TITLE' => button( 'bus', '', '', 'bigger' ) . ' ' . _( 'Bus Pickup' ),
			'DISPLAY' => _makeAddress( 'BUS_PICKUP' ),
		);

		$custom_fields_RET[0][] = array(
			'CATEGORY' => '<b>' . _( 'Addresses' ) . '</b>',
			'ID' => 'ADDRESS',
			'TITLE' => button( 'bus', '', '', 'bigger' ) . ' ' . _( 'Bus Dropoff' ),
			'DISPLAY' => _makeAddress( 'BUS_DROPOFF' ),
		);

		if ( User( 'PROFILE' ) === 'admin'
			|| User( 'PROFILE' ) === 'teacher' )
		{
			$columns = array(
				'CATEGORY' => '<span class="a11y-hidden">' . _( 'Category' ) . '</span>',
				'TITLE' => _( 'Field' ),
				'SEARCH' => _( 'Search' ),
				'DISPLAY' => _( 'Expanded View' ),
			);
		}
		else
		{
			$columns = array(
				'CATEGORY' => '<span class="a11y-hidden">' . _( 'Category' ) . '</span>',
				'TITLE' => _( 'Field' ),
				'DISPLAY' => _( 'Expanded View' ),
			);
		}

		ListOutput(
			$custom_fields_RET,
			$columns,
			'.',
			'.',
			array(),
			array( array( 'CATEGORY' ) ),
			$LO_options
		);
	}

	// Widgets tab.

	if ( $_REQUEST['tab'] === 'widgets' )
	{
		$widgets = array();

		if ( $BETHELModules['Students'] )
		{
			$widgets += array(
				'calendar' => _( 'Calendar' ),
				'next_year' => _( 'Next School Year' ),
			);
		}

		if ( $BETHELModules['Scheduling'] && User( 'PROFILE' ) === 'admin' )
		{
			$widgets += array( 'course' => _( 'Course' ), 'request' => _( 'Request' ) );
		}

		if ( $BETHELModules['Attendance'] )
		{
			$widgets += array( 'absences' => _( 'Days Absent' ) );
		}

		if ( $BETHELModules['Grades'] )
		{
			$widgets += array(
				'gpa' => _( 'GPA' ),
				'class_rank' => _( 'Class Rank' ),
				'letter_grade' => _( 'Grade' ),
			);
		}

		if ( $BETHELModules['Eligibility'] )
		{
			$widgets += array( 'eligibility' => _( 'Eligibility' ), 'activity' => _( 'Activity' ) );
		}

		if ( $BETHELModules['Food_Service'] )
		{
			$widgets += array(
				'fsa_balance' => _( 'Food Service Balance' ),
				'fsa_discount' => _( 'Food Service Discount' ),
				'fsa_status' => _( 'Food Service Status' ),
				'fsa_barcode' => _( 'Food Service Barcode' ),
			);
		}

		if ( $BETHELModules['Discipline'] )
		{
			$widgets += array(
				'reporter' => _( 'Discipline Reporter' ),
				'incident_date' => _( 'Discipline Incident Date' ),
				'discipline_fields' => _( 'Discipline Fields' ),
			);
		}

		if ( $BETHELModules['Student_Billing'] )
		{
			$widgets += array( 'balance' => _( 'Student Billing Balance' ) );
		}

		$widgets_RET[0] = array();

		foreach ( (array) $widgets as $widget => $title )
		{
			$THIS_RET['ID'] = $widget;
			$widgets_RET[] = array( 'ID' => $widget, 'TITLE' => $title, 'WIDGET' => _make( '', 'WIDGET' ) );
		}

		unset( $widgets_RET[0] );

		echo '<input type="hidden" name="values[WidgetsSearch]" />';

		$columns = array( 'TITLE' => _( 'Widget' ), 'WIDGET' => _( 'Search' ) );

		ListOutput(
			$widgets_RET,
			$columns,
			'.',
			'.',
			array(),
			array(),
			$LO_options
		);
	}

	// Staff Fields tab.

	if ( $_REQUEST['tab'] === 'staff_fields'
		&& User( 'PROFILE' ) === 'admin' )
	{
		$custom_fields_sql = "SELECT sfc.TITLE AS CATEGORY,cf.ID,cf.TITLE,cf.TYPE,
				'' AS STAFF_SEARCH,'' AS STAFF_DISPLAY
			FROM STAFF_FIELDS cf,STAFF_FIELD_CATEGORIES sfc
			WHERE sfc.ID=cf.CATEGORY_ID
			AND (SELECT CAN_USE
				FROM " .
			( User( 'PROFILE_ID' ) ?
			"PROFILE_EXCEPTIONS WHERE PROFILE_ID='" . User( 'PROFILE_ID' ) . "'" :
			"STAFF_EXCEPTIONS WHERE USER_ID='" . User( 'STAFF_ID' ) . "'" ) .
			" AND MODNAME='Users/User.php&category_id='||cf.CATEGORY_ID
			LIMIT 1)='Y'
			AND cf.TYPE<>'files'
			ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE";

		$custom_fields_RET = DBGet(
			DBQuery( $custom_fields_sql ),
			array( 'STAFF_SEARCH' => '_make', 'STAFF_DISPLAY' => '_make' ),
			array( 'CATEGORY' )
		);

		foreach ( $custom_fields_RET as &$category_RET )
		{
			foreach ( $category_RET as &$field )
			{
				$field['CATEGORY'] = '<b>' . ParseMLField( $field['CATEGORY'] ) . '</b>';
				$field['TITLE'] = ParseMLField( $field['TITLE'] );
			}
		}

		// User Fields: search Email Address & Phone.
		$general_info_category_title = DBGetOne( "SELECT sfc.TITLE
			FROM STAFF_FIELD_CATEGORIES sfc
			WHERE sfc.ID=1" );

		if ( isset( $custom_fields_RET[$general_info_category_title] ) )
		{
			$i = count( $custom_fields_RET[$general_info_category_title] );
		}
		else
		{
			$i = 1;

			// Empty General Info category.
			$custom_fields_RET[$general_info_category_title] = array();
		}

		echo '<input type="hidden" name="values[StaffFieldsSearch]" />
			<input type="hidden" name="values[StaffFieldsView]" />';

		$columns = array(
			'CATEGORY' => '<span class="a11y-hidden">' . _( 'Category' ) . '</span>',
			'TITLE' => _( 'Field' ),
			'STAFF_SEARCH' => _( 'Search' ),
			'STAFF_DISPLAY' => _( 'Expanded View' ),
		);

		//FJ no responsive table
		ListOutput(
			$custom_fields_RET,
			$columns,
			'.',
			'.',
			array(),
			array( array( 'CATEGORY' ) ),
			$LO_options
		);
	}

	// Staff Widgets tab

	if ( $_REQUEST['tab'] === 'staff_widgets'
		&& User( 'PROFILE' ) === 'admin' )
	{
		$widgets = array();

		if ( $BETHELModules['Users'] )
		{
			$widgets += array( 'permissions' => _( 'Permissions' ) );
		}

		if ( $BETHELModules['Food_Service'] )
		{
			$widgets += array(
				'fsa_balance' => _( 'Food Service Balance' ),
				'fsa_status' => _( 'Food Service Status' ),
				'fsa_barcode' => _( 'Food Service Barcode' ),
			);
		}

		if ( $BETHELModules['Accounting'] )
		{
			$widgets += array( 'staff_balance' => _( 'Staff Payroll Balance' ) );
		}

		$widgets_RET[0] = array();

		foreach ( (array) $widgets as $widget => $title )
		{
			$THIS_RET['ID'] = $widget;
			$widgets_RET[] = array( 'ID' => $widget, 'TITLE' => $title, 'STAFF_WIDGET' => _make( '', 'STAFF_WIDGET' ) );
		}

		unset( $widgets_RET[0] );

		echo '<input type="hidden" name="values[StaffWidgetsSearch]" />';
		$columns = array( 'TITLE' => _( 'Widget' ), 'STAFF_WIDGET' => _( 'Search' ) );

		ListOutput(
			$widgets_RET,
			$columns,
			'.',
			'.',
			array(),
			array(),
			$LO_options
		);
	}

	if ( ! in_array( $_REQUEST['tab'], array( 'student_fields', 'staff_fields' ) ) )
	{
		PopTable( 'footer' );
	}

	echo '<br /><div class="center">' . Buttons( _( 'Save' ) ) . '</div>';
	echo '</form>';
}

/**
 * Make Checkbox
 *
 * @since 5.3.2 & 4.9.12 Fix regression since 4.4 save unchecked config option: use CheckboxInput()
 *
 * @param string $value
 * @param string $name Column.
 *
 * @return string Checkbox HTML.
 */
function _make( $value, $name )
{
	global $THIS_RET,
		$current_RET,
		$_BETHEL;

	// No Search checkbox for textarea fields.

	if ( isset( $THIS_RET['TYPE'] )
		&& $THIS_RET['TYPE'] === 'textarea'
		&& mb_strpos( $name, 'SEARCH' ) !== false )
	{
		return '';
	}

	switch ( $name )
	{
		case 'SEARCH':
			$program = 'StudentFieldsSearch';

			break;

		case 'DISPLAY':
			$program = 'StudentFieldsView';

			break;

		case 'WIDGET':
			$program = 'WidgetsSearch';

			break;

		case 'STAFF_SEARCH':
			$program = 'StaffFieldsSearch';

			break;

		case 'STAFF_DISPLAY':
			$program = 'StaffFieldsView';

			break;

		case 'STAFF_WIDGET':
			$program = 'StaffWidgetsSearch';

			break;
	}

	$name = 'values[' . $program . '][' . $THIS_RET['ID'] . ']';

	$_BETHEL['allow_edit'] = true;

	$checkbox = CheckboxInput(
		issetVal( $current_RET[ $program ][$THIS_RET['ID']][1]['VALUE'], '' ),
		$name,
		'',
		'',
		true
	);

	$_BETHEL['allow_edit'] = false;

	return $checkbox;
}

/**
 * @param $value
 */
function _makeAddress( $value )
{
	global $current_RET;

	$checked = ( empty( $current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] ) && $value == '' )
	|| ( ! empty( $current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] ) == $value
		&& $current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] == $value ) ? ' checked' : '';

	return '<input type="radio" name="values[StudentFieldsView][ADDRESS]" value="' . $value . '"' . $checked . ' />';
}