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

        // Define field timemodified to be added to udehauthoring_resource.
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

    return true;
}
