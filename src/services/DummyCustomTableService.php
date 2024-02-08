<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Faker;
use craft\base\Component;
use Exception;
use quatrecentquatre\dummydata\DummyData;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;

class DummyCustomTableService extends Component
{

    public $settings;
    
    /**
     * @inheritdoc
     */
    public function init() :void
    {
        parent::init();

        $this->settings = DummyData::getInstance()->getSettings();
    }

    public function clean() 
    {  
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

            echo 'Custom table - ' . $table . ' - ' . $fieldName . ' - Items affected : ' . $results . "\n";
        } catch (Exception $e) {
            Craft::warning("Unable to clean field {$fieldName} in table {$table}: {$e->getMessage()}", __METHOD__);
        }
    }
}
