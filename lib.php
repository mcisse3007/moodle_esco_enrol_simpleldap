<?php

/**
 * Simple LDAP enrolment plugin implementation.
 *
 * This plugin synchronises enrolment and roles with a LDAP server.
 *
 * @package    enrol
 * @subpackage simpleldap
 * @author     Aurore Weber - based on code by Iñaki Arenaza, Martin Dougiamas, Martin Langhoff and others
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @copyright  2010 Iñaki Arenaza <iarenaza@eps.mondragon.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_simpleldap_plugin extends enrol_plugin {
    protected $enrol_localcoursefield = 'idnumber';
    protected $enroltype = 'enrol_simpleldap';
    protected $errorlogtag = '[ENROL SIMPLE LDAP] ';

    
    /**
     * Constructor for the plugin. In addition to calling the parent
     * constructor, we define and 'fix' some settings depending on the
     * real settings the admin defined.
     */
    public function __construct() {
        global $CFG;
        require_once($CFG->libdir.'/ldaplib.php');

        // Do our own stuff to fix the config (it's easier to do it
        // here than using the admin settings infrastructure). We
        // don't call $this->set_config() for any of the 'fixups'
        // (except the objectclass, as it's critical) because the user
        // didn't specify any values and relied on the default values
        // defined for the user type she chose.
        $this->load_config();

        // Make sure we get sane defaults for critical values.
        $this->config->ldapencoding = $this->get_config('ldapencoding', 'utf-8');

    }
    
    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;

        $context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);
        
        //FIXME add : or !has_capability('enrol/simpleldap:config', $context)
        if (!has_capability('moodle/course:enrolconfig', $context) )     {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'simpleldap'))) {
            return NULL;
        }
        return new moodle_url('/enrol/simpleldap/addinstance.php', array('sesskey'=>sesskey(),'id'=>$courseid));
    }
    
    /**
     * Add new instance of enrol plugin with default settings.
     * @param object $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = array();

        return $this->add_instance($course, $fields);
    }
    
    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        $fields = (array)$fields;

        return parent::add_instance($course, $fields);
    }

    /**
     * Returns a button to manually enrol users through the manual enrolment plugin.
     *
     * By default the first manual enrolment plugin instance available in the course is used.
     * If no manual enrolment instances exist within the course then false is returned.
     *
     * This function also adds a quickenrolment JS ui to the page so that users can be enrolled
     * via AJAX.
     *
     * @param course_enrolment_manager $manager
     * @return enrol_user_button
     */
    public function get_manual_enrol_button(course_enrolment_manager $manager) {

        $course = $manager->get_course();
        
        $instance = null;
        foreach ($manager->get_enrolment_instances() as $tempinstance) {
            if ($tempinstance->enrol == 'simpleldap') {
                if ($instance === null) {
                    $instance = $tempinstance;
                }
            }
        }
        if (empty($instance)) {
            return false;
        }
        
        $simpleldapurl = new moodle_url('/enrol/simpleldap/manage.php', array('id' => $course->id, 'enrolid'=> $instance->id));
        $button = new enrol_user_button($simpleldapurl, get_string('enrolusers', 'enrol_simpleldap'), 'get');
        $button->class .= ' enrol_simpleldap_plugin';

        
        return $button;
    }
   
    public function getConfig($attr){    
        return $this->config->$attr;
    }



    public function can_hide_show_instance($instance) {
        return true;
    }
    
    public function can_delete_instance($instance) {
    	return true;
    }

    
}

