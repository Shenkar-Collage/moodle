<?php
require_once __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$courseId = (int)($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    json_response(['error' => 'Invalid course'], 400);
}

// Verify dept access
try {
    $course = MoodleData::getCourse($courseId);
    if (!$course) json_response([], 200);

    $deptCode = get_dept_code_from_shortname($course['shortname']);
    if (!Auth::canViewDepartment($deptCode)) {
        json_response(['error' => 'Access denied'], 403);
    }

    $data = MoodleData::getCourseComponents($courseId);
    json_response($data);
} catch (Exception $e) {
    json_response(['error' => $e->getMessage()], 500);
}
