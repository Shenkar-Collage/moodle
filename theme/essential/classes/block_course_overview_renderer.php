<?php

if (file_exists($CFG->dirroot . "/blocks/course_overview/renderer.php") ) {
    include_once($CFG->dirroot . "/blocks/course_overview/renderer.php");

    include_once("course_filter_form.php");

    define ('SORTCOURSESBY_ABC', 0);
    define ('SORTCOURSESBY_LASTACCESS', 1);

    class theme_essential_block_course_overview_renderer extends block_course_overview_renderer {

        public function course_overview($courses, $overviews) {
            global $CFG, $USER;

            //list($categories, $childrencats, $roles, $filterbycategory, $filterbyrole, $filterbysemester, $html) = filter_courses_form();
            list($filterbyextendedcoursename, $filterbyrole, $html) = filter_courses_form();

            // Initiate semester list keys.
            $semesterlistkeys = array('-1'=>get_string('all'));
            foreach (explode(',',get_string('semesterlistkeys','theme_essential')) as $semesterkey) {
                $semesterlistkeys[] = $semesterkey;
            }

            // Remove courses which are not chosen by Category / Role / Semester
            foreach ($courses as $key => $course) {
                $course->context = context_course::instance($course->id, MUST_EXIST);
                if ($filterbyrole > 0 && !user_has_role_assignment($USER->id, $filterbyrole, $course->context->id)){
                    //continue;
                    unset($courses[$key]);
                }
//                if ($filterbycategory > 0) {
//                    if (isset($CFG->showonlytopcategories)) {  //Show courses from his category and all children categories
//                        if (!array_key_exists($course->category, $childrencats) && $course->category != $filterbycategory) {
//                            //continue;   //Course id not in category or in child category
//                            unset($courses[$key]);
//                        }
//                    } else {   //Show only courses in THIS category
//                        if ($course->category != $filterbycategory) {
//                            //continue;
//                            unset($courses[$key]);
//                        }
//                    }
//                }

//                list($course_year, $course_semester ,$course_code, $course_groupcode) = explode('_', $course->idnumber.'____');
//                if ( $filterbysemester >= 0 and $course_semester != $semesterlistkeys[$filterbysemester] ) {
//                    unset($courses[$key]);
//                }
                if (!empty($filterbyextendedcoursename) &&
                    mb_strpos($course->shortname, $filterbyextendedcoursename) === false) {
                    unset($courses[$key]);
                }
            }

            // Start of sort buttons
            $sortcoursesby = optional_param('sortcoursesby', SORTCOURSESBY_LASTACCESS, PARAM_INT);
            $selectedsort_abc = '';
            $selectedsort_lastaccess = '';
            switch ($sortcoursesby) {
                case SORTCOURSESBY_ABC:
                    // Sort by course fullname
                    usort($courses, function($a, $b) { return strcmp($a->fullname, $b->fullname); });
                    $selectedsort_abc = 'selected';

                    break;
                case SORTCOURSESBY_LASTACCESS:
                    // Sort by user's lastaccess to course
                    //usort($courses, function($a, $b) { return $a->lastaccess - $b->lastaccess; });

                //default:
                    global $DB;
                    $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid'=>$USER->id), 'timeaccess DESC');
                    //if ($USER->id == 5151) print_object($lastaccesscourses);
                    foreach ($lastaccesscourses as $c) {
                        if (isset($courses[$c->courseid])) {
                            $courses[$c->courseid]->lastaccess = $c->timeaccess;
                        }
                    }
                    // Sort by user's lastaccess to course
                    usort($courses, function($a, $b) { return $b->lastaccess - $a->lastaccess; });
                    $selectedsort_lastaccess = 'selected';

            }

            //$filterbycategory = optional_param('filterByCategory', $CFG->defaultcoursecategroy, PARAM_INT);
            //$filterbyrole = optional_param('filterByRole', -1, PARAM_INT);
            //$filterbysemester = optional_param('filterBySemester', -1, PARAM_INT);
            $formfilterparams = array(
                //'filterByCategory'=>$filterbycategory,
                'filterByRole'=> $filterbyrole,
                //'filterBySemester'=> $filterbysemester);
                'filterByExtendedCourseName'=> $filterbyextendedcoursename);
            $html .= html_writer::start_div('row-fluid');
                $sortcoursesurl = new moodle_url('/my/index.php', array_merge($formfilterparams, array('sortcoursesby' => SORTCOURSESBY_LASTACCESS)));
                $sortcoursesurlhtml = html_writer::link($sortcoursesurl, get_string('sortbylastaccess', 'theme_essential'), array('class' => 'btn '.$selectedsort_lastaccess));
                $html .= html_writer::tag('div', $sortcoursesurlhtml, array('class' => 'sortbylastaccess buttonz span6'));

                $sortcoursesurl = new moodle_url('/my/index.php', array_merge($formfilterparams, array('sortcoursesby' => SORTCOURSESBY_ABC)));
                $sortcoursesurlhtml = html_writer::link($sortcoursesurl, get_string('sortbyabc', 'theme_essential'), array('class' => 'btn '.$selectedsort_abc));
                $html .= html_writer::tag('div', $sortcoursesurlhtml, array('class' => 'sortbyabc buttonz span6'));
            $html .= html_writer::end_div();

            $html .= html_writer::tag('hr', '',array('style'=>'clear:both;'));
            // End of sort buttons

            //return $html . parent::course_overview($courses, $overviews);

            //$html = '';
            $config = get_config('block_course_overview');
            if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
                global $CFG;
                require_once($CFG->libdir.'/coursecatlib.php');
            }
            $ismovingcourse = false;
            $courseordernumber = 0;
            $maxcourses = count($courses);
            $userediting = false;
            // Intialise string/icon etc if user is editing and courses > 1
            if ($this->page->user_is_editing() && (count($courses) > 1)) {
                $userediting = true;
                $this->page->requires->js_init_call('M.block_course_overview.add_handles');

                // Check if course is moving
                $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
                $movingcourseid = optional_param('courseid', 0, PARAM_INT);
            }

            // Render first movehere icon.
            if ($ismovingcourse) {
                // Remove movecourse param from url.
                $this->page->ensure_param_not_in_url('movecourse');

                // Show moving course notice, so user knows what is being moved.
                $html .= $this->output->box_start('notice');
                $a = new stdClass();
                $a->fullname = $courses[$movingcourseid]->fullname;
                $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
                $html .= get_string('movingcourse', 'block_course_overview', $a);
                $html .= $this->output->box_end();

                $moveurl = new moodle_url('/blocks/course_overview/move.php',
                    array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
                // Create move icon, so it can be used.
                $movetofirsticon = html_writer::empty_tag('img',
                    array('src' => $this->output->pix_url('movehere'),
                        'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                        'title' => get_string('movehere')));
                $moveurl = html_writer::link($moveurl, $movetofirsticon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
            }

            foreach ($courses as $key => $course) {
                // If moving course, then don't show course which needs to be moved.
                if ($ismovingcourse && ($course->id == $movingcourseid)) {
                    continue;
                }
                $html .= $this->output->box_start('coursebox', "course-{$course->id}");
                $html .= html_writer::start_tag('div', array('class' => 'course_title'));
                // If user is editing, then add move icons.
                if ($userediting && !$ismovingcourse) {
                    $moveicon = html_writer::empty_tag('img',
                        array('src' => $this->pix_url('t/move')->out(false),
                            'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                            'title' => get_string('move')));
                    $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
                    $moveurl = html_writer::link($moveurl, $moveicon);
                    $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));

                }

                // No need to pass title through s() here as it will be done automatically by html_writer.
                $attributes = array('title' => $course->fullname);
                if ($course->id > 0) {
                    if (empty($course->visible)) {
                        $attributes['class'] = 'dimmed';
                    }
                    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                    $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
                    $link = html_writer::link($courseurl, $coursefullname, $attributes);
                    $html .= $this->output->heading('<i class="fa fa-university"></i>'.$link, 2, 'title');

                    // Syllabus link + icon (top left)
                    /*
                    global $USER;
                    $syllabuslink = html_writer::link(new moodle_url('/ws/levinsky/get_syllabus.php',
                        array('courseid' => $course->id, 'userid' => $USER->id)), get_string('syllabus', 'theme_essential'),
                        array('target' => '_new', 'class' => 'link'));
                    $html .= html_writer::tag('div', '<i class="fa fa-graduation-cap"></i>'.$syllabuslink, array('id'=>'syllabus'));
                    */
                } else {
                    $html .= $this->output->heading(html_writer::link(
                            new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                            format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
                }

                /*
                $html .= html_writer::tag('div', '<i class="fa fa-calendar"></i>'.get_string('timetabletitle', 'theme_essential'),
                    array('id'=>'timetableview', 'class'=>'tt'.$course->id));
                $html .= html_writer::script("Y.one('#timetableview.tt$course->id').on('click', function(){
                                        Y.one('#timetabletitle.ttt$course->id').toggleClass('tttshow');
                                        Y.one('#timetable.tt$course->id').toggleClass('ttshow')
                                        })");
                */

                /*
                // Display group(s) info. (Galit)
                $coursegroups = groups_get_all_groups($course->id);
                $meshotaf = '';
                $grouplist = '';
                if (count($coursegroups) > 1) {
                    $meshotaf = ' משותף לכל הקבוצות / ';
                    $grouplist = ' קבוצות: ';
                    foreach ($coursegroups as $group) {
                        $grouplist .= " - ". str_replace('קבוצה',' ',$group->name);
                    }
                }
                $html .= html_writer::tag('div', $meshotaf.$grouplist, array('id'=>'groups'));
                */

                /*
                // List teachers.
                global $CFG;
                if ($course instanceof stdClass) {
                    require_once($CFG->libdir. '/coursecatlib.php');
                    $course = new course_in_list($course);
                }
                $html .= html_writer::start_tag('div', array('id' => 'teacherlist')); // #teacherlist
                if ($course->has_course_contacts() and count($course->get_course_contacts()) < 4 ) {
                    //$content .= get_string('teachers').': ';
                    $html .= html_writer::start_tag('ul', array('class' => 'teachers'));
                    $html .= html_writer::tag('li', get_string('teachers').': ');
                    $countteachers = count($course->get_course_contacts());
                    $teachercounter = 0;
                    foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                        $teachercounter++;
                        if ($teachercounter < $countteachers) {
                            $delemiter = ', ';
                        } else {
                            $delemiter = '';
                        }
                        $name = html_writer::link(new moodle_url('/user/view.php',
                            array('id' => $userid, 'course' => SITEID)), $coursecontact['username'].$delemiter);
                        $html .= html_writer::tag('li', $name);
                    }
                    $html .= html_writer::end_tag('ul'); // .teacher
                }
                $html .= html_writer::end_tag('div'); // #teacherlist
                */

                /*
                // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
                $html .= html_writer::start_tag('div', array('class' => 'moreinfo'));
                //if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
                    if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
                        $url = new moodle_url('/course/info.php', array('id' => $course->id));
                        $image = html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/info'),
                            'alt' => get_string('summary')));
                        $strmoreinfo = get_string('moreinfo', 'theme_essential');
                        $html .= html_writer::link($url, $image.$strmoreinfo, array('title' => get_string('summary')));
                        // Make sure JS file to expand course content is included.
                        //$this->coursecat_include_js();
                    }
                //}
                $html .= html_writer::end_tag('div'); // .moreinfo
                */

                /*
                // Display course timetable (using course's teacher) in realtime, from Michlol database.
                //$course_users = $course->get_course_contacts();
                //$teacher = array_shift($course_users);
                global $USER;
                $specialmsg = 'מועדי המפגשים בקורסים המשותפים הם מפגשים לכל הקבוצות בקורס. אם אינכם יודעים מהו מועד המפגש של הקבוצה שלך ראה ב"מערכת מידע לסטודנט"';
                $specialmsghtml = html_writer::tag('span', $specialmsg, array('id'=>'timetablemsg', 'style'=>'color:red;'));

                $html .= html_writer::tag('div', get_string('timetabletitle', 'theme_essential').'-'.$specialmsghtml,
                    array('id'=>'timetabletitle','class'=>'ttt'.$course->id));
                $timetable = $this->get_course_timetable($course, $USER->username );
                $html .= html_writer::tag('div', $timetable);
                */

                $html .= $this->output->box('', 'flush');
                $html .= html_writer::end_tag('div');

                if (!empty($config->showchildren) && ($course->id > 0)) {
                    // List children here.
                    if ($children = block_course_overview_get_child_shortnames($course->id)) {
                        $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                    }
                }

                // If user is moving courses, then down't show overview.
                if (isset($overviews[$course->id]) && !$ismovingcourse) {
                    $html .= $this->activity_display($course->id, $overviews[$course->id]);
                }

                if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
                    // List category parent or categories path here.
                    $currentcategory = coursecat::get($course->category, IGNORE_MISSING);
                    if ($currentcategory !== null) {
                        $html .= html_writer::start_tag('div', array('class' => 'categorypath'));
                        if ($config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                            foreach ($currentcategory->get_parents() as $categoryid) {
                                $category = coursecat::get($categoryid, IGNORE_MISSING);
                                if ($category !== null) {
                                    $html .= $category->get_formatted_name().' / ';
                                }
                            }
                        }
                        $html .= $currentcategory->get_formatted_name();
                        $html .= html_writer::end_tag('div');
                    }
                }

                $html .= $this->output->box('', 'flush');
                $html .= $this->output->box_end();
                $courseordernumber++;
                if ($ismovingcourse) {
                    $moveurl = new moodle_url('/blocks/course_overview/move.php',
                        array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
                    $a = new stdClass();
                    $a->movingcoursename = $courses[$movingcourseid]->fullname;
                    $a->currentcoursename = $course->fullname;
                    $movehereicon = html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('movehere'),
                            'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                            'title' => get_string('movehere')));
                    $moveurl = html_writer::link($moveurl, $movehereicon);
                    $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
                }
            }
            // Wrap course list in a div and return.
            return html_writer::tag('div', $html, array('class' => 'course_list'));
        }

        private function get_course_timetable($course, $teacheruserid) {
            //global $USER;
            global $CFG;

            return '.';

            // todo: return to real user
            //$sql_krsidnumber='תשעד_א_8742029_1212';
            $sql_krsidnumber=$course->idnumber;
            //$sql_usridnumber = 301653259;
            $sql_usridnumber = $teacheruserid; //$USER->idnumber;
            //$sql_allornum=3; // 0 = all sessions , num = from current date, retrieve num of sessions.
            $sql_allornum=5;

            $conn = mssql_connect($CFG->kobi_ws_host, $CFG->kobi_ws_dbuser, $CFG->kobi_ws_dbpass);
            mssql_select_db( "formoodle2", $conn );
            $stmt = mssql_init("spmifgashim",$conn);
            mssql_bind($stmt, "@krsidnumber", $sql_krsidnumber, SQLVARCHAR, FALSE, FALSE, 30);
            mssql_bind($stmt, "@usridnumber", $sql_usridnumber,  SQLINT4);
            mssql_bind($stmt, "@AllOrNum", $sql_allornum,  SQLINT4);


            $result = mssql_execute($stmt);

            #$result = mssql_free_statement($stmt);

            $columnnames = array(
                'dt'=> get_string('date'),
                'yom'=> get_string('day'),
                'shaot'=> 'שעות', //get_string('times'),
                'more'=> 'מרצה', //get_string('teacher'),
                'hdr' => 'חדר', // get_string('room'),
                'cancel'=> 'האם בוטל?' //get_string('cancelled')
            );

            if (!$result)
            {
                $message = 'ERROR: ' . mssql_get_last_message();
                return $message;
            } else {
                $i = 0;
                $htmloutput = '<table id="timetable" class="tt'.$course->id.'" border=0><thead><tr>';
                while ($i < mssql_num_fields($result)) {
                    $meta = mssql_fetch_field($result, $i);
                    if ($i != 3) // Do not show "teacher name" column.
                        $htmloutput .= '<th>' . $columnnames[$meta->name] . '</th>';
                    $i = $i + 1;
                }
                $htmloutput .= '</tr></thead>';

                /*
                          // Get next (single) session.
                          $row = mssql_fetch_row($result);
                          $htmloutput .= '<tr>';
                          $c_row = current($row);
                          $htmloutput .= '<td>' . $c_row . '</td>';
                          $htmloutput .= '</tr>';
                */
//
                // Get all sessions.
                $sessions = 0;
                while ( ($row = mssql_fetch_row($result)) and $sessions++ < 6) { // todo: disable 5 sessions limit
                    // Show only 4 future sessions. "more..." link to see all future sessions
                    $count = count($row);
                    $y = 0;
                    $htmloutput .= '<tr>';
                    while ($y < $count) {
                        $c_row = current($row);
                        if ($y != 3) // Do not show "teacher name" column.
                            $htmloutput .= '<td>' . $c_row . '</td>';
                        next($row);
                        $y = $y + 1;
                    }
                    $htmloutput .= '</tr>';
                }
//
                mssql_free_result($result);

                $htmloutput .= '</table>';
            }

            if ($sessions == 0)
                $htmloutput = html_writer::div(get_string('noclasses', 'theme_essential'), 'alert tt'.$course->id ,array('id'=>'timetable'));

            //echo count($row);die;
            // No timetable for this course and teacher.
            //if (count($row) < 6) $htmloutput = '';

            mssql_close($conn); // close connection
            return $htmloutput;

        }
    }
}