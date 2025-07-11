<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Callback implementations for User Attendance
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Check user enrolment in all courses within a specific category.
 * This function returns true if the user is enrolled in at least one course in the category.
 *
 * @param int $categoryid
 * @param int $userid
 *
 * @return bool
 *
 */
function report_userattend_check_user_enrolment_in_category(int $categoryid, int $userid) :bool {
    global $DB;

    // Fetch courses in the category.
    $courses = $DB->get_fieldset_select('course', 'id', 'category = ?', [$categoryid]);

    foreach ($courses as $courseid) {
        // Check if the user is enrolled in the course.
        if (is_enrolled(context_course::instance($courseid), $userid)) {
            return true; // User is enrolled in at least one course in the category.
        }
    }

    return false; // User is not enrolled in any course in the category.
}

/**
 * Get list of subcategories (programs) where the user is enrolled in at least one course.
 *
 * @param int $userid The ID of the user.
 * @return array $enrolledprograms Array of programs id and name.
 */
function report_userattend_get_user_programs_in_batch(int $userid): array {
    global $DB;

    $batchid = get_config('report_userattend', 'batch');

    // Fetch the records from DB of sub-categories (Programs).
    $programs = $DB->get_records_menu('course_categories', ['parent' => $batchid], 'id', 'id, name');

    // Get the programs where the user is enrolled in at least one course.
    $enrolledprograms = [];
    $isbreak = false;
    foreach ($programs as $programid => $programname) {
        // Fetch the subcategories (quarters) under this program.
        $quarters = $DB->get_fieldset_select('course_categories', 'id', 'parent = ?', [$programid]);
        foreach ($quarters as $quarterid) {
            // Get courses in quarter category.
            $courses = $DB->get_fieldset_select('course', 'id', 'category = ?', [$quarterid]);
            foreach ($courses as $courseid) {
                // Check if the user is enrolled in this course.
                $context = \context_course::instance($courseid);
                if (is_enrolled($context, $userid)) {
                    // If the user is enrolled, add the program to the list.
                    $enrolledprograms[$programid] = $programname;

                    $isbreak = true; // User is enrolled in at least one course in this program.
                    break; // No need to check more courses in this subcategory
                }
            }

            // If the user is enrolled in this program, no need to check further quarters.
            if ($isbreak) {
                $isbreak = false;
                break;
            }
        }
    }

    return $enrolledprograms;
}


/**
 * Get user attendance record in a course.
 *
 * @param int $userid
 * @param array $sessionids
 *
 * @return array
 *
 */
function report_userattend_get_user_attendance_record_in_course(int $userid, int $courseid): array {
    global $DB;

    // Fetch user last access date in the course.
    $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', ['userid' => $userid, 'courseid' => $courseid]);
    $lastaccess = ($lastaccess == 0) ? 'Never' : $lastaccess;

    $emptydata = [
        'emptydata'             => true,
        'coursepresent'         => '-',
        'courseabsent'          => '-',
        'courselate'            => '-',
        'coursegraderate'       => '-',
        'courselastsession'     => $lastaccess,
        'isstrlastsession'      => is_string($lastaccess),
    ];

    // Don't add the course in the report if the user is not enrolled.
    if (!is_enrolled(context_course::instance($courseid), $userid)) {
        $emptydata['courselastsession'] = 'Not Enrolled';
        $emptydata['isstrlastsession'] = true;
        return $emptydata;
    }

    // Check if the course has auto attendance activity.
    if (!$DB->record_exists('autoattendmod', ['course' => $courseid])) {
        return $emptydata; // No auto attendance activity in this course.
    }

    // Check if there are any auto attendance sessions for the course.
    if (!$DB->record_exists('autoattend_sessions', ['courseid' => $courseid])) {
        return $emptydata; // No sessions for the course.
    }

    // Retrieve all auto attendance sessions records of user for the course.
    $userattendances = $DB->get_records_sql(
        "SELECT stu.attsid, stu.status, stu.calledtime,
                stt.grade
           FROM {autoattend_students} stu
           JOIN {autoattend_sessions} ses ON ses.id = stu.attsid
           JOIN {autoattend_settings} stt ON stt.courseid = ses.courseid AND stt.status = stu.status
          WHERE stu.studentid = :userid
            AND ses.courseid = :courseid
       ORDER BY stu.calledtime DESC", [
            'userid'    => $userid,
            'courseid'  => $courseid,
        ]
    );

    // Init loop variables.
    $coursepresent = $courseabsent = $courselate = $courselastsession = $usertotalgrades = 0;
    $presentstatusgrade = 0;

    foreach ($userattendances as $userattendance) {
        if ($userattendance->status === 'P') {
            $coursepresent++;

            // Get the grade for the present status.
            if ($presentstatusgrade == 0) {
                $presentstatusgrade = $userattendance->grade;
            }
        } else if ($userattendance->status === 'X') {
            $courseabsent++;
        } else if ($userattendance->status === 'L') {
            $courselate++;
        }

        // Get the last session called time of the user for all sessions.
        if ($userattendance->calledtime > $courselastsession) {
            $courselastsession = $userattendance->calledtime;
        }

        // Calculate the total grades of user in all autoattendance sessions.
        $usertotalgrades += $userattendance->grade;
    }

    // Get all sessions IDs for the course auto attendance activity.
    $coursetotalsessions = $DB->count_records('autoattend_sessions', ['courseid' => $courseid]);
    // Calculate the total grades in auto attendance for the course.
    $coursetotalgrades = $presentstatusgrade * $coursetotalsessions;

    return [
        'coursepresent'         => $coursepresent,
        'courseabsent'          => $courseabsent,
        'courselate'            => $courselate,
        'coursegraderate'       => ($coursetotalgrades > 0) ? round(($usertotalgrades / $coursetotalgrades) * 100, 1) : 0,
        'courselastsession'     => $courselastsession,
        'isstrlastsession'      => false,
    ];
}

/**
 * Get user attendance record in a quarter.
 *
 * @param int $userid
 * @param int $quarterid
 *
 * @return array
 *
 */
function report_userattend_get_user_attendance_record_in_quarter(int $userid, int $quarterid): array {
    global $DB;

    // Get all courses under the quarter.
    $courses = $DB->get_records('course', ['category' => $quarterid], 'id', 'id, fullname');
    // Check if there are no courses in the quarter.
    if (empty($courses)) {
        return [];
    }

    // Init loop variables.
    $quartercoursesdata = [];
    $quarterpresent = $quarterabsent = $quarterlate = $quartergraderate = $quarterlastsession = $processedquartercourses = 0;

    foreach ($courses as $course) {
        $courseid = $course->id;

        // Initialize course data.
        $coursedata = report_userattend_get_user_attendance_record_in_course($userid, $courseid);
        // Add two more values to coursedata for the Mustache context.
        $coursedata['courseid']     = $courseid;
        $coursedata['coursename']   = $course->fullname;

        // CALCULATE quarter values for the report context.
        // Add course data to quarter courses data.
        $quartercoursesdata[] = $coursedata;

        if (!array_key_exists('emptydata', $coursedata)) { // Check if the course data is not empty.
            // Calculate quarter present, absent, late and grade rate.
            $quarterpresent += $coursedata['coursepresent'];
            $quarterabsent  += $coursedata['courseabsent'];
            $quarterlate    += $coursedata['courselate'];
            // Calculate quarter grade rate.
            $quartergraderate += $coursedata['coursegraderate'];
            // Check if the last session date of the quarter is greater than cumulative last session date.
            if ($quarterlastsession < $coursedata['courselastsession']) {
                $quarterlastsession = (int) $coursedata['courselastsession'];
            }

            // Total processed courses in the quarter.
            $processedquartercourses++;
        }

    }

    // Set for empty quarter last session.
    $quarterlastsession = ($quarterlastsession == 0) ? '-' : $quarterlastsession;

    return [
        'quartercourses'        => $quartercoursesdata,
        'quarterpresent'        => $quarterpresent,
        'quarterabsent'         => $quarterabsent,
        'quarterlate'           => $quarterlate,
        'quartergraderate'      => $processedquartercourses > 0 ? round($quartergraderate / $processedquartercourses, 1) : 0,
        'quarterlastsession'    => $quarterlastsession,
        'isstrlastsession'      => is_string($quarterlastsession),
    ];
}

/**
 * Get the context of 'user attendance report in a specific program' for the Mustache template.
 *
 * @param int $userid
 * @param int $programid
 *
 * @return array $reportcontext
 *
 */
function report_userattend_get_context_of_user_attendance_report_in_program(int $userid, int $programid): array {
    global $DB;

    // Get all quarters under the program.
    $quarters = $DB->get_records('course_categories', ['parent' => $programid]);
    if (empty($quarters)) {
        return [];
    }

    // Init loop variables.
    $cumulativepresent = $cumulativeabsent = $cumulativelate = $cumulativegraderate = $cumulativelastsession = $processedcumulativequarters = 0;

    foreach ($quarters as $quarter) {
        $quarterid = $quarter->id;

        // Initialize course data.
        $quarterdata = report_userattend_get_user_attendance_record_in_quarter($userid, $quarterid);
        // Add two more values to quarterdata for the Mustache context.
        $quarterdata['quarterid']     = $quarterid;
        $quarterdata['quartername']   = $quarter->name;

        // CALCULATE cummulative values for the report context.
        // Add course data to quarter courses data.
        $cumulativequartersdata[] = $quarterdata;
        // Calculate quarter present, absent, late and grade rate.
        $cumulativepresent += $quarterdata['quarterpresent'];
        $cumulativeabsent  += $quarterdata['quarterabsent'];
        $cumulativelate    += $quarterdata['quarterlate'];
        // Calculate quarter grade rate.
        $cumulativegraderate += $quarterdata['quartergraderate'];
        // Total processed courses in the quarter.
        if ($quarterdata['quartergraderate'] > 0) {
            $processedcumulativequarters++;
        }
        // Check if the last session date of the quarter is greater than cumulative last session date.
        if ($cumulativelastsession < $quarterdata['quarterlastsession']) {
            $cumulativelastsession = $quarterdata['quarterlastsession'];
        }
    }

    // Total processed quarters in the program.
    $totalcumulativequarters = count($cumulativequartersdata);

    return [
        'userid'                => $userid,
        'quarters'              => $cumulativequartersdata,
        'cumulativepresent'     => $cumulativepresent,
        'cumulativeabsent'      => $cumulativeabsent,
        'cumulativelate'        => $cumulativelate,
        'cumulativegraderate'   => $totalcumulativequarters > 0 ? round($cumulativegraderate / $totalcumulativequarters, 1) : 0,
        'cumulativelastsession' => $cumulativelastsession,
        'isstrlastsession'      => is_string($cumulativelastsession),
    ];
}



