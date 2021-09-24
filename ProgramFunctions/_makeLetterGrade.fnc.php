<?php
/**
 * Make Letter Grade function
 *
 * @package BETHELSIS
 * @subpackage ProgramFunctions
 */

/**
 * Calculate letter grade from percent
 * Determine letter grade with breakoff values:
 * percent >= breakoff
 * Take in account Teacher grade scale if any (DOES_BREAKOFF)
 *
 * Used in:
 * Eligibility/EnterEligibility.php
 * Grades/GradebookBreakdown.php
 * Grades/Grades.php
 * Grades/InputFinalGrades.php
 * Grades/ProgressReports.php
 * Grades/StudentGrades.php
 *
 * @since 7.5 Percent rounding to 2 decimal places is new School default.
 *
 * @example _makeLetterGrade( $percent, $course_period_id, $staff_id )
 *
 * @uses ProgramUserConfig()
 *
 * @global array   $_BETHEL         Sets $_BETHEL['_makeLetterGrade']
 *
 * @param  string  $percent          Percent grade.
 * @param  integer $course_period_id Course period ID (optional). Defaults to UserCoursePeriod().
 * @param  integer $staff_id         Staff ID (optional). Defaults to User( 'STAFF_ID' ).
 * @param  string  $ret              Returned column (optional). Defaults to 'TITLE'.
 *
 * @return string                    report card letter grade
 */
function _makeLetterGrade( $percent, $course_period_id = 0, $staff_id = 0, $ret = 'TITLE' )
{
	global $_BETHEL;

	$course_period_id = $course_period_id ? $course_period_id : UserCoursePeriod();

	if ( ! $staff_id )
	{
		if ( User( 'PROFILE' ) === 'teacher' )
		{
			$staff_id = User( 'STAFF_ID' );
		}
		else
		{
			$staff_id = DBGetOne( "SELECT TEACHER_ID
				FROM COURSE_PERIODS
				WHERE COURSE_PERIOD_ID='" . $course_period_id . "'" );
		}
	}

	$gradebook_config = ProgramUserConfig( 'Gradebook', $staff_id );

	// Save courses in $_BETHEL['_makeLetterGrade']['courses'] global var.
	if ( ! isset( $_BETHEL['_makeLetterGrade']['courses'][ $course_period_id ] ) )
	{
		$_BETHEL['_makeLetterGrade']['courses'][ $course_period_id ] = DBGet( "SELECT DOES_BREAKOFF,GRADE_SCALE_ID
			FROM COURSE_PERIODS
			WHERE COURSE_PERIOD_ID='" . $course_period_id . "'" );
	}

	$does_breakoff = $_BETHEL['_makeLetterGrade']['courses'][ $course_period_id ][1]['DOES_BREAKOFF'];

	$grade_scale_id = $_BETHEL['_makeLetterGrade']['courses'][ $course_period_id ][1]['GRADE_SCALE_ID'];

	$percent *= 100;

	// If Teacher Grade Scale.
	if ( $does_breakoff === 'Y'
		&& is_array( $gradebook_config ) )
	{
		if ( $gradebook_config['ROUNDING'] === 'UP' )
		{
			$percent = ceil( $percent );
		}
		elseif ( $gradebook_config['ROUNDING'] === 'DOWN' )
		{
			$percent = floor( $percent );
		}
		elseif ( $gradebook_config['ROUNDING'] === 'NORMAL' )
		{
			$percent = round( $percent );
		}
	}
	else
		$percent = round( $percent, 2 ); // School default.

	if ( $ret === '%' )
	{
		return $percent;
	}

	// Save grades in $_BETHEL['_makeLetterGrade']['grades'] global var.
	if ( ! isset( $_BETHEL['_makeLetterGrade']['grades'][ $grade_scale_id ] ) )
	{
		$_BETHEL['_makeLetterGrade']['grades'][ $grade_scale_id ] = DBGet( "SELECT TITLE,ID,BREAK_OFF,COMMENT
			FROM REPORT_CARD_GRADES
			WHERE SYEAR='" . UserSyear() . "'
			AND SCHOOL_ID='" . UserSchool() . "'
			AND GRADE_SCALE_ID='" . $grade_scale_id . "'
			ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER" );
	}

	// Fix error invalid input syntax for type numeric
	// If Teacher Grade Scale.
	if ( $does_breakoff === 'Y'
		&& is_array( $gradebook_config ) )
	{
		foreach ( (array) $_BETHEL['_makeLetterGrade']['grades'][ $grade_scale_id ] as $grade )
		{
			if ( is_numeric( $gradebook_config[ $course_period_id . '-' . $grade['ID'] ] )
				&& $percent >= $gradebook_config[ $course_period_id . '-' . $grade['ID'] ] )
			{
				// FJ use Report Card Grades comments.
				// return $ret=='ID' ? $grade['ID'] : $grade['TITLE'];
				return $grade[ $ret ];
			}
		}
	}

	foreach ( (array) $_BETHEL['_makeLetterGrade']['grades'][ $grade_scale_id ] as $grade )
	{
		if ( $percent >= $grade['BREAK_OFF'] )
		{
			// FJ use Report Card Grades comments.
			// return $ret=='ID' ? $grade['ID'] : $grade['TITLE'];
			return $grade[ $ret ];
		}
	}
}
