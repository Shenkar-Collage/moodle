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
 * Authentication Plugin: Manual Authentication
 * Just does a simple check against the moodle database.
 *
 * @package    auth
 * @subpackage rashim
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once(dirname(__FILE__).'/rashim.class.php');

/**
 * Manual authentication plugin.
 *
 * @package    auth
 * @subpackage manual
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_rashim extends auth_plugin_base {

    /**
     * Constructor.
     */
    function auth_plugin_rashim() {
        $this->authtype = 'rashim';
        $this->config = get_config('auth/rashim');
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        global $CFG, $DB, $USER;

        //If the user come from michlol use manual auth
        if (isset($_SERVER['HTTP_REFERER'])) {
            if ($this->rashim_sso($username, $password)) {
                return TRUE;
            }
        }

        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return false;
        }

        //$michlol = new RashimAuth($this->config->michlol_url);
	$michlol = new RashimAuth($this->config->auth_rashim_addres);

        if ($michlol->user_login(stripslashes($username), stripslashes($password))) {
            return TRUE;
        } else {
            //$this->lasterror = $michlol->last_error();
            return FALSE;
        }
    }

    public function rashim_sso($username, $password) {
        global $DB;
        if (substr($_SERVER['HTTP_REFERER'],-19) == "ws/rashim/login.php") {
            //$this->debug("\n\nReferer:".substr($_SERVER['HTTP_REFERER'],-19)."\n:".$username.":".$password.":\n");

            if ($user = $DB->get_record('user', array('username' => $username))) {
                $validate = validate_internal_user_password($user, $password);
		//print_r($user);
		//echo md5($password);	
		//die();
                //$this->debug("md5 of inserted password:".md5($password)."\nstored password:".$user->password."\nvalidate:".intval($validate)."\n");
                return $validate;
            }
        }
    }


    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $config An object containing all the data for this page.
     * @param string $error
     * @param array $user_fields
     * @return void
     */
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param array $config
     * @return void
     */
    function process_config($config) {

        if(!isset($config->auth_rashim_addres))   {
                $config->auth_rashim_addres = '';
        }
        if(!isset($config->auth_rashim_debug))   {
                    $config->auth_rashim_debug = 1;
        }
        if(!isset($config->auth_rashim_debug_to_log))   {
                    $config->auth_rashim_debug_to_log = 1;
        }
        if(!isset($config->auth_rashim_debug_log_file))   {
                    $config->auth_rashim_debug_log_file = '';
        }

        set_config('auth_rashim_addres', $config->auth_rashim_addres, 'auth/rashim');
        set_config('auth_rashim_debug', $config->auth_rashim_debug, 'auth/rashim');
        set_config('auth_rashim_debug_to_log', $config->auth_rashim_debug_to_log, 'auth/rashim');
        set_config('auth_rashim_debug_log_file', $config->auth_rashim_debug_log_file, 'auth/rashim');

        return true;
    }

   /**
    * Confirm the new user as registered. This should normally not be used,
    * but it may be necessary if the user auth_method is changed to manual
    * before the user is confirmed.
    *
    * @param string $username
    * @param string $confirmsecret
    */
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                if ($user->firstaccess == 0) {
                    $DB->set_field("user", "firstaccess", time(), array("id"=>$user->id));
                }
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }

}


