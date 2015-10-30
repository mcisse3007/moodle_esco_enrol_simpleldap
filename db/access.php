<?php

/**
 * Capabilities for LDAP simple enrolment plugin.
 *
 * @package    enrol
 * @subpackage simpleldap
 * @author     Aurore Weber
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'enrol/simpleldap:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        )
    ),

);


