<?php
/**
 * Demo/mock replacement for MoodleData.
 * Returns static data so the dashboard works without a real Moodle DB.
 * Active only when config.php has 'demo' => true.
 */
class MoodleData
{
    private static array $courses = [
        ['id'=>1,'fullname'=>'עיצוב תעשייתי מתקדם','shortname'=>'תשפה_א_3501001','idnumber'=>'IND-501','dept_code'=>'35','total_students'=>45,'active_30d'=>40,'inactive_30d'=>5,'never_accessed'=>0,'component_count'=>8],
        ['id'=>2,'fullname'=>'תכנות מתקדם Python','shortname'=>'תשפה_ב_4401003','idnumber'=>'CS-401','dept_code'=>'44','total_students'=>67,'active_30d'=>52,'inactive_30d'=>11,'never_accessed'=>4,'component_count'=>12],
        ['id'=>3,'fullname'=>'אדריכלות בת-קיימא','shortname'=>'תשפה_א_3301005','idnumber'=>'ARCH-301','dept_code'=>'33','total_students'=>38,'active_30d'=>18,'inactive_30d'=>15,'never_accessed'=>5,'component_count'=>6],
        ['id'=>4,'fullname'=>'מבנה נתונים ואלגוריתמים','shortname'=>'תשפה_ב_4402001','idnumber'=>'CS-402','dept_code'=>'44','total_students'=>82,'active_30d'=>61,'inactive_30d'=>14,'never_accessed'=>7,'component_count'=>14],
        ['id'=>5,'fullname'=>'צילום ועריכת וידאו','shortname'=>'תשפה_א_3601007','idnumber'=>'VIS-201','dept_code'=>'36','total_students'=>29,'active_30d'=>0,'inactive_30d'=>22,'never_accessed'=>7,'component_count'=>2],
        ['id'=>6,'fullname'=>'ממשקי משתמש ו-UX','shortname'=>'תשפה_ב_3501009','idnumber'=>'IND-601','dept_code'=>'35','total_students'=>54,'active_30d'=>47,'inactive_30d'=>6,'never_accessed'=>1,'component_count'=>10],
        ['id'=>7,'fullname'=>'הנדסת תוכנה - אג׳ייל','shortname'=>'תשפה_א_4403002','idnumber'=>'CS-403','dept_code'=>'44','total_students'=>71,'active_30d'=>58,'inactive_30d'=>9,'never_accessed'=>4,'component_count'=>9],
        ['id'=>8,'fullname'=>'עיצוב גרפי ממוחשב','shortname'=>'תשפה_ב_3601004','idnumber'=>'VIS-301','dept_code'=>'36','total_students'=>33,'active_30d'=>29,'inactive_30d'=>3,'never_accessed'=>1,'component_count'=>7],
    ];

    private static array $students = [
        ['id'=>101,'firstname'=>'נועה','lastname'=>'כהן','email'=>'n.cohen@s.ac.il','days_since_access'=>3,'total_minutes'=>872],
        ['id'=>102,'firstname'=>'יואב','lastname'=>'לוי','email'=>'y.levi@s.ac.il','days_since_access'=>7,'total_minutes'=>558],
        ['id'=>103,'firstname'=>'שירה','lastname'=>'מזרחי','email'=>'s.mizrahi@s.ac.il','days_since_access'=>45,'total_minutes'=>185],
        ['id'=>104,'firstname'=>'אדם','lastname'=>'ברק','email'=>'a.barak@s.ac.il','days_since_access'=>null,'total_minutes'=>0],
        ['id'=>105,'firstname'=>'תמר','lastname'=>'גולן','email'=>'t.golan@s.ac.il','days_since_access'=>12,'total_minutes'=>1367],
        ['id'=>106,'firstname'=>'רון','lastname'=>'שפירא','email'=>'r.shapira@s.ac.il','days_since_access'=>2,'total_minutes'=>920],
        ['id'=>107,'firstname'=>'מיכל','lastname'=>'אורן','email'=>'m.oren@s.ac.il','days_since_access'=>62,'total_minutes'=>210],
        ['id'=>108,'firstname'=>'עמית','lastname'=>'נחמיאס','email'=>'a.nahmias@s.ac.il','days_since_access'=>1,'total_minutes'=>2240],
        ['id'=>109,'firstname'=>'דנה','lastname'=>'פרידמן','email'=>'d.friedman@s.ac.il','days_since_access'=>null,'total_minutes'=>0],
        ['id'=>110,'firstname'=>'איתן','lastname'=>'רוזנברג','email'=>'e.rosenberg@s.ac.il','days_since_access'=>18,'total_minutes'=>430],
        ['id'=>111,'firstname'=>'הדר','lastname'=>'כץ','email'=>'h.katz@s.ac.il','days_since_access'=>5,'total_minutes'=>780],
        ['id'=>112,'firstname'=>'אבי','lastname'=>'מלכה','email'=>'a.malka@s.ac.il','days_since_access'=>95,'total_minutes'=>60],
    ];

    public static function getAvailableSemesters(): array
    {
        return [
            ['year'=>'תשפה','semester'=>'ב','year_sem'=>'תשפה_ב'],
            ['year'=>'תשפה','semester'=>'א','year_sem'=>'תשפה_א'],
            ['year'=>'תשפד','semester'=>'ב','year_sem'=>'תשפד_ב'],
        ];
    }

    public static function getCourseStats(array $deptCodes = [], string $semFilter = '', int $categoryId = 0): array
    {
        $courses = self::$courses;

        // Filter by dept codes if provided
        if (!empty($deptCodes)) {
            $courses = array_filter($courses, fn($c) => in_array($c['dept_code'], $deptCodes));
        }

        // Filter by semester prefix
        if ($semFilter !== '') {
            $courses = array_filter($courses, fn($c) => str_starts_with($c['shortname'], $semFilter . '_'));
        }

        return array_values($courses);
    }

    public static function getCourseStudents(int $courseId): array
    {
        return self::$students;
    }

    public static function getCourse(int $courseId): ?array
    {
        foreach (self::$courses as $c) {
            if ((int)$c['id'] === $courseId) return $c;
        }
        return self::$courses[0]; // fallback for demo
    }

    public static function getCourseComponents(int $courseId): array
    {
        return [
            ['component'=>'assign','cnt'=>6],
            ['component'=>'quiz','cnt'=>4],
            ['component'=>'resource','cnt'=>8],
            ['component'=>'forum','cnt'=>2],
            ['component'=>'url','cnt'=>3],
        ];
    }

    public static function getCourseAssignStats(array $courseIds): array
    {
        $result = [];
        $mockStats = [
            ['assign_count'=>8,'submitted_unique'=>38,'graded_unique'=>35,'enrolled'=>45],
            ['assign_count'=>12,'submitted_unique'=>41,'graded_unique'=>29,'enrolled'=>67],
            ['assign_count'=>6,'submitted_unique'=>14,'graded_unique'=>8,'enrolled'=>38],
            ['assign_count'=>14,'submitted_unique'=>68,'graded_unique'=>44,'enrolled'=>82],
            ['assign_count'=>4,'submitted_unique'=>3,'graded_unique'=>0,'enrolled'=>29],
            ['assign_count'=>10,'submitted_unique'=>49,'graded_unique'=>47,'enrolled'=>54],
            ['assign_count'=>9,'submitted_unique'=>60,'graded_unique'=>55,'enrolled'=>71],
            ['assign_count'=>7,'submitted_unique'=>28,'graded_unique'=>25,'enrolled'=>33],
        ];
        foreach ($courseIds as $i => $id) {
            $stat = $mockStats[$i % count($mockStats)];
            $stat['course'] = $id;
            $result[$id] = $stat;
        }
        return $result;
    }

    public static function getCategories(): array
    {
        return [
            ['id'=>1,'name'=>'שנקר','parent'=>0,'depth'=>0,'path'=>'/1'],
            ['id'=>2,'name'=>'שנקר 2025','parent'=>1,'depth'=>1,'path'=>'/1/2'],
            ['id'=>3,'name'=>'סמסטר א׳','parent'=>2,'depth'=>2,'path'=>'/1/2/3'],
            ['id'=>4,'name'=>'סמסטר ב׳','parent'=>2,'depth'=>2,'path'=>'/1/2/4'],
            ['id'=>5,'name'=>'שנקר 2024','parent'=>1,'depth'=>1,'path'=>'/1/5'],
            ['id'=>6,'name'=>'סמסטר א׳','parent'=>5,'depth'=>2,'path'=>'/1/5/6'],
            ['id'=>7,'name'=>'סמסטר ב׳','parent'=>5,'depth'=>2,'path'=>'/1/5/7'],
        ];
    }

    public static function getCategoriesFlat(): array
    {
        $result = [];
        foreach (self::getCategories() as $cat) {
            $depth  = (int)$cat['depth'];
            $indent = str_repeat('— ', $depth);
            $result[] = ['id' => (int)$cat['id'], 'name' => $indent . $cat['name'], 'depth' => $depth];
        }
        return $result;
    }

    public static function getCategorySubtreeIds(int $rootId): array
    {
        return [1, 2, 3, 4, 5, 6, 7]; // return all for demo
    }
}
