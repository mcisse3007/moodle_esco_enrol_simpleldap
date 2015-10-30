<?php
/**
 * Simple LDAP enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage simpleldap
 * @author     Aurore Weber
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- heading ---
    $settings->add(new admin_setting_heading('enrol_simpleldap_settings', '', get_string('pluginname_desc', 'enrol_simpleldap')));

    if (!function_exists('ldap_connect')) {
        $settings->add(new admin_setting_heading('enrol_phpldap_noextension', '', get_string('phpldap_noextension', 'enrol_simpleldap')));
    } else {
        require_once($CFG->dirroot.'/enrol/simpleldap/settingslib.php');
        require_once($CFG->libdir.'/ldaplib.php');

        $yesno = array(get_string('no'), get_string('yes'));

        $settings->add(new admin_setting_heading('enrol_manual_defaults',
        		get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));
        $settings->add(new admin_setting_configcheckbox('enrol_simpleldap/defaultenrol',
        		get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));
        
        //--- connection settings ---
        $settings->add(new admin_setting_heading('enrol_simpleldap_server_settings', get_string('server_settings', 'enrol_ldap'), ''));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/host_url', get_string('host_url_key', 'enrol_ldap'), get_string('host_url', 'enrol_ldap'), ''));
        // Set LDAPv3 as the default. Nowadays all the servers support it and it gives us some real benefits.
        $options = array(3=>'3', 2=>'2');
        $settings->add(new admin_setting_configselect('enrol_simpleldap/ldap_version', get_string('version_key', 'enrol_ldap'), get_string('version', 'enrol_ldap'), 3, $options));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/ldapencoding', get_string('ldap_encoding_key', 'enrol_ldap'), get_string('ldap_encoding', 'enrol_ldap'), 'utf-8'));

        //--- binding settings ---
        $settings->add(new admin_setting_heading('enrol_simpleldap_bind_settings', get_string('bind_settings', 'enrol_ldap'), ''));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/bind_dn', get_string('bind_dn_key', 'enrol_ldap'), get_string('bind_dn', 'enrol_ldap'), ''));
        $settings->add(new admin_setting_configpasswordunmask('enrol_simpleldap/bind_pw', get_string('bind_pw_key', 'enrol_ldap'), get_string('bind_pw', 'enrol_ldap'), ''));
        
        //--- main search settings ---
        $settings->add(new admin_setting_heading('enrol_simpleldap_main_search_settings', get_string('main_search', 'enrol_simpleldap'), ''));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/branch', get_string('branch', 'enrol_simpleldap'), get_string('branch_desc', 'enrol_simpleldap'), ''));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/username_attribute', get_string('username_attribute', 'enrol_simpleldap'), get_string('username_attribute_desc', 'enrol_simpleldap'), 'uid'));
        $settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/default_filter', get_string('default_filter', 'enrol_simpleldap'), get_string('default_filter_desc', 'enrol_simpleldap'), ''));
        
        
        $nb_filter = 5;
        //--- filters -----
        for ($i = 1; $i <= $nb_filter ; $i++){
        	$settings->add(new admin_setting_heading('filter_'.$i, get_string('filter'.$i, 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_label', get_string('filter_label', 'enrol_simpleldap'), get_string('filter_label_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configcheckbox('enrol_simpleldap/filter'.$i.'_mandatory', get_string('filter_mandatory', 'enrol_simpleldap'), get_string('filter_mandatory_desc', 'enrol_simpleldap'), false));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_list_values', get_string('filter_list_values', 'enrol_simpleldap'), get_string('filter_list_values_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_list_filter', get_string('filter_list_filter', 'enrol_simpleldap'), get_string('filter_list_filter_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_list_branch', get_string('filter_list_branch', 'enrol_simpleldap'), get_string('filter_list_branch_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_list_label', get_string('filter_list_label', 'enrol_simpleldap'), get_string('filter_list_label_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_list_code', get_string('filter_list_code', 'enrol_simpleldap'), get_string('filter_list_code_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_sub_filter', get_string('filter_sub_filter', 'enrol_simpleldap'), get_string('filter_sub_filter_desc', 'enrol_simpleldap'), ''));
        	$settings->add(new admin_setting_configtext_trim_lower('enrol_simpleldap/filter'.$i.'_default', get_string('filter_default', 'enrol_simpleldap'), get_string('filter_default_desc', 'enrol_simpleldap'), ''));
        	
        }

    }
}
