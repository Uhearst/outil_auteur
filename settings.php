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
 * Settings for udeh_authoring
 *
 * @package    format_udehauthoring
 * @copyright  2022 SOFAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG, $ADMIN, $PAGE;

require_once($CFG->dirroot. '/course/format/udehauthoring/settingslib.php');

if ($ADMIN->fulltree && strpos($GLOBALS['FULLME'], 'formatsettingudehauthoring')) {

    $PAGE->requires->css('/course/format/udehauthoring/authoring_tool_settings.css');
    // Course plan
    $settings->add(new admin_setting_configstoredfile('udeh_syllabusheaderlogo', 'Logo de l’entête du plan de cours', '', 'syllabusheaderlogo'));

    // Teaching Units
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_unit_0',
        'Unité de cours',
        'Unité de cours',
        null));

    // Instructions
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionscoursegeneralinformations',
        'Instructions pour les informations générales de cours',
        'Instructions pour les informations générales de cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionscourseteachingobjectives',
        'Instructions pour les objectifs du cours',
        'Instructions pour les objectifs du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionscoursesections',
        'Instructions pour les modules du cours',
        'Instructions pour les modules du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionscourseevaluations',
        'Instructions pour les évaluations du cours',
        'Instructions pour les évaluations du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionssections',
        'Instructions pour le module',
        'Instructions pour le module',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionssubquestion',
        'Instructions pour la trame',
        'Instructions pour la trame',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionsevaluation',
        'Instructions pour l\'évaluation',
        'Instructions pour l\'évaluation',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_instructionsglobalevaluations',
        'Instructions pour les évaluations globales',
        'Instructions pour les évaluations globales',
        null));

    // Course plan
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursetitle_help',
        'Aide titre du cours',
        'Texte affiché dans l\'infobulle titre du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursequestion_help',
        'Aide question du cours',
        'Texte affiché dans l\'infobulle question du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseunit_help',
        'Aide unité du cours',
        'Texte affiché dans l\'infobulle unité du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursecode_help',
        'Aide code du cours',
        'Texte affiché dans l\'infobulle code du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursebloc_help',
        'Aide bloc du cours',
        'Texte affiché dans l\'infobulle bloc du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursecredit_help',
        'Aide crédit du cours',
        'Texte affiché dans l\'infobulle crédit du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteachername_help',
        'Aide nom du professeur enseignant',
        'Texte affiché dans l\'infobulle du nom du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteacheremail_help',
        'Aide email du professeur enseignant',
        'Texte affiché dans l\'infobulle email du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteacherphone_help',
        'Aide téléphone du professeur enseignant',
        'Texte affiché dans l\'infobulle téléphone du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteachercellphone_help',
        'Aide téléphone portable du professeur enseignant',
        'Texte affiché dans l\'infobulle téléphone portable du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteachercontacthours_help',
        'Aide horaire de contact du professeur enseignant',
        'Texte affiché dans l\'infobulle horaire de contact du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseteacherzoomlink_help',
        'Aide lien zoom du professeur enseignant',
        'Texte affiché dans l\'infobulle lien zoom du professeur',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursezoomlink_help',
        'Aide lien zoom du professeur enseignant',
        'Texte affiché dans l\'infobulle lien zoom du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursedescription_help',
        'Aide description du cours',
        'Texte affiché dans l\'infobulle description du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseintroductionembed_help',
        'Aide fichier d\'introduction \'embed\' du cours',
        'Texte affiché dans l\'infobulle introduction \'embed\' du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseintroduction_help',
        'Aide fichier d\'introduction du cours',
        'Texte affiché dans l\'infobulle introduction du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseplanvignette_help',
        'Aide vignette du cours',
        'Texte affiché dans l\'infobulle vignette du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseproblematic_help',
        'Aide problématique du cours',
        'Texte affiché dans l\'infobulle problématique du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseplace_help',
        'Aide place du cours',
        'Texte affiché dans l\'infobulle place du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_coursemethod_help',
        'Aide méthode pédagogique du cours',
        'Texte affiché dans l\'infobulle méthode pédagogique du cours',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courseannex_help',
        'Aide informations additionnelles du cours',
        'Texte affiché dans l\'infobulle informations additionnelles du cours',
        null));

    //Objectives
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_teachingobjective_help',
        'Aide objectif d\'enseignement du cours',
        'Texte affiché dans l\'infobulle objectif d\'enseignement.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_learningobjective_help',
        'Aide objectif d\'apprentissage de l\'objectif d\'enseigenement',
        'Texte affiché dans l\'infobulle objectif d\'apprentissage de l\'objectif d\'enseigenement.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_courselearningobjectivescompetencytype_help',
        'Aide compétence de l\'apprentissage',
        'Texte affiché dans l\'infobulle compétence de l\'apprentissage.',
        null));

    // Modules
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectiontitle_help',
        'Aide titre du module.',
        'Texte affiché dans l\'infobulle titre du module.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectionquestion_help',
        'Aide question du module.',
        'Texte affiché dans l\'infobulle question du module.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectiondescription_help',
        'Aide description du module.',
        'Texte affiché dans l\'infobulle description du module.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectionintroduction_help',
        'Aide fichier d\'introduction du module.',
        'Texte affiché dans l\'infobulle fichier d\'introduction.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectionintroductionembed_help',
        'Aide fichier d\'introduction embed du module.',
        'Texte affiché dans l\'infobulle fichier embed \'introduction.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectionintroductiontext_help',
        'Aide texte d\'introduction du module.',
        'Texte affiché dans l\'infobulle texte d\'introduction.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_sectionimage_help',
        'Aide image du module.',
        'Texte affiché dans l\'infobulle image du module.',
        null));

    // Evaluations
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationtitle_help',
        'Aide titre de l\'évaluation.',
        'Texte affiché dans l\'infobulle titre de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationdescription_help',
        'Aide description de l\'évaluation.',
        'Texte affiché dans l\'infobulle description de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationweight_help',
        'Aide pondération de l\'évaluation.',
        'Texte affiché dans l\'infobulle pondération de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationlearningobjective_help',
        'Aide objectif d\'apprentissage de l\'évaluation.',
        'Texte affiché dans l\'infobulle objectif d\'apprentissage de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationassociatedmodule_help',
        'Aide module associé de l\'évaluation.',
        'Texte affiché dans l\'infobulle module associé de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationintroduction_help',
        'Aide fichier d\'introduction de l\'évaluation.',
        'Texte affiché dans l\'infobulle fichier d\'introduction de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationintroductionembed_help',
        'Aide fichier d\'introduction embed de l\'évaluation.',
        'Texte affiché dans l\'infobulle fichier d\'introduction embed de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationfiles_help',
        'Aide fichiers associés à l\'évaluation.',
        'Texte affiché dans l\'infobulle fichiers associés à l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationfulldescription_help',
        'Aide description complète de l\'évaluation.',
        'Texte affiché dans l\'infobulle description complète de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationinstructions_help',
        'Aide consignes de l\'évaluation.',
        'Texte affiché dans l\'infobulle consignes de l\'évaluation.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_evaluationcriteria_help',
        'Aide critères de l\'évaluation.',
        'Texte affiché dans l\'infobulle critères de l\'évaluation.',
        null));

    // Trames
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_subquestiontitle_help',
        'Aide titre de la trame.',
        'Texte affiché dans l\'infobulle titre de la trame.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_subquestionenonce_help',
        'Aide énoncé de la trame.',
        'Texte affiché dans l\'infobulle énoncé de la trame.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_subquestionvignette_help',
        'Aide image de la trame.',
        'Texte affiché dans l\'infobulle image de la trame.',
        null));


    // Acitvités
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationtitle_help',
        'Aide titre de l\'activité.',
        'Texte affiché dans l\'infobulle titre de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationquestion_help',
        'Aide question de l\'activité.',
        'Texte affiché dans l\'infobulle question de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationactivitytype_help',
        'Aide type de l\'activité.',
        'Texte affiché dans l\'infobulle type de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationtemporality_help',
        'Aide temporailté de l\'activité.',
        'Texte affiché dans l\'infobulle temporailté de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationlength_help',
        'Aide durée de l\'activité.',
        'Texte affiché dans l\'infobulle durée de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationlocation_help',
        'Aide lieu de l\'activité.',
        'Texte affiché dans l\'infobulle lieu de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationgrouping_help',
        'Aide groupement de l\'activité.',
        'Texte affiché dans l\'infobulle groupement de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationinstructions_help',
        'Aide consignes de l\'activité.',
        'Texte affiché dans l\'infobulle consignes de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationevaluationtype_help',
        'Aide type d\'évaluation de l\'activité.',
        'Texte affiché dans l\'infobulle type d\'évaluation de l\'activité.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationmarked_help',
        'Aide d\'évaluation est elle notée.',
        'Texte affiché dans l\'infobulle évaluation est elle notée.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_explorationmedia_help',
        'Aide image de l\'activité.',
        'Texte affiché dans l\'infobulle image de l\'activité.',
        null));

    // Ressources
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_resourcetitle_help',
        'Aide titre de la ressource.',
        'Texte affiché dans l\'infobulle titre de la ressource.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_resourceexternallink_help',
        'Aide lien externe de la ressource.',
        'Texte affiché dans l\'infobulle lien externe de la ressource.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_resourcevignette_help',
        'Aide image de la ressource.',
        'Texte affiché dans l\'infobulle image de la ressource.',
        null));

    // Tools
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_toolgroup_help',
        'Aide Génération outil.',
        'Texte affiché dans l\'infobulle génération outil.',
        null));
    $settings->add(new format_udehauthoring_admin_setting_tooltipcontent('udeh_toolurlgroup_help',
        'Aide url de l\'outil.',
        'Texte affiché dans l\'infobulle url de l\'outil.',
        null));

    $PAGE->requires->js_call_amd('format_udehauthoring/helperSettings', 'init');
}
