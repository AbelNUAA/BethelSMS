<?php
/**
 * User & Preferences functions
 *
 * @package BETHELSIS
 * @subpackage functions
 */

/**
 * Get (logged) User info
 *
 * @example User( 'PROFILE' )
 *
 * @since 7.6.1 Remove use of `$_SESSION['STAFF_ID'] === '-1'`.
 *
 * @global array  $_BETHEL Sets $_BETHEL['User']
 *
 * @param  string $item     User info item; see STAFF table fields for Admin/Parent/Teacher; STUDENT & STUDENT_ENROLLMENT fields for Student.
 *
 * @return string User info value
 */
function User( $item )
{
	global $_BETHEL;

	if ( ! $item )
	{
		return '';
	}

	// Set Current School Year if needed.
	if ( ! UserSyear() )
	{
		$_SESSION['UserSyear'] = Config( 'SYEAR' );
	}

	// Get User Info or Update it if Syear changed.
	if ( ! isset( $_BETHEL['User'] )
		|| UserSyear() !== $_BETHEL['User'][1]['SYEAR'] )
	{
		// Get User Info.
		if ( ! empty( $_SESSION['STAFF_ID'] )
			&& $_SESSION['STAFF_ID'] > 0 )
		{
			$sql = "SELECT STAFF_ID,USERNAME," . DisplayNameSQL() . " AS NAME,
				PROFILE,PROFILE_ID,SCHOOLS,CURRENT_SCHOOL_ID,EMAIL,SYEAR,LAST_LOGIN
				FROM STAFF
				WHERE SYEAR='" . UserSyear() . "'
				AND USERNAME=(SELECT USERNAME
					FROM STAFF
					WHERE SYEAR='" . Config( 'SYEAR' ) . "'
					AND STAFF_ID='" . $_SESSION['STAFF_ID'] . "')";

			$_BETHEL['User'] = DBGet( $sql );
		}
		// Get Student Info.
		elseif ( ! empty( $_SESSION['STUDENT_ID'] )
			&& $_SESSION['STUDENT_ID'] > 0 )
		{
			$sql = "SELECT '0' AS STAFF_ID,s.USERNAME," . DisplayNameSQL( 's' ) . " AS NAME,
				'student' AS PROFILE,'0' AS PROFILE_ID,LAST_LOGIN,
				','||se.SCHOOL_ID||',' AS SCHOOLS,se.SYEAR,se.SCHOOL_ID
				FROM STUDENTS s,STUDENT_ENROLLMENT se
				WHERE s.STUDENT_ID='" . $_SESSION['STUDENT_ID'] . "'
				AND se.SYEAR='" . UserSyear() . "'
				AND se.STUDENT_ID=s.STUDENT_ID
				ORDER BY se.END_DATE DESC LIMIT 1";

			$_BETHEL['User'] = DBGet( $sql );

			if ( ! empty( $_BETHEL['User'][1]['SCHOOL_ID'] )
				&& $_BETHEL['User'][1]['SCHOOL_ID'] !== UserSchool() )
			{
				$_SESSION['UserSchool'] = $_BETHEL['User'][1]['SCHOOL_ID'];
			}
		}
		else
		{
			return false;
		}
	}

	return issetVal( $_BETHEL['User'][1][ $item ] );
}


/**
 * Get User Preference
 *
 * @example  Preferences( 'THEME' )
 *
 * @global array  $_BETHEL Sets $_BETHEL['Preferences']
 *
 * @since 5.8 Preferences overridden with USER_ID='-1', see ProgramUserConfig().
 *
 * @param  string $item     Preference item.
 * @param  string $program  Preferences|Gradebook (optional).
 *
 * @return string          Preference value
 */
function Preferences( $item, $program = 'Preferences' )
{
	global $_BETHEL,
		$locale;

	if ( ! $item
		|| ! $program )
	{
		return '';
	}

	// Get User Preferences.
	if ( User( 'STAFF_ID' )
		&& ! isset( $_BETHEL['Preferences'][ $program ] ) )
	{
		$_BETHEL['Preferences'][ $program ] = DBGet( "SELECT TITLE,VALUE
			FROM PROGRAM_USER_CONFIG
			WHERE (USER_ID='" . User( 'STAFF_ID' ) . "' OR USER_ID='-1')
			AND PROGRAM='" . $program . "'
			ORDER BY USER_ID", array(), array( 'TITLE' ) );
	}

	$defaults = array(
		'SORT' => 'Name',
		'SEARCH' => 'Y',
		'DELIMITER' => 'Tab',
		'HEADER' => '#333366',
		'HIGHLIGHT' => '#FFFFFF',
		'THEME' => Config( 'THEME' ),
		// @since 7.1 Select Date Format: Add Preferences( 'DATE' ).
		// @link https://www.w3.org/International/questions/qa-date-format
		'DATE' => ( $locale === 'en_US.utf8' ? '%B %d %Y' : '%d %B %Y' ),
		// @deprecated since 7.1 Use Preferences( 'DATE' ).
		'MONTH' => '%B', 'DAY' => '%d', 'YEAR' => '%Y',
		'DEFAULT_ALL_SCHOOLS' => 'N',
		'ASSIGNMENT_SORTING' => 'ASSIGNMENT_ID',
		'ANOMALOUS_MAX' => '100',
		'PAGE_SIZE' => 'A4',
		'HIDE_ALERTS' => 'N',
		'DEFAULT_FAMILIES' => 'N',
	);

	if ( ! isset( $_BETHEL['Preferences'][ $program ][ $item ][1]['VALUE'] ) )
	{
		$_BETHEL['Preferences'][ $program ][ $item ][1]['VALUE'] = issetVal( $defaults[ $item ] );
	}

	/**
	 * Force Display student search screen to No
	 * for Parents & Students.
	 */
	if ( $item === 'SEARCH'
		&& ! empty( $_SESSION['STAFF_ID'] )
		&& User( 'PROFILE' ) === 'parent'
		|| ! empty( $_SESSION['STUDENT_ID'] ) )
	{
		$_BETHEL['Preferences'][ $program ]['SEARCH'][1]['VALUE'] = 'N';
	}

	/**
	 * Force Default Theme.
	 * Override user preference if any.
	 */
	if ( $item === 'THEME'
		&& Config( 'THEME_FORCE' )
		&& ! empty( $_SESSION['STAFF_ID'] ) )
	{
		$_BETHEL['Preferences'][ $program ]['THEME'][1]['VALUE'] = $defaults['THEME'];
	}

	return $_BETHEL['Preferences'][ $program ][ $item ][1]['VALUE'];
}

/**
 * Impersonate Teacher User
 * So User() function returns UserCoursePeriod() teacher
 * instead of admin or secondary teacher.
 *
 * @since 6.9 Add Secondary Teacher: set User to main teacher.
 *
 * @example if ( ! empty( $_SESSION['is_secondary_teacher'] ) ) UserImpersonateTeacher();
 *
 * @param int $teacher_id Teacher User ID (optional). Defaults to UserCoursePeriod() teacher.
 *
 * @return bool False if no $teacher_id & no UserCoursePeriod(), else true.
 */
function UserImpersonateTeacher( $teacher_id = 0 )
{
	global $_BETHEL;

	if ( ! $teacher_id
		&& ! UserCoursePeriod() )
	{
		return false;
	}

	if ( ! $teacher_id )
	{
		$teacher_id = DBGetOne( "SELECT TEACHER_ID
			FROM COURSE_PERIODS
			WHERE COURSE_PERIOD_ID='" . UserCoursePeriod() . "'" );
	}

	$_BETHEL['User'] = array(
		0 => $_BETHEL['User'][1],
		1 => array(
			'STAFF_ID' => $teacher_id,
			'NAME' => GetTeacher( $teacher_id ),
			'USERNAME' => GetTeacher( $teacher_id, 'USERNAME' ),
			'PROFILE' => 'teacher',
			'SCHOOLS' => ',' . UserSchool() . ',',
			'SYEAR' => UserSyear(),
		),
	);

	return true;
}