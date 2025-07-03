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


function get_user_attendance_report(int $userid, int $programid): array {
    global $DB;
    // Geting quarters records against program id
    $quarters = $DB->get_fieldset_select('course_categories', 'id', 'parent = ?', [$programid]);

    if (empty($quarters)) {
        return ['quarters' => [], 'message' => 'No quarters found for this program.'];
    }
    // Initialize the variables
    $reportdata = [
        'quarters' => [],
        'summary' => [
            'totalpresent' => 0,
            'totalabsent' => 0,
            'totalleave' => 0,
            'lastsession' => '-'
        ]
    ];

    foreach ($quarters as $index => $qid) {
        $quartertitle = 'Quarter ' . ($index + 1);
        // Getting course records
        $courses = $DB->get_records('course', ['category' => $qid], 'id', 'id, fullname');
        $coursedata = []; 
         // Initialize the variables   
        $totalpresent = $totalabsent = $totalleave = 0;
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            if (!is_enrolled($context, $userid)) {
                continue;
            }
            // Checking is attendance activity present against course ids.
            $attendance = $DB->record_exists('autoattendmod', ['course' => $course->id]);
            
            if ($attendance==false) {
                continue;
            }
            // Geting sessions records
            $sessions = $DB->get_records('autoattend_sessions', ['courseid' => $course->id]);
            if (empty($sessions)) {
                continue;
            }
            // Geting sessions id's
            $sessionids = array_keys($sessions);
            list($sql, $params) = $DB->get_in_or_equal($sessionids, SQL_PARAMS_NAMED);
            $params['userid'] = $userid;
            // Geting attendance records
            $logs = $DB->get_records_sql("
                SELECT attsid, status, calledtime
                FROM {autoattend_students}
                WHERE studentid = :userid AND attsid $sql
                ORDER BY calledtime DESC
            ", $params);

            $p = $a = $l = 0;
            $lastdate = '';

            foreach ($logs as $log) {
                // Check status 
                if ($log->status === 'P') {
                    $p++;
                } elseif ($log->status === 'A' || $log->status === 'X') {
                    $a++;
                } elseif ($log->status === 'L') {
                    $l++;
                }

                if (empty($lastdate) || $log->calledtime > strtotime($lastdate)) {
                    $lastdate = $sessions[$log->attsid]->sessdate ?? null;
                }
            }

            $totalpresent += $p;
            $totalabsent += $a;
            $totalleave += $l;

            $coursedata[] = [
                'coursename' => $course->fullname,
                'p' => $p,
                'a' => $a,
                'l' => $l,
                
                'lastsession' => $lastdate ? userdate($lastdate) : '-',
            ];
        }

        if (!empty($coursedata)) {
            
            $reportdata['quarters'][] = [
                'title' => $quartertitle,
                'courses' => $coursedata,
                'totalpresent' => $totalpresent,
                'totalabsent' => $totalabsent,
                'totalleave' => $totalleave,
                
                'lastsession' => $lastdate ? userdate($lastdate) : '-'
            ];

            // Add to cumulative summary
            $reportdata['summary']['totalpresent'] += $totalpresent;
            $reportdata['summary']['totalabsent'] += $totalabsent;
            $reportdata['summary']['totalleave'] += $totalleave;
        }
    }

    // Set latest session for summary
    $reportdata['summary']['lastsession'] = $lastdate ? userdate($lastdate) : '-';

    return $reportdata;
}



