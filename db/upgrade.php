<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz module upgrade function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_format_udehauthoring_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2021121600.01) {
        $now = time();

        // Define field timemodified to be added to udehauthoring_course.
        $table = new xmldb_table('udehauthoring_course');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'annex');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_course} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field timemodified to be added to udehauthoring_teaching_obj.
        $table = new xmldb_table('udehauthoring_teaching_obj');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'teachingobjective');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_teaching_obj} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field timemodified to be added to udehauthoring_learning_obj.
        $table = new xmldb_table('udehauthoring_learning_obj');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'learningobjectivecompetency');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_learning_obj} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field timemodified to be added to udehauthoring_section.
        $table = new xmldb_table('udehauthoring_section');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'comments');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_section} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field id to be added to udehauthoring_evaluation.
        $table = new xmldb_table('udehauthoring_evaluation');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'weight');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_evaluation} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field id to be added to udehauthoring_sub_question.
        $table = new xmldb_table('udehauthoring_sub_question');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'learningobjectiveid');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_sub_question} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field timemodified to be added to udehauthoring_exploration.
        $table = new xmldb_table('udehauthoring_exploration');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'instructions');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_exploration} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Define field timemodified to be added to udehauthoring_resource.
        $table = new xmldb_table('udehauthoring_resource');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'link');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute("UPDATE {udehauthoring_resource} SET timemodified = '{$now}' WHERE 1");
        $field->setDefault(null);
        $dbman->change_field_default($table, $field);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2021121600.01, 'format', 'udehauthoring');
    }

    if($oldversion < 2022022301) {
        $coursetable = new xmldb_table('udehauthoring_course');
        $namefield = new xmldb_field('teachername', XMLDB_TYPE_TEXT);
        $phonefield = new xmldb_field('teacherphone', XMLDB_TYPE_TEXT);
        $cellfield = new xmldb_field('teachercellphone', XMLDB_TYPE_TEXT);
        $emailfield = new xmldb_field('teacheremail', XMLDB_TYPE_TEXT);

        if (!$dbman->field_exists($coursetable, $namefield)) {
            $dbman->add_field($coursetable, $namefield);
        }
        $namefield->setDefault(null);
        $dbman->change_field_default($coursetable, $namefield);

        if (!$dbman->field_exists($coursetable, $phonefield)) {
            $dbman->add_field($coursetable, $phonefield);
        }
        $phonefield->setDefault(null);
        $dbman->change_field_default($coursetable, $phonefield);

        if (!$dbman->field_exists($coursetable, $cellfield)) {
            $dbman->add_field($coursetable, $cellfield);
        }
        $cellfield->setDefault(null);
        $dbman->change_field_default($coursetable, $cellfield);

        if (!$dbman->field_exists($coursetable, $emailfield)) {
            $dbman->add_field($coursetable, $emailfield);
        }
        $emailfield->setDefault(null);
        $dbman->change_field_default($coursetable, $emailfield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022022301, 'format', 'udehauthoring');
    }

    if($oldversion < 2022022302) {
        $DB->execute("ALTER TABLE {udehauthoring_teaching_obj} MODIFY teachingobjective TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_learning_obj} MODIFY learningobjective TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_course} MODIFY title TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_section} MODIFY title TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_course} MODIFY code CHAR(32)");
        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022022302, 'format', 'udehauthoring');
    }

    if($oldversion < 2022022400) {
        $DB->execute("ALTER TABLE {udehauthoring_course} MODIFY code TEXT");
        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022022400, 'format', 'udehauthoring');
    }

    if($oldversion < 2022022500) {
        $DB->execute("ALTER TABLE {udehauthoring_evaluation} MODIFY weight TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_course} MODIFY teacherzoomlink TEXT");
        $DB->execute("ALTER TABLE {udehauthoring_course} MODIFY coursezoomlink TEXT");

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022022500, 'format', 'udehauthoring');
    }

    if($oldversion < 2022022505) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_evaluation_obj')) {
            $jointable = new xmldb_table('udehauthoring_evaluation_obj');

            $jointable->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $jointable->add_field('audehevaluationid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $jointable->add_field('audehlearningobjectiveid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);

            $jointable->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $jointable->add_key('audehevaluationidfk', XMLDB_KEY_FOREIGN, ['audehevaluationid'], 'udehauthoring_evaluation', ['id']);
            $jointable->add_key('audehlearningobjectiveidfk', XMLDB_KEY_FOREIGN, ['audehlearningobjectiveid'], 'udehauthoring_learning_obj', ['id']);
            $dbman->create_table($jointable);
        }

        // Define field timemodified to be added to udehauthoring_evaluation_obj.
        $currenttable = new xmldb_table('udehauthoring_evaluation_obj');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'audehlearningobjectiveid');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($currenttable, $timefield)) {
            $dbman->add_field($currenttable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_evaluation_obj} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($currenttable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022022505, 'format', 'udehauthoring');
    }

    if($oldversion < 2022033100) {
        $evalobjtable = new xmldb_table('udehauthoring_evaluation_obj');
        $courseidfield = new xmldb_field('audehcourseid', XMLDB_TYPE_INTEGER, 10, XMLDB_NOTNULL, XMLDB_NOTNULL);

        if (!$dbman->field_exists($evalobjtable, $courseidfield)) {
            $dbman->add_field($evalobjtable, $courseidfield);
            $evalobjtable->add_key('audehcourseidfk', XMLDB_KEY_FOREIGN, ['audehcourseid'], 'udehauthoring_course', ['id']);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022033100, 'format', 'udehauthoring');
    }

    if($oldversion < 2022040700) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_unit')) {
            $unittable = new xmldb_table('udehauthoring_unit');

            $unittable->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $unittable->add_field('audehunitid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $unittable->add_field('audehcourseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);

            $unittable->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $unittable->add_key('audehunitidfk', XMLDB_KEY_FOREIGN, ['audehunitid'], 'config', ['id']);
            $unittable->add_key('audehcourseidfk', XMLDB_KEY_FOREIGN, ['audehcourseid'], 'udehauthoring_course', ['id']);
            $dbman->create_table($unittable);
        }

        $table = new xmldb_table('udehauthoring_course');
        $field = new xmldb_field('unit');

        // Conditionally launch drop field fileid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field timemodified to be added to udehauthoring_evaluation_obj.
        $currentunittable = new xmldb_table('udehauthoring_unit');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'audehcourseid');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($currentunittable, $timefield)) {
            $dbman->add_field($currentunittable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_unit} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($currentunittable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022040700, 'format', 'udehauthoring');
    }

    if($oldversion < 2022041400) {

        // Define field embed to be added to udehauthoring_course.
        $coursetable = new xmldb_table('udehauthoring_course');
        $embedfield = new xmldb_field('embed', XMLDB_TYPE_TEXT, null, null, null, null, null, 'question');

        // Conditionally launch add field embed.
        if (!$dbman->field_exists($coursetable, $embedfield)) {
            $dbman->add_field($coursetable, $embedfield);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022041400, 'format', 'udehauthoring');
    }

    if($oldversion < 2022042200) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_exp_tool')) {
            $tooltable = new xmldb_table('udehauthoring_exp_tool');

            $tooltable->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $tooltable->add_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $tooltable->add_field('audehexplorationid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $tooltable->add_field('toolid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $tooltable->add_field('tooltype', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);

            $tooltable->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $tooltable->add_key('audehexplorationidfk', XMLDB_KEY_FOREIGN, ['audehexplorationid'], 'udeh_exploration', ['id']);
            $dbman->create_table($tooltable);
        }

        // Define field timemodified to be added to udehauthoring_evaluation_obj.
        $currenttooltable = new xmldb_table('udehauthoring_exp_tool');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'tooltype');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($currenttooltable, $timefield)) {
            $dbman->add_field($currenttooltable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_exp_tool} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($currenttooltable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022042200, 'format', 'udehauthoring');
    }

    if($oldversion < 2022042201) {

        // Define field embed to be added to udehauthoring_course.
        $coursetable = new xmldb_table('udehauthoring_course');
        $isembedfield = new xmldb_field('isembed', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, 'embed');
        $isembedfield->setDefault(0);

        // Conditionally launch add field embed.
        if (!$dbman->field_exists($coursetable, $isembedfield)) {
            $dbman->add_field($coursetable, $isembedfield);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022042201, 'format', 'udehauthoring');
    }

    if($oldversion < 2022050400) {

        // Define field embed to be added to udehauthoring_course.
        $sectiontable = new xmldb_table('udehauthoring_section');
        $embedfield = new xmldb_field('embed', XMLDB_TYPE_TEXT, null, null, null, null, null, 'introductiontext');
        $isembedfield = new xmldb_field('isembed', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, 'embed');
        $isembedfield->setDefault(0);

        // Conditionally launch add field embed.
        if (!$dbman->field_exists($sectiontable, $embedfield)) {
            $dbman->add_field($sectiontable, $embedfield);
        }
        if (!$dbman->field_exists($sectiontable, $isembedfield)) {
            $dbman->add_field($sectiontable, $isembedfield);
        }

        // Define field embed to be added to udehauthoring_course.
        $evaluationtable = new xmldb_table('udehauthoring_evaluation');
        $embedfield = new xmldb_field('embed', XMLDB_TYPE_TEXT, null, null, null, null, null, 'descriptionfull');
        $isembedfield = new xmldb_field('isembed', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, 'embed');
        $isembedfield->setDefault(0);

        // Conditionally launch add field embed.
        if (!$dbman->field_exists($evaluationtable, $embedfield)) {
            $dbman->add_field($evaluationtable, $embedfield);
        }
        if (!$dbman->field_exists($evaluationtable, $isembedfield)) {
            $dbman->add_field($evaluationtable, $isembedfield);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022050400, 'format', 'udehauthoring');
    }

    if($oldversion < 2022050500) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_eval_tool')) {
            $tooltable = new xmldb_table('udehauthoring_eval_tool');

            $tooltable->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $tooltable->add_field('courseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $tooltable->add_field('audehevaluationid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $tooltable->add_field('toolid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);

            $tooltable->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $tooltable->add_key('audehevaluationidfk', XMLDB_KEY_FOREIGN, ['audehevaluationid'], 'udeh_evaluation', ['id']);
            $dbman->create_table($tooltable);
        }


        $currenttooltable = new xmldb_table('udehauthoring_eval_tool');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'toolid');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($currenttooltable, $timefield)) {
            $dbman->add_field($currenttooltable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_eval_tool} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($currenttooltable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022050500, 'format', 'udehauthoring');
    }

    if($oldversion < 2022061701) {
        $currentexptable = new xmldb_table('udehauthoring_exploration');
        $activityfreetype = new xmldb_field('activityfreetype', XMLDB_TYPE_TEXT, null, null, null, null, null, 'activitytype');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($currentexptable, $activityfreetype)) {
            $dbman->add_field($currentexptable, $activityfreetype);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022061701, 'format', 'udehauthoring');
    }

    if($oldversion < 2022062000) {
        $evaltooltable = new xmldb_table('udehauthoring_eval_tool');
        $tooltype = new xmldb_field('tooltype', XMLDB_TYPE_TEXT, null, null, null, null, null, 'toolid');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($evaltooltable, $tooltype)) {
            $dbman->add_field($evaltooltable, $tooltype);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2022062000, 'format', 'udehauthoring');
    }

    if($oldversion < 2023042800) {
        $table = new xmldb_table('udehauthoring_section');
        $field = new xmldb_field('isvisible', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, 1, 'comments');
        $field->setDefault(1);

        //$DB->execute("UPDATE {udehauthoring_section} SET isvisible = 1 WHERE 1");

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023042800, 'format', 'udehauthoring');
    }

    if($oldversion < 2023050200) {
        $table = new xmldb_table('udehauthoring_section');
        $field = new xmldb_field('isvisiblepreview', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, 1, 'isvisible');
        $field->setDefault(1);

        //$DB->execute("UPDATE {udehauthoring_section} SET isvisible = 1 WHERE 1");

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023050200, 'format', 'udehauthoring');
    }

    if($oldversion < 2023050500) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_eval_sect')) {
            $table = new xmldb_table('udehauthoring_eval_sect');

            $table->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('audehsectionid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->add_field('audehevaluationid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);

            $table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('audehevaluationidfk', XMLDB_KEY_FOREIGN, ['audehevaluationid'], 'udeh_evaluation', ['id']);
            $table->add_key('audehsectionidfk', XMLDB_KEY_FOREIGN, ['audehsectionid'], 'udeh_section', ['id']);
            $dbman->create_table($table);
        }


        $evalsecttable = new xmldb_table('udehauthoring_eval_sect');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'audehevaluationid');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($evalsecttable, $timefield)) {
            $dbman->add_field($evalsecttable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_eval_sect} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($evalsecttable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023050500, 'format', 'udehauthoring');
    }

    if($oldversion < 2023051600) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_add_info')) {
            $table = new xmldb_table('udehauthoring_add_info');

            $table->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('audehcourseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('content', XMLDB_TYPE_TEXT, null, null, null);

            $table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('audehcourseidfk', XMLDB_KEY_FOREIGN, ['audehcourseid'], 'udehauthoring_course', ['id']);
            $dbman->create_table($table);
        }


        $addinfotable = new xmldb_table('udehauthoring_add_info');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'content');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($addinfotable, $timefield)) {
            $dbman->add_field($addinfotable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_add_info} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($addinfotable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023051600, 'format', 'udehauthoring');
    }

    if($oldversion < 2023081400) {
        $now = time();
        if (!$dbman->table_exists('udehauthoring_title')) {
            $table = new xmldb_table('udehauthoring_title');

            $table->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('audehcourseid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
            $table->add_field('module', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('question', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('question_explore', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('question_hide', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('question_sub', XMLDB_TYPE_TEXT, null, null, null);

            $table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('audehcourseidfk', XMLDB_KEY_FOREIGN, ['audehcourseid'], 'udehauthoring_course', ['id']);
            $dbman->create_table($table);
        }


        $titletable = new xmldb_table('udehauthoring_title');
        $timefield = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'question_sub');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($titletable, $timefield)) {
            $dbman->add_field($titletable, $timefield);
        }

        $DB->execute("UPDATE {udehauthoring_title} SET timemodified = '{$now}' WHERE 1");
        $timefield->setDefault(null);
        $dbman->change_field_default($titletable, $timefield);

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023081400, 'format', 'udehauthoring');
    }

    if ($oldversion < 2023102003) {
        $exptable = new xmldb_table('udehauthoring_exploration');

        // Conditionally launch add field timemodified.
        if ($dbman->field_exists($exptable, 'party')) {
            $DB->execute("ALTER TABLE {udehauthoring_exploration} CHANGE `party` party int");
        }

        // Udehauthoring savepoint reached.
        upgrade_plugin_savepoint(true, 2023102003, 'format', 'udehauthoring');
    }

    return true;
}
