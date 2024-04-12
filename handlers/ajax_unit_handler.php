<?php

require_once('../../../../config.php');
global $DB;

if (isset($_POST)) {
    if (array_key_exists("units", $_POST)) {
        $units = $_POST["units"];
        $currentunits = \format_udehauthoring\model\unit_config::instance_all();
        $savedunits = [];
        foreach ($units as $key => $unitvalue) {
            if($key > 0) {
                $unit = \format_udehauthoring\model\unit_config::instance_by_name('udeh_unit_' . $key);
                if ($unit === null) {
                    $unit = new \format_udehauthoring\model\unit_config();
                    $unit->name = 'udeh_unit_' . $key;
                }
                $unit->value = $unitvalue;
                $unit->save();
            }
            $savedunits[] = $unitvalue;
        }

        if(count($currentunits) > 0) {
            if(count($savedunits) < count($currentunits)) {
                for($i = (count($savedunits) - 1); $i < (count($currentunits) - 1); $i++) {
                    $currentunits[$i]->delete();
                }
            }
        }
    }

    echo json_encode(array('success' => 1));
} else {
    echo json_encode(array('success' => 0));
}