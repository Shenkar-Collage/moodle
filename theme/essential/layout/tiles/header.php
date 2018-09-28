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
 * Essential is a clean and customizable theme.
 *
 * @package     theme_essential
 * @copyright   2017 Gareth J Barnard
 * @copyright   2016 Gareth J Barnard
 * @copyright   2014 Gareth J Barnard, David Bezemer
 * @copyright   2013 Julian Ridden
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(\theme_essential\toolbox::get_tile_file('pagesettings'));

// Get topmost user's role for this page (context)
// And add a user's role class to body classes.
$userrole = ' role-teacher';
$isstudent = false;
$userroles = get_user_roles($PAGE->context, $USER->id, true);
foreach ($userroles as $role) {
    if ($role->roleid == 5) $isstudent = true;
}
if ($isstudent) {
    $userrole = ' role-student';
}
if (has_capability('moodle/site:config', context_system::instance())) {
    $userrole = ' role-admin';
}
array_push($bodyclasses, $userrole);
 
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?> class="no-js">
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php
    echo $OUTPUT->standard_head_html();
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google web fonts -->
    <?php require_once(\theme_essential\toolbox::get_tile_file('fonts')); ?>
    <!-- iOS Homescreen Icons -->
    <?php require_once(\theme_essential\toolbox::get_tile_file('iosicons')); ?>
    <!-- Start Analytics -->
    <?php require_once(\theme_essential\toolbox::get_tile_file('analytics')); ?>
    <!-- End Analytics -->
</head>

<body <?php echo $OUTPUT->body_attributes($bodyclasses); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<header role="banner">
<?php
if (!$oldnavbar) {
    require_once(\theme_essential\toolbox::get_tile_file('navbar'));
}
?>
    <div id="page-header" class="clearfix<?php echo ($oldnavbar) ? ' oldnavbar' : ''; echo ($haslogo) ? ' logo' : ' nologo'; ?>">
        <div class="container-fluid">
            <div class="row-fluid">
                <!-- HEADER: LOGO AREA -->
<?php
if (!$haslogo) {
    echo '<div class="pull-left">';
    $usesiteicon = \theme_essential\toolbox::get_setting('usesiteicon');
    $headertitle = $OUTPUT->get_title('header');
    if ($usesiteicon || $headertitle) {
        echo '<a class="textlogo" href="';
        echo preg_replace("(https?:)", "", $CFG->wwwroot);
        echo '">';
        if ($usesiteicon) {
            echo '<span id="headerlogo" aria-hidden="true" class="fa fa-'.\theme_essential\toolbox::get_setting('siteicon').'"></span>';
        }
        if ($headertitle) {
            echo '<div class="titlearea">'.$headertitle.'</div>';
        }
        echo '</a>';
    }
} else {
    echo '<div class="pull-left logo-container">';
    echo '<a class="logo" href="'.preg_replace("(https?:)", "", $CFG->wwwroot).'" title="'.get_string('home').'">';
    echo '<img src="'.\theme_essential\toolbox::get_setting('logo', 'format_file_url').'" class="img-responsive" />';
    echo '</a>';
}
?>
                </div>
            <!-- Was social links, mov to footer (nadavkav) -->
            </div>
        </div>
    </div>
<?php
if ($oldnavbar) {
    require_once(\theme_essential\toolbox::get_tile_file('navbar'));
}
?>
</header>
