<?php


namespace format_udehauthoring\model;


class unit_config
{
    public $id = null;
    public $name = null;
    public $value = null;

    /**
     * Instantiate an object by querying the database with the config name. Returns null if not found
     * plan exists.
     * @param $name
     * @return unit_config | null
     */
    public static function instance_by_name($name) {
        global $DB;

        try {
            $record = $DB->get_record('config', ['name' => $name]);

            if($record) {
                $unitconfig = new self();
                foreach($unitconfig as $key => $_) {
                    $unitconfig->$key = $record->$key;
                }
                return $unitconfig;
            }
            return null;
        }
        catch(dml_missing_record_exception $e) {
            return null;
        }

    }

    /**
     * Instantiate an array of objects by querying the database. Returns [] if not found
     * plan exists.
     * @return array
     */
    public static function instance_all_values() {
        global $DB;

        $units = [];

        $records = $DB->get_records_sql("SELECT * from {config} where name LIKE 'udeh_unit_%'");

        foreach ($records as $record) {
            $units[$record->id] = $record->value;
        }

        return $units;
    }

    /**
     * Instantiate an array of objects by querying the database. Returns [] if not found
     * plan exists.
     * @return array
     */
    public static function instance_all() {
        global $DB;

        $units = [];

        $records = $DB->get_records_sql("SELECT * from {config} where name LIKE 'udeh_unit_%'");

        foreach ($records as $record) {
            $unitconfig = new self();
            $unitconfig->id = $record->id;
            $unitconfig->name = $record->name;
            $unitconfig->value = $record->value;
            $units[] = $unitconfig;
        }

        return $units;
    }

    /**
     * Retruns string value associated to ID.
     * @param $id
     * @return string
     */
    public static function getValueById($id) {
        global $DB;

        $record = $DB->get_record('config', ['id' => $id], 'value');

        if($record) {
            return $record->value;
        }
        return null;

    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value)) {
                $record->$key = $value;
            }
        }

        if (isset($record->id)) {
            $DB->update_record('config', $record);
        } else {
            $this->id = $DB->insert_record('config', $record);

        }
    }

    public function delete() {
        global $DB;

        $DB->delete_records('udehauthoring_unit', ['audehunitid' => $this->id]);
        return $DB->delete_records('config', ['name' => $this->name]);
    }
}