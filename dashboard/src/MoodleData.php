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
     * Returns all visible course categories ordered by path.
     */
    public static function getCategories(): array
    {
        $p = self::p();
        $sql = "
            SELECT id, name, parent, depth, path
            FROM {$p}course_categories
            WHERE visible = 1
            ORDER BY path
        ";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Returns a flat array of categories with indented names for use in dropdowns.
     */
    public static function getCategoriesFlat(): array
    {
        $cats = self::getCategories();
        $result = [];
        foreach ($cats as $cat) {
            $depth  = (int)($cat['depth'] ?? 0);
            $indent = str_repeat('— ', $depth);
            $result[] = [
                'id'    => (int)$cat['id'],
                'name'  => $indent . $cat['name'],
                'depth' => $depth,
            ];
        }
        return $result;
    }

    /**
     * Returns all category IDs in the subtree rooted at $rootId (inclusive),
     * using a PostgreSQL recursive CTE.
     */
    public static function getCategorySubtreeIds(int $rootId): array
    {
        $p = self::p();
        $sql = "
            WITH RECURSIVE cat_tree AS (
                SELECT id FROM {$p}course_categories WHERE id = ?
                UNION ALL
                SELECT c.id
                FROM {$p}course_categories c
                INNER JOIN cat_tree ct ON c.parent = ct.id
            )
            SELECT id FROM cat_tree
        ";
        $rows = Database::query($sql, [$rootId])->fetchAll();
        return array_column($rows, 'id');
    }

    /**
     * Returns course list with activity stats.
     *
     * @param array  $deptCodes   e.g. ['35','44'] – empty = all departments (admin)
     * @param string $semFilter   e.g. 'תשפו_ב' – empty = all semesters
     * @param int    $categoryId  if > 0, filter by category subtree instead of semFilter+deptCodes
     */
    public static function getCourseStats(array $deptCodes = [], string $semFilter = '', int $categoryId = 0): array
    {
        $p    = self::p();
        $ts30 = time() - 30 * 86400;

        $params = [];

        // Build category / dept / sem filter
        if ($categoryId > 0) {
            $catIds = self::getCategorySubtreeIds($categoryId);
            if (empty($catIds)) {
                return [];
            }
            $ins        = implode(',', array_map('intval', $catIds));
            $scopeWhere = "c.category IN ($ins)";
        } else {
            // Department filter
            $deptWhere  = '1=1';
            $deptParams = [];
            if (!empty($deptCodes)) {
                $ins        = implode(',', array_fill(0, count($deptCodes), '?'));
                $deptWhere  = "SUBSTRING(SPLIT_PART(c.shortname, '_', 3), 1, 2) IN ($ins)";
                $deptParams = $deptCodes;
            }

            // Semester filter
            $semWhere  = '1=1';
            $semParams = [];
            if ($semFilter !== '') {
                $semWhere  = "c.shortname LIKE ? ESCAPE '\\'";
                $semParams = [str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $semFilter) . '_%'];
            }

            $scopeWhere = "$deptWhere AND $semWhere";
            $params     = array_merge($deptParams, $semParams);
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
                c.idnumber,
                SUBSTRING(SPLIT_PART(c.shortname, '_', 3), 1, 2)                            AS dept_code,
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
            LEFT JOIN enrolled e   ON e.courseid  = c.id
            LEFT JOIN {$p}user_lastaccess ula ON ula.userid = e.userid AND ula.courseid = c.id
            WHERE c.visible = 1
              AND $scopeWhere
            GROUP BY c.id, c.fullname, c.shortname, c.idnumber
            ORDER BY c.fullname
        ";

        return Database::query($sql, $params)->fetchAll();
    }

    /**
     * Returns assignment submission/grading stats per course.
     *
     * @param array $courseIds  list of integer course IDs
     * @return array  keyed by course ID
     */
    public static function getCourseAssignStats(array $courseIds): array
    {
        if (empty($courseIds)) return [];

        $p   = self::p();
        $ins = implode(',', array_map('intval', $courseIds));

        $sql = "
            SELECT
                a.course,
                COUNT(DISTINCT a.id)                                                                         AS assign_count,
                COUNT(DISTINCT CASE WHEN sub.status = 'submitted' AND sub.latest = 1
                                    THEN sub.userid END)                                                      AS submitted_unique,
                COUNT(DISTINCT CASE WHEN ag.grade >= 0 AND ag.latest = 1
                                    THEN ag.userid END)                                                       AS graded_unique,
                (SELECT COUNT(DISTINCT ue2.userid)
                 FROM {$p}enrol en2
                 JOIN {$p}user_enrolments ue2 ON ue2.enrolid = en2.id AND ue2.status = 0
                 WHERE en2.courseid = a.course AND en2.status = 0)                                            AS enrolled
            FROM {$p}assign a
            LEFT JOIN {$p}assign_submission sub ON sub.assignment = a.id
            LEFT JOIN {$p}assign_grades     ag  ON ag.assignment  = a.id
            WHERE a.course IN ($ins)
            GROUP BY a.course
        ";

        $rows = Database::query($sql)->fetchAll();
        return array_column($rows, null, 'course');
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
        $p   = self::p();
        $now = time();

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
     * Returns a course by ID.
     */
    public static function getCourse(int $courseId): ?array
    {
        $p   = self::p();
        $sql = "SELECT id, fullname, shortname, idnumber FROM {$p}course WHERE id = ?";
        $row = Database::query($sql, [$courseId])->fetch();
        return $row ?: null;
    }

    /**
     * Returns activity summary for a department (used by pie chart).
     *
     * @return array{active: int, needs_followup: int, inactive: int}
     */
    public static function getDepartmentActivitySummary(array $deptCodes, string $semFilter = ''): array
    {
        $courses    = self::getCourseStats($deptCodes, $semFilter);
        $total30    = 0;
        $inactive30 = 0;
        $never      = 0;

        foreach ($courses as $c) {
            $total30    += (int)$c['active_30d'];
            $inactive30 += (int)$c['inactive_30d'];
            $never      += (int)$c['never_accessed'];
        }

        return [
            'active'         => $total30,
            'needs_followup' => $inactive30,
            'inactive'       => $never,
        ];
    }

    /**
     * Returns per-course active/total ratio for the pie chart (course-level).
     */
    public static function getDepartmentCoursePieData(array $deptCodes, string $semFilter = ''): array
    {
        $courses = self::getCourseStats($deptCodes, $semFilter);
        $labels  = $values = $colors = [];
        $palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
        $i       = 0;
        foreach ($courses as $c) {
            if ((int)$c['total_students'] === 0) continue;
            $labels[] = $c['fullname'];
            $values[] = (int)$c['active_30d'];
            $colors[] = $palette[$i % count($palette)];
            $i++;
        }
        return compact('labels', 'values', 'colors');
    }
}
