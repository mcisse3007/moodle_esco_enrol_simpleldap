<?php

/**
 * Manual user enrolment UI.
 *
 * @package    enrol
 * @subpackage manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/enrol/simpleldap/managelib.php');
require_once($CFG->dirroot.'/enrol/simpleldap/locallib.php');
require_once($CFG->dirroot.'/enrol/simpleldap/ldapconn.php');

$enrolid      = required_param('enrolid', PARAM_INT);
$roleid       = optional_param('roleid', 5, PARAM_INT);
$extendperiod = optional_param('extendperiod', 0, PARAM_INT);
$extendbase   = optional_param('extendbase', 3, PARAM_INT);

//Values selected by the user 
/*$codes = array();
$codes[1] = optional_param('code1', '', PARAM_TEXT);
$codes[2] = optional_param('code2', '', PARAM_TEXT);
$codes[3] = optional_param('code3', '', PARAM_TEXT);
$codes[4] = optional_param('code4', '', PARAM_TEXT);
$codes[5] = optional_param('code5', '', PARAM_TEXT);*/

$code1 = optional_param('code1', '', PARAM_TEXT);
$code2 = optional_param('code2', '', PARAM_TEXT);
$code3 = optional_param('code3', '', PARAM_TEXT);
$code4 = optional_param('code4', '', PARAM_TEXT);
$code5 = optional_param('code5', '', PARAM_TEXT);

$search = optional_param('search', false, PARAM_BOOL);
$searchZoneShown = optional_param('showSearchZone', false, PARAM_BOOL);
$showSearchZone = $search || $searchZoneShown;


$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'simpleldap'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/manual:enrol', $context);
require_capability('enrol/manual:manage', $context);
require_capability('enrol/manual:unenrol', $context);

function post_process1($liste){
    return $liste;
}
function post_process2($liste){
    return $liste;
}
function post_process3($liste){
    
    $listCorrigee = array();
    foreach ($liste as $key=>$value) {
        ////////////////////////////////////////////////
        // MODIFICATION RECIA | DEBUT | 2013-03-14
        ////////////////////////////////////////////////
        // Ancien code :
        //$index = strrpos($value, '_', -1) +1;

        // Nouveau code :
        $index = strrpos($value, '$', -1) +1;
        ////////////////////////////////////////////////
        // MODIFICATION RECIA | FIN
        ////////////////////////////////////////////////

        $listCorrigee[$key] = substr ( $value , $index) ;
    }

    return $listCorrigee;
}
function post_process4($liste){
    ////////////////////////////////////////////////
    // MODIFICATION RECIA | DEBUT | 2013-03-14
    ////////////////////////////////////////////////
    // Ancien code :
    /*
        $listCorrigee = array();
        foreach ($liste as $key=>$value) {
            $index = strrpos($value, '_', -1) +1;
            $listCorrigee[$key] = substr ( $value , $index) ;
        }

        return $listCorrigee;
    */

    // Nouveau code :
    return post_process3($liste);
    ////////////////////////////////////////////////
    // MODIFICATION RECIA | FIN
    ////////////////////////////////////////////////
}
function post_process5($liste){
    ////////////////////////////////////////////////
    // MODIFICATION RECIA | DEBUT | 2013-03-14
    ////////////////////////////////////////////////
    // Ancien code :
    //return $liste;

    // Nouveau code :
    $listCorrigee = array();
    foreach ($liste as $key=>$value) {
        $valueCorrigee = $value;
		for($j = 0; $j < 3; $j++ ) {
		        $index = strpos($valueCorrigee, ':') + 1;
		        $valueCorrigee = substr ( $valueCorrigee , $index) ;
		    }
		$valueIndesirable1 = preg_match( '/^Parents/', $valueCorrigee );
		$valueIndesirable2 = preg_match( '/^Groupes_Parents/', $valueCorrigee );

		if( !$valueIndesirable1 && !$valueIndesirable2 ) {
		    	$listCorrigee[$key] = $valueCorrigee;
		}
        
    }

    return $listCorrigee;
    ////////////////////////////////////////////////
    // MODIFICATION RECIA | FIN
    ////////////////////////////////////////////////
}

function pre_process1($filtre){
    return $filtre;
}
function pre_process2($filtre){
    return $filtre;
}
function pre_process3($filtre){
     ////////////////////////////////////////////////
     // MODIFICATION RECIA | DEBUT | 2013-03-14
     ////////////////////////////////////////////////
     // Ancien code :
     /*
         if (strrpos($filtre, "[ELEVE]") !== false){
            return str_replace (  "[ELEVE]" , "Eleves", $filtre );
         } else if (strrpos($filtre, "[ENS]") !== false) {
            return str_replace ( "[ENS]" , "Profs", $filtre );
         } else {
             return "";
         }
      */

     // Nouveau code :
     return $filtre;
     ////////////////////////////////////////////////
     // MODIFICATION RECIA | FIN
     ////////////////////////////////////////////////
}

function pre_process4($filtre){
    return $filtre;
}
function pre_process5($filtre){
    return $filtre;
}


if ($roleid < 0) {
    $roleid = $instance->roleid;
}
$roles = get_assignable_roles($context);
$roles = array('0'=>get_string('none')) + $roles;

if (!isset($roles[$roleid])) {
    // weird - security always first!
    $roleid = 0;
}

if (!$enrol_simpleldap = enrol_get_plugin('simpleldap')) {
    throw new coding_exception('Can not instantiate enrol_simpleldap');
}

$instancename = $enrol_simpleldap->get_instance_name($instance);

$PAGE->set_url('/enrol/simpleldap/manage.php', array('enrolid'=>$instance->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title($enrol_simpleldap->get_instance_name($instance));
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/enrol/users.php', array('id'=>$course->id)));


$ldapconn = new ldapconn($enrol_simpleldap);
$ldapselector = new enrol_simpleldap_ldapsearch($enrol_simpleldap,$ldapconn);

$listWithEmptyValue = array();
$listWithEmptyValue['']='';

$nb_filter = 5; // TODO EXternaliser pour une meilleure maintenabilité

//Get the default values for the user
// OLD BUT GOLD 
//$userValues = getDefaultsValuesForUser($enrol_simpleldap, $ldapselector, $nb_filter);
$username = $USER->username;
$attributes = array();
for ($i = 1; $i <= $nb_filter ; $i++){
	array_push($attributes, $enrol_simpleldap->getConfig('filter'.$i.'_default'));	
}
$userValues = $ldapselector->get_userAttribute($username,$attributes);

//Create the lists to be shown before the enrol_simpleldap_potential_participant.
// OLD 
/* $filtersLists = array();
for ($i = 1; $i <= $nb_filter ; $i++){
	$tab = createListFilter($i, $enrol_simpleldap, $ldapselector, $userValues,  $codes);
	$filtersLists[$i] = $tab[0];
	$codes[$i] = $tab[1];
} */
$list = array();
for ($i = 1; $i <= $nb_filter ; $i++){
	$attrbValue = 'filter'.$i.'_list_filter';
	$ldapFilter = $enrol_simpleldap->getConfig('filter'.$i.'_list_filter');
	for ($j = 1; $j <= $i ; $j++){
		$nomvar = 'code'.$j;
		if ($$nomvar != ''){
			$ldapFilter = str_replace("{CODE".$j."}", $$nomvar, $ldapFilter);
		}
	}
	$nomFonction = 'pre_process'.$i;
	$ldapFilter = call_user_func($nomFonction, $ldapFilter);
	$list[$i] = $ldapselector->get_listFilter($i,$ldapFilter);

	//Post traitement sur les résultat provenant du LDAP.
	$nomFonction = 'post_process'.$i;
	
	$myListe  = call_user_func($nomFonction, $list[$i]);
	asort($myListe);
   	$list[$i] = $myListe; 

	$nomvar = 'code'.$i;
	
	if (count($list[$i]) == 0 ){
	    $$nomvar = null;
	}
    
    //If there is no value for this code, we will choose one.
	if ($$nomvar == null || $$nomvar == ''){
		
		if ($enrol_simpleldap->getConfig('filter'.$i.'_mandatory') && $$nomvar == ''){
			$$nomvar = array_shift(array_keys($list[$i]));
		}	

		$defaultAttr = $enrol_simpleldap->getConfig('filter'.$i.'_default');
		if ($defaultAttr != null) {
    		if ( $list[$i][$defaultAttr] != null ){
   				$$nomvar = $defaultAttr;
	    	} else if ( $userValues[$defaultAttr] != null ){
    			$defaultValue = $userValues[$defaultAttr];
	    		if ($list[$i][$defaultValue] != null ){
	    			$$nomvar = $defaultValue;
	    		}
	    	}
		}
	} 
	
}
// Create the user selector objects.
/* if ($showSearchZone) {
	$options = array(
		'enrolid' => $enrolid, 
		'codes' => $codes,
		'enrol_simpleldap' =>  $enrol_simpleldap,
		'ldapconn' => $ldapconn,
		'nbFilter' => 5
	);
	$potentialuserselector = new enrol_simpleldap_potential_participant('addselect', $options);
	$currentuserselector = new enrol_simpleldap_current_participant('removeselect', $enrolid);
} */ 

///////////////////////////////////////////////
// MODIFICATION RECIA | DEBUT | 2013-03-14
////////////////////////////////////////////////
// Ancien code :
//$options = array('enrolid' => $enrolid, 'code1' => $code1, 'code2' => $code2, 'code3' => $code3, 'code4' => $code4);

// Nouveau code :
$options = array('enrolid' => $enrolid, 'code1' => $code1, 'code2' => $code2, 'code3' => $code3, 'code4' => $code4, 'code5' => $code5);
////////////////////////////////////////////////
// MODIFICATION RECIA | FIN
////////////////////////////////////////////////

$potentialuserselector = new enrol_simpleldap_potential_participant('addselect', $options, $enrol_simpleldap, $ldapconn);
$currentuserselector = new enrol_simpleldap_current_participant('removeselect', $options);

// Build the list of options for the enrolment period dropdown.
$unlimitedperiod = get_string('unlimited');
$periodmenu = array();
for ($i=1; $i<=365; $i++) {
    $seconds = $i * 86400;
    $periodmenu[$seconds] = get_string('numdays', '', $i);
}

// Work out the apropriate default setting.
if ($extendperiod) {
    $defaultperiod = $extendperiod;
} else {
    $defaultperiod = $instance->enrolperiod;
}

// Build the list of options for the starting from dropdown.
$timeformat = get_string('strftimedatefullshort');
$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

// enrolment start
$basemenu = array();
if ($course->startdate > 0) {
    $basemenu[2] = get_string('coursestart') . ' (' . userdate($course->startdate, $timeformat) . ')';
}
$basemenu[3] = get_string('today') . ' (' . userdate($today, $timeformat) . ')' ;


// process add user
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach($userstoassign as $adduser) {
            switch($extendbase) {
                case 2:
                    $timestart = $course->startdate;
                    break;
                case 3:
                default:
                    $timestart = $today;
                    break;
            }

            if ($extendperiod <= 0) {
                $timeend = 0;
            } else {
                $timeend = $timestart + $extendperiod;
            }
            $enrol_simpleldap->enrol_user($instance, $adduser->id, $roleid, $timestart, $timeend);
			add_to_log($course->id, 'course', 'enrol', '../enrol/users.php?id='.$course->id, $course->id); //there should be userid somewhere!
        }

        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();

    }
}

// process remove user
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstounassign = $currentuserselector->get_selected_users();
    if (!empty($userstounassign)) {
        foreach($userstounassign as $removeuser) {
            $enrol_simpleldap->unenrol_user($instance, $removeuser->id);
			add_to_log($course->id, 'course', 'unenrol', '../enrol/users.php?id='.$course->id, $course->id); //there should be userid somewhere!
        }
        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();
    }
}


echo $OUTPUT->header();
echo $OUTPUT->heading($instancename);


?>
<form id="assignform" method="post" class="mform" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <div class="enroloptions">

    <p><label for="roleid"><?php print_string('assignrole', 'enrol_manual') ?></label>
    <?php echo html_writer::select($roles, 'roleid', $roleid, false); ?>

    <label for="extendperiod"><?php print_string('enrolperiod', 'enrol') ?></label>
    <?php echo html_writer::select($periodmenu, 'extendperiod', $defaultperiod, $unlimitedperiod); ?>

    <label for="extendbase"><?php print_string('startingfrom') ?></label>
    <?php echo html_writer::select($basemenu, 'extendbase', $extendbase, false); ?></p>
    
    <?php 
	for ($i = 1; $i <= $nb_filter ; $i++){
		$nomvar = 'code'.$i;
		$label = $enrol_simpleldap->getConfig('filter'.$i.'_label');
		if ($label != '') {
  	?>
		      <div class="fitem fitem_fselect">
		        <div class="fitemtitle">
		          <label><?php echo $label; ?> </label>
		        </div>
		        <div class="felement fselect">
		        <?php 
		        $selectListe =  $list[$i];
		        if (count($selectListe) == 0 ){
		          print_string('no_available_value', 'enrol_simpleldapens');
		        } else {
		          echo html_writer::select($selectListe, 'code'.$i, $$nomvar, $enrol_simpleldap->getConfig('filter'.$i.'_mandatory') ? false : array(''=>'Tous')  ); 

		          //On force le rafraichissement de la zone de recherche si on a modifié l'établissement
		          if ($i == 1){
		          ?>
		          <script>
		          var code = document.getElementById('menucode<?php echo $i;?>');
					var myform = document.getElementById('assignform');
		          code.onchange = function(){myform.submit();};
		          </script>
		        <?php } } ?>
		        </div>
		      </div>
      <?php
    }
  } ?>
  
	<div style="text-align: center; margin-bottom : 5px;">
		<input name="search" id="search" type="submit" value="<?php echo get_string('search'); ?>" title="<?php print_string('search'); ?>" /><br />
	</div>
    
          
  </div>

  
	<table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    	<tr>
      		<td id="existingcell">
          		<p><label for="removeselect"><?php print_string('enrolledusers', 'enrol'); ?></label></p>
          		<?php $currentuserselector->display() ?>
      		</td>
      		<td id="buttonscell">
        		<div id="addcontrols">
              		<input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
          		</div>

          		<div id="removecontrols">
              		<input name="remove" id="remove" type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
          		</div>
      		</td>
      		<td id="potentialcell">
          		<p><label for="addselect"><?php print_string('enrolcandidates', 'enrol'); ?></label></p>
          		<?php $potentialuserselector->display() ?>
      		</td>
    	</tr>
	</table>
	
</div></form>
<?php


echo $OUTPUT->footer();
