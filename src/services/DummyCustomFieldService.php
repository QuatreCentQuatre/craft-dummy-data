<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Faker;
use craft\base\Component;
use craft\elements\Asset;
use Exception;
use quatrecentquatre\dummydata\DummyData;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;

class DummyCustomFieldService extends Component
{

    public $settings;

    public array $assetsType = ['word', 'txt', 'pdf', 'image', 'excel', 'compressed'];
    
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
        $fieldsSettings = collect($this->settings->custom_fields ?? []);
        if (!$fieldsSettings->count()) {
            return;
        }

        $fields = Craft::$app->getFields()->getAllFields();

        foreach ($fields as $field) {
            if ($setting = $fieldsSettings->where('handle', $field['handle'])->first()) {
                $setting['value'] = (new DummyDataHelpers)->getFieldDataByType($setting['type'], ($setting['value'] ?? ''));
                if (in_array($setting['type'], $this->assetsType)) {
                    $this->updateAssetField($field, $setting);
                } else {
                    $this->updateCustomField($field, $setting);
                }
            }
        }
    }

    private function updateCustomField($field, $setting) 
    {
        $fieldName = (!empty($field['columnPrefix'])) ? $field['columnPrefix'] . '_' : 'field_';
        $fieldName .= $field['handle'];
        $fieldName .= (!empty($field['columnSuffix'])) ? '_' . $field['columnSuffix'] : '';
        
        try {
            $results = Yii::$app->db->createCommand(
                                                     "UPDATE content 
                                                     SET " . $fieldName . "=:fieldName 
                                                     WHERE " . $fieldName . " IS NOT NULL"
                                                    )
                                    ->bindValue(':fieldName', $setting['value'])
                                    ->execute();

            echo 'Custom field - ' . $fieldName . ' - Items affected : ' . $results . "\n";
        } catch (Exception $e) {
            Craft::warning("Unable to clean field {$setting['handle']}: {$e->getMessage()}", __METHOD__);
        }
    }

    private function updateAssetField($field, $setting) 
    {
        $assetsIds = Yii::$app->db->createCommand(
                                                    "SELECT DISTINCT(targetId) 
                                                    FROM relations 
                                                    WHERE fieldId =:fieldId"
                                                )
                                    ->bindValue(':fieldId', $field->id)
                                    ->queryColumn();

        try {
            //Replace all relation to dummy file
            $resultsRelations = Yii::$app->db->createCommand("UPDATE relations
                                    SET targetId =:fileId
                                    WHERE fieldId =:fieldId")
                ->bindValue(':fileId', $setting['value']->id)
                ->bindValue(':fieldId', $field->id)
                ->execute();

            //delete private files from db/server
            $assets = Asset::find()->id($assetsIds)->all();
            foreach ($assets as $asset) {
                Craft::$app->elements->deleteElement($asset);
            }

            echo 'Assets - ' . $setting['handle'] . ' - Items affected - ' . count($assets) . "\n";
        } catch (Exception $e) {
            Craft::warning("Unable to clean assets for field {$setting['handle']}: {$e->getMessage()}", __METHOD__);
        }
        
    }
}
