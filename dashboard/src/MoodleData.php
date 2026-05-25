<?php

class MoodleData
{
    private static function p(): string
    {
        return Database::p();
    }

    /**
     * Returns distinct year-semester combinations found in course shortnames.
     * Shortname format: {year}_{sem}_{coursenum}
     */
    public static function getAvailableSemesters(): array
    {
        $p = self::p();
        $sql = "
            SELECT DISTINCT
                SPLIT_PART(shortname, '_', 1) AS year,
                SPLIT_PART(shortname, '_', 2) AS semester,
                SPLIT_PART(shortname, '_', 1) || '_' || SPLIT_PART(shortname, '_', 2) AS year_sem
            FROM {$p}course
            WHERE shortname ~ '^\S+_\S+_\d{5,}'
              AND visible = 1
            ORDER BY year_sem DESC
        ";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Returns course list with activity stats for the given department codes and semester.
     *
     * @param array  $deptCodes  e.g. ['35','44'] – empty = all departments (admin)
     * @param string $semFilter  e.g. 'תשפו_ב' – empty = all semesters
     */
    public static function getCourseStats(array $deptCodes = [], string $semFilter = ''): array
    {
        $p   = self::p();
        $ts30 = time() - 30 * 86400;

        // Build department filter
        $deptWhere = '1=1';
        $deptParams = [];
        if (!empty($deptCodes)) {
            $ins = implode(',', array_fill(0, count($deptCodes), '?'));
            $deptWhere = "SUBSTRING(SPLIT_PART(c.shortname, '_', 3), 1, 2) IN ($ins)";
            $deptParams = $deptCodes;
        }

        // Build semester filter
        $semWhere  = '1=1';
        $semParams = [];
        if ($semFilter !== '') {
            // e.g. semFilter = 'תשפו_ב'  -> LIKE 'תשפו\_ב\_%' ESCAPE '\'
            $semWhere  = "c.shortname LIKE ? ESCAPE '\\'";
            $semParams = [str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $semFilter) . '_%'];
        }

        $sql = "
            WITH enrolled AS (
                SELECT DISTINCT ue.userid, en.courseid
                FROM {$p}enrol en
                JOIN {$p}user_enrolments ue ON ue.enrolid = en.id AND ue.status = 0
                JOIN {$p}user u ON u.id = ue.userid AND u.deleted = 0
                WHERE en.status = 0
            )
            SELECT
                c.id,
                c.fullname,
                c.shortname,
                SUBSTRING(SPLIT_PART(c.shortname, '_', 3), 1, 2) AS dept_code,
                COUNT(e.userid)                                                              AS total_students,
                COUNT(CASE WHEN ula.lastaccess IS NOT NULL AND ula.lastaccess > $ts30
                           THEN 1 END)                                                       AS active_30d,
                COUNT(CASE WHEN ula.lastaccess IS NOT NULL AND ula.lastaccess <= $ts30
                           THEN 1 END)                                                       AS inactive_30d,
                COUNT(CASE WHEN ula.lastaccess IS NULL THEN 1 END)                           AS never_accessed,
                (SELECT COUNT(DISTINCT cm.module)
                 FROM {$p}course_modules cm
                 WHERE cm.course = c.id AND cm.deletioninprogress = 0)                       AS component_count
            FROM {$p}course c
            LEFT JOIN enrolled e  ON e.courseid  = c.id
            LEFT JOIN {$p}user_lastaccess ula ON ula.userid = e.userid AND ula.courseid = c.id
            WHERE c.visible = 1
              AND $deptWhere
              AND $semWhere
            GROUP BY c.id, c.fullname, c.shortname
            ORDER BY c.fullname
        ";

        $params = array_merge($deptParams, $semParams);
        return Database::query($sql, $params)->fetchAll();
    }

    /**
     * Returns unique module types used in a course (for the popup).
     */
    public static function getCourseComponents(int $courseId): array
    {
        $p   = self::p();
        $sql = "
            SELECT m.name AS component, COUNT(*) AS cnt
            FROM {$p}course_modules cm
            JOIN {$p}modules m ON m.id = cm.module
            WHERE cm.course = ? AND cm.deletioninprogress = 0
            GROUP BY m.name
            ORDER BY cnt DESC
        ";
        return Database::query($sql, [$courseId])->fetchAll();
    }

    /**
     * Returns students enrolled in a course with activity details.
     */
    public static function getCourseStudents(int $courseId): array
    {
        $p    = self::p();
        $now  = time();

        $sql = "
            WITH log_time AS (
                SELECT
                    userid,
                    SUM(LEAST(COALESCE(
                        LEAD(timecreated) OVER (PARTITION BY userid ORDER BY timecreated) - timecreated,
                        0
                    ), 1800)) / 60.0 AS total_minutes
                FROM {$p}logstore_standard_log
                WHERE courseid = ?
                GROUP BY userid
            )
            SELECT
                u.id,
                u.firstname,
                u.lastname,
                u.email,
                ula.lastaccess,
                CASE
                    WHEN ula.lastaccess IS NULL THEN NULL
                    ELSE FLOOR(($now - ula.lastaccess) / 86400)
                END::integer                          AS days_since_access,
                COALESCE(lt.total_minutes, 0)         AS total_minutes
            FROM {$p}user u
            JOIN {$p}user_enrolments ue ON ue.userid = u.id AND ue.status = 0
            JOIN {$p}enrol en ON en.id = ue.enrolid AND en.courseid = ? AND en.status = 0
            LEFT JOIN {$p}user_lastaccess ula ON ula.userid = u.id AND ula.courseid = ?
            LEFT JOIN log_time lt ON lt.userid = u.id
            WHERE u.deleted = 0
            ORDER BY u.lastname, u.firstname
        ";
        return Database::query($sql, [$courseId, $courseId, $courseId])->fetchAll();
    }

    /**
     * Returns activity summary for a department (used by pie chart).
     *
     * @return array{active: int, needs_followup: int, inactive: int}
     */
    public static function getDepartmentActivitySummary(array $deptCodes, string $semFilter = ''): array
    {
        $courses = self::getCourseStats($deptCodes, $semFilter);
        $total30  = 0;
        $inactive30 = 0;
        $never    = 0;

        foreach ($courses as $c) {
            $total30    += (int)$c['active_30d'];
            $inactive30 += (int)$c['inactive_30d'];
            $never      += (int)$c['never_accessed'];
        }

        return [
            'active'        => $total30,
            'needs_followup'=> $inactive30,
            'inactive'      => $never,
        ];
    }

    /**
     * Returns per-course active/total ratio for the pie chart (course-level).
     */
    public static function getDepartmentCoursePieData(array $deptCodes, string $semFilter = ''): array
    {
        $courses = self::getCourseStats($deptCodes, $semFilter);
        $labels = $values = $colors = [];
        $palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
        $i = 0;
        foreach ($courses as $c) {
            if ((int)$c['total_students'] === 0) continue;
            $labels[] = $c['fullname'];
            $values[] = (int)$c['active_30d'];
            $colors[] = $palette[$i % count($palette)];
            $i++;
        }
        return compact('labels', 'values', 'colors');
    }

    /**
     * Returns a course by ID (used to show the course name on the students page).
     */
    public static function getCourse(int $courseId): ?array
    {
        $p   = self::p();
        $sql = "SELECT id, fullname, shortname FROM {$p}course WHERE id = ?";
        $row = Database::query($sql, [$courseId])->fetch();
        return $row ?: null;
    }
}
