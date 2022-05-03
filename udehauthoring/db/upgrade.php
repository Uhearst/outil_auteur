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

    return true;
}
