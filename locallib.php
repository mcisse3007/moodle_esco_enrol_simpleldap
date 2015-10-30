<?php

/**
 * Auxiliary manual user enrolment lib
 *
 * @package    enrol
 * @subpackage manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->libdir.'/ldaplib.php');


class enrol_simpleldap_ldapsearch {
    protected $instance;
    protected $ldapconn;    
    
    public function __construct($instance, $ldapconn){
        $this->instance = $instance;
        $this->ldapconn = $ldapconn;
    }
    
    
    public function get_listFilter($number,$ldapFilter){
        $filter = 'filter'.$number;
        $list = array();
        
        if ($this->instance->getConfig($filter.'_list_values') != ''){
            $config_values = $this->instance->getConfig($filter.'_list_values');
            $values = explode(";", $config_values);
            foreach ($values as $value){
                $val = explode("#", $value);
                $list[$val[1]] = $val[0];
            }
            
        } else { 
            
            $ldapconnection = $this->ldapconn->ldap_connect();
            if (!$ldapconnection) {
                return;
            }
            $keyAttr = $this->instance->getConfig($filter.'_list_code');
            $labelAttr = $this->instance->getConfig($filter.'_list_label');
            
            if ($ldapFilter != ''){
	            $records = $this->ldapconn->ldap_search(
	                    $this->instance->getConfig($filter.'_list_branch'),
	                    $ldapFilter,
	                    array($keyAttr,$labelAttr)              
	                    );
            } 
	            foreach ($records as $record) {
	                //array_push($flat_records, $records[$c]);
	                if ($keyAttr == "dn"){
	                    $key = $record[$keyAttr];
////////////////////////////////////////////////
                        // MODIFICATION RECIA | DEBUT | 2013-03-14
                        ////////////////////////////////////////////////
                        // Ancien code :
                        /*
	                } else {
	                    $key = $record[$keyAttr][0];
	                }
	                if ($labelAttr == "dn"){
	                    $label = $record[$labelAttr];
	                } else {
	                    $label = $record[$labelAttr][0];                    
	                }
	                $list[$key] = $label; 
						*/

                        // Nouveau code :
			} else {
    	                    for ($j = 0; $j < $record[$keyAttr]["count"] ; $j++){
	                    	$key = $record[$keyAttr][$j];
				if($labelAttr == "dn") {
					$label = $record[$labelAttr];
				} else {
	                    	    $label = $record[$labelAttr][$j];
	            }   
					$list[$key] = $label;          	
            }

 ////////////////////////////////////////////////
                        // MODIFICATION RECIA | FIN
                        ////////////////////////////////////////////////

}
}
            $this->ldapconn->ldap_close();
        }
        
        asort($list);
        return $list;
    }
    
    public function get_userAttribute($username, $attributes){
        $ldapconnection = $this->ldapconn->ldap_connect();
        if (!$ldapconnection) {
            return;
        }
        
        $ldapFilter = "(".$this->instance->getConfig('username_attribute')."=".$username.")";
        
        $records = $this->ldapconn->ldap_search(
                $this->instance->getConfig('branch'),
                $ldapFilter,
                $attributes
        );
         
        $list = array();
        foreach ($records as $record) {
            foreach ($attributes as $attribute) {
            	if (array_key_exists($attribute, $record)){
                	$list[$attribute] = $record[$attribute][0];
                }
            }
        }
        $this->ldapconn->ldap_close();
        return $list;
    }
    
    
    
}

/**
 * Enrol candidates
 */
class enrol_simpleldap_potential_participant extends user_selector_base {
    protected $enrolid;
    protected $code1;
	protected $code2;
	protected $code3;
	protected $code4;
	protected $code5;
    protected $instance;
    protected $ldapconn;
    protected $nbFilter;
    protected $potentialUsers;
    
    public function __construct($name, $options, $instance,$ldapconn) {
        
        $this->enrolid  = $options['enrolid'];
       /* if (array_key_exists('codes', $options)){
        	$this->codes  = $options['codes'];
        } */

  		$this->code1  = $options['code1'];
        $this->code2  = $options['code2'];
        $this->code3  = $options['code3'];
        $this->code4  = $options['code4'];
        $this->code5  = $options['code5'];

	
      /*  if (array_key_exists('enrol_simpleldap', $options)){
        	//$this->instance  = $options['enrol_simpleldap']; //MODIF 
			$this->instance = $instance;
        } 

        if (array_key_exists('ldapconn', $options)){
        	$this->ldapconn  = $options['ldapconn'];
        } */

		$this->instance = $instance;
        $this->ldapconn = $ldapconn;
        
       /* if (array_key_exists('nbFilter', $options)){
        	$this->nbFilter  = $options['nbFilter'];
        } */
        

       /* if (array_key_exists('potentialUsers', $options)){
			$this->potentialUsers = $options['potentialUsers'];        	
        } else {
        	$this->potentialUsers = null;
        } */

        //parent::__construct($name, array("enrolid" => $this->enrolid));
		parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
		$nbFilter = 5; // TODO : A externaliser pour une meilleure maintenabilité
        $doLDAPSearch = false;

		if ($this->code1 != '' 
    			 || $this->code2 != ''
    			 || $this->code3 != ''
    			 || $this->code4 != ''
    			 || $this->code5 != '' ){
    		$doLDAPSearch = true;
    	}
      /*  for ($i = 1 ; $i <= $this->nbFilter ; $i++){
        	if ($this->codes[$i] != '') {
        		$doLDAPSearch = true;
        	}
        } */
        
        $userList = array();
        $LDAPWhereCondition = "";
        if ($doLDAPSearch){
            
            $ldapconnection = $this->ldapconn->ldap_connect();
            if (!$ldapconnection) {
                return;
            }
            
            $filter = "(&"; 
            $filter .=  $this->instance->getConfig('default_filter');

// A MODIFIER POUR ADAPTATION
           /* for ($i = 1 ; $i <= $this->nbFilter ; $i++){
                if ($this->codes[$i] != '' ){
                    $filter .=  $this->instance->getConfig('filter'.$i.'_sub_filter');
                }
            }
            $filter .= ")";
            for ($i = 1 ; $i <= $this->nbFilter ; $i++){
                if ($this->codes[$i] != ''){
                    $filter = str_replace("{CODE".$i."}", $this->codes[$i], $filter);
                }
            } */
 // MODIF 

for ($i = 1 ; $i <= $nbFilter ; $i++){
                ////////////////////////////////////////////////
                // MODIFICATION RECIA | FIN
                ////////////////////////////////////////////////
	    		$nomvar = 'code'.$i; 
	    		if ($this->$nomvar != '' ){
	    		    if ( $this->$nomvar == "ANYOTHER"){
	    		        $filter .= "(!(|(ESCOPersonProfils=ELEVE)(ESCOPersonProfils=ENS)(ESCOPersonProfils=PERSREL)))";
	    		    } else {
    		    		$filter .=  $this->instance->getConfig('filter'.$i.'_sub_filter');
    		    	}
	    		}
    		}
    		$filter .= ")";
                ////////////////////////////////////////////////
                // MODIFICATION RECIA | DEBUT | 2013-03-14
                ////////////////////////////////////////////////
                // Ancien code :
    		// for ($i = 1 ; $i < $nbFilter ; $i++){

                // Nouveau code :
    		for ($i = 1 ; $i <= $nbFilter ; $i++){
                ////////////////////////////////////////////////
                // MODIFICATION RECIA | FIN
                ////////////////////////////////////////////////
    			$nomvar = 'code'.$i;
    			if ($this->$nomvar != ''){
    			    $valeur = $this->$nomvar;
    				$valeur = str_replace("(", "\\28", $valeur);
    				$valeur = str_replace(")", "\\29", $valeur);    			    
    				$filter = str_replace("{CODE".$i."}", $valeur, $filter);
    			}
    		}            
            $records = $this->ldapconn->ldap_search(
                    $this->instance->getConfig('branch'),
                    $filter,
                    array($this->instance->getConfig('username_attribute')));
            
            
            foreach ($records as $record) {
					array_push($userList, $record[$this->instance->getConfig('username_attribute')][0]);
                //array_push($userList, strtolower($record[$this->instance->getConfig('username_attribute')][0]));
            }
            $this->potentialUsers = $userList;

			/*$LDAPWhereCondition = "";

            if (count($userList) > 0 ){

				$LDAPWhereCondition = "AND (u.mnethostid, u.username) IN ( (1,'";
            	$LDAPWhereCondition .= implode("'),(1,'", $userList);
                $LDAPWhereCondition .= "')) ";
            } else {
            	$LDAPWhereCondition .= " AND 1=0 ";
            }*/
            
			$LDAPWhereCondition = " u.username IN ( '' ";
    		if (count($userList) > 0 ){
    			foreach ($userList as $user){
    				$LDAPWhereCondition .= ",'".$user."'";
    			}
    		}
    		$LDAPWhereCondition .= ") AND ";
            

            
            $this->ldapconn->ldap_close();
        } /*else if ($this->potentialUsers != null && count($this->potentialUsers)){
    		$LDAPWhereCondition = "";
			
    		if (count($this->potentialUsers) > 0 ){

				$LDAPWhereCondition = "AND (u.mnethostid, u.username) IN ( (1,'";
            	$LDAPWhereCondition .= implode("'),(1,'", $this->potentialUsers);
                $LDAPWhereCondition .= "')) ";
            } else {
            	$LDAPWhereCondition .= " AND 1=0 ";
            }*/
    	
        
        
        global $DB , $CFG;
        //by default wherecondition retrieves all users except the deleted, not confirmed and guest
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';
		// MODIF RECIA
       /* $sql = " FROM {user} u
        		LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition $LDAPWhereCondition
                AND ue.id IS NULL  ";*/
		$sql = " FROM {user} u
                WHERE $wherecondition AND $LDAPWhereCondition
                      u.id NOT IN (
                          SELECT ue.userid
                            FROM {user_enrolments} ue
                            JOIN {enrol} e ON (e.id = ue.enrolid AND e.id = :enrolid))";
        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

        if (!empty($CFG->maxusersperpage)) {
            $maxusersperpage = $CFG->maxusersperpage;
        } else {
	    $maxusersperpage = 100;
	}

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('enrolcandidatesmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolcandidates', 'enrol');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
       // $options['potentialUsers'] = $this->potentialUsers;
        $options['file']    = 'enrol/simpleldap/locallib.php';
        return $options;
    }
    public function display($return = false) {
        global $PAGE;

        // Get the list of requested users.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedusers = $this->find_users($search);

        // Output the select.
        $name = $this->name;
        $multiselect = '';
        if ($this->multiselect) {
            $name .= '[]';
            $multiselect = 'multiple="multiple" ';
        }
        $output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedusers, $search);

        // Output the search controls.
        $output .= "</select>\n<div>\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="' .
                $this->name . '_searchtext" size="15" value="' . s($search) . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="' .
                $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" />';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="' .
                $this->name . '_clearbutton" value="' . get_string('clear') . '" />';

        // And the search options.
        $optionsoutput = false;
        $output .= "</div>\n</div>\n\n";

        // Initialise the ajax functionality.
        //$output .= $this->initialise_javascript($search);

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    // Initialise one of the option checkboxes, either from
    // the request, or failing that from the user_preferences table, or
    // finally from the given default.
    private function initialise_option($name, $default) {
        $param = optional_param($name, null, PARAM_BOOL);
        if (is_null($param)) {
            return get_user_preferences($name, $default);
        } else {
            set_user_preference($name, $param);
            return $param;
        }
    }

    // Output one of the options checkboxes.
    private function option_checkbox($name, $on, $label) {
        if ($on) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }
        $name = 'userselector_' . $name;
        $output = '<p><input type="hidden" name="' . $name . '" value="0" />' .
                // For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
                // It seems that document.getElementById('frog') in IE will return and element with name="frog".
                '<input type="checkbox" id="' . $name . 'id" name="' . $name . '" value="1"' . $checked . ' /> ' .
                '<label for="' . $name . 'id">' . $label . "</label></p>\n";
        user_preference_allow_ajax_update($name, PARAM_BOOL);
        return $output;
    }
}

/**
 * Enroled users
 */
class enrol_simpleldap_current_participant extends user_selector_base {
    protected $courseid;
    protected $enrolid;

   /* public function __construct($name, $enrolid) {
        $this->enrolid  = $enrolid;
        parent::__construct($name, array("enrolid" => $enrolid) );
    } */

	public function __construct($name, $options) {
        $this->enrolid  = $options['enrolid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB , $CFG;
        //by default wherecondition retrieves all users except the deleted, not confirmed and guest
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';

	if (!empty($CFG->maxusersperpage)) {
            $maxusersperpage = $CFG->maxusersperpage;
        } else {
            $maxusersperpage = 100;
        }


        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/simpleldap/locallib.php';
        return $options;
    }
}
