<?php

/**
 * Strings for component 'enrol_simpleldap', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    enrol
 * @subpackage simpleldap
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Inscription Annuaire ENT';
$string['pluginname_desc'] = '<p>Le plugin d\'inscription Annuaire permet de rechercher des utilisateurs en filtrant sur les données d\'un annuaire LDAP.</p>';

$string['main_search'] = 'Recherche des utilisateurs';
$string['branch'] = 'Contexte';
$string['branch_desc'] = 'Le contexte de la recherche pour les utilisateurs.';
$string['username_attribute'] = 'Identifiant utilisateur';
$string['username_attribute_desc'] = 'L\'attribut LDAP qui correspond au login de l\'utilisateur.';
$string['default_filter'] = 'Filtre par défaut';
$string['default_filter_desc'] = 'Un filtre LDAP qui sera toujours appelé. Ce filtre permet de limiter la rechercher et de cibler les utilisateurs pertinants. Par example : (actif=1).';

$string['filter1'] = 'Filtre n°1';
$string['filter2'] = 'Filtre n°2';
$string['filter3'] = 'Filtre n°3';
$string['filter4'] = 'Filtre n°4';
$string['filter5'] = 'Filtre n°5';

$string['filter_label'] = 'Libellé du filtre';
$string['filter_label_desc'] = 'Le libellé du filtre.';
$string['filter_mandatory'] = 'Filtre obligatoire';
$string['filter_mandatory_desc'] = 'Cocher la case si le filtre doit toujours contenir une valeur.';

$string['filter_list_values'] = 'Valeurs proposées';
$string['filter_list_values_desc'] = 'Les valeurs proposées ainsi que les codes associés. Les données doivent être de la forme : libellé1#filtre1;libellé2#filtre2;... Par exemple:"Enseignants#ENTAuxEnseignant;Directeurs#ENTDirecteur;Personnel non enseignant#ENTAuxNonEnsEtab"';
$string['filter_list_filter'] = 'Filtres de valeurs';
$string['filter_list_filter_desc'] = 'Le filtre utilisé pour constituer la liste des valeurs. N\'est pas utilisé lorsque l\'option précédente est remplie.';
$string['filter_list_branch'] = 'Contexte de la liste';
$string['filter_list_branch_desc'] = 'Le contexte interogé pour constituer la liste des valeurs.';
$string['filter_list_label'] = 'Attribut libellé';
$string['filter_list_label_desc'] = 'L\'attribut LDAP qui sert de libellé dans la liste.';
$string['filter_list_code'] = 'Attribut code';
$string['filter_list_code_desc'] = 'L\'attribut LDAP qui sert de code dans la liste.';
$string['filter_sub_filter'] = 'Le filtre';
$string['filter_sub_filter_desc'] = 'Le filtre associé à la séléction. Ce filtre n\'est pris en compte que s\'il existe une valeur séléctionnée.';
$string['filter_default'] = 'Code par defaut';
$string['filter_default_desc'] = 'Le filtre qui permet de determiner la valeur par défaut.';

////////////////////////////////////////////////
// MODIFICATION RECIA | DEBUT | 2013-04-23
////////////////////////////////////////////////
// Ancien code :
// $string['enrolusers'] = 'Inscrire des utilisateurs (Annuaire)';^M

// Nouveau code :
$string['enrolusers'] = 'Depuis annuaire ENT';
////////////////////////////////////////////////
// MODIFICATION RECIA | FIN
////////////////////////////////////////////////

$string['phpldap_noextension'] = '<em>The PHP LDAP module does not seem to be present. Please ensure it is installed and enabled if you want to use this enrolment plugin.</em>';

$string['pluginnotenabled'] = 'Plugin not enabled!';

