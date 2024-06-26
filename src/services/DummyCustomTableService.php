<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Exception;
use craft\helpers\Console;
use quatrecentquatre\dummydata\services\DummyService;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;

class DummyCustomTableService extends DummyService
{

    public function clean() 
    {  
        //Get all fields settings for custom tables
        $tablesSettings = collect($this->settings->custom_tables ?? []);
        if (!$tablesSettings->count()) {
            return;
        }

        foreach ($tablesSettings as $table) {
            foreach ($table['custom_fields'] as $field) {
                $field['value'] = (new DummyDataHelpers)->getFieldDataByType($field['type'], ($field['value'] ?? ''));

                $this->updateCustomField($table['name'], $field);
            }
        }
    }

    /*
    * Update specific column with random generated data.
    */
    private function updateCustomField($table, $field) 
    {       
        $fieldName = $field['handle'];

        try {
            $results = Yii::$app->db->createCommand(
                                                        "UPDATE " . $table . " 
                                                        SET " . $fieldName . "=:fieldName 
                                                        WHERE " . $fieldName . " IS NOT NULL"
                                                    )
                                    ->bindValue(':fieldName', $field['value'])
                                    ->execute();
            
            Console::stdout("Custom table - " . $table . " - " . $fieldName . " - Items affected : " . $results . "\n");
        } catch (Exception $e) {
            Craft::warning("Unable to clean field {$fieldName} in table {$table}: {$e->getMessage()}", __METHOD__);
        }
    }
}
