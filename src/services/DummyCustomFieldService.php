<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Exception;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\ElementHelper;
use quatrecentquatre\dummydata\DummyData;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;

class DummyCustomFieldService extends Component
{

    public $settings;

    public $assetsType = ['word', 'txt', 'pdf', 'image', 'excel', 'compressed'];
    
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

        $this->updateTitleField();
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
                                                    WHERE fieldId =:fieldId AND targetId <> :dummyFileId"
                                                )
                                        ->bindValue(':fieldId', $field->id)
                                        ->bindValue(':dummyFileId', $setting['value']->id)
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

    private function updateTitleField()
    {
        $fieldsSettings = collect($this->settings->section_title ?? []);
        if (!$fieldsSettings->count()) {
            return;
        }

        foreach ($fieldsSettings as $field) {
            $value = (new DummyDataHelpers)->getFieldDataByType($field['type'], ($field['value'] ?? ''));

            $contentIds = Yii::$app->db->createCommand(
                                                    "SELECT distinct(elements.id)
                                                    FROM sections 
                                                    INNER JOIN entries ON entries.sectionId = sections.id
                                                    INNER JOIN elements ON elements.id = entries.id
                                                    INNER JOIN content ON content.elementId = elements.id
                                                    WHERE handle = :sectionHandle"
                                                )
                                    ->bindValue(':sectionHandle', $field['handle'])
                                    ->queryColumn();
            
            try {
                if(!$contentIds) {
                    return;
                }

                //Replace title content for section
                $results = Yii::$app->db->createCommand("UPDATE content
                                                            SET title = :title
                                                            WHERE elementId IN ( '" . implode( "', '" , $contentIds ) . "' )")
                    ->bindValue(':title', $value)
                    ->execute();
    
                echo 'Section titles - ' . $field['handle'] . ' - Items affected - ' . $results . "\n";

                if ($field['slug']) {
                    //Replace slug for section
                    $slug = ElementHelper::generateSlug($value);
                    $results = Yii::$app->db->createCommand("UPDATE elements_sites
                                                                SET slug = CONCAT('" . $slug ."-', id)
                                                                WHERE elementId IN ( '" . implode( "', '" , $contentIds ) . "' )")
                        ->execute();
        
                    echo 'Section slug - ' . $field['handle'] . ' - Items affected - ' . $results . "\n";
                }


            } catch (Exception $e) {
                Craft::warning("Unable to clean title and/or slug for section {$field['handle']}: {$e->getMessage()}", __METHOD__);
            }
        }
    }

}
