<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Exception;
use craft\elements\Asset;
use craft\helpers\Console;
use craft\helpers\ElementHelper;
use quatrecentquatre\dummydata\services\DummyService;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;
use yii\base\InvalidConfigException;

class DummyCustomFieldService extends DummyService
{

    public $assetsType = ['word', 'txt', 'pdf', 'image', 'excel', 'compressed', 'video'];

    public function clean() 
    {
        $fieldsSettings = collect($this->settings->custom_fields ?? []);
        if (!$fieldsSettings->count()) {
            return;
        }

        //Get all custom fields in CraftCMS
        $fields = Craft::$app->getFields()->getAllFields();

        foreach ($fields as $field) {
            if ($setting = $fieldsSettings->where('handle', $field['handle'])->first()) {
                $setting['value'] = (new DummyDataHelpers)->getFieldDataByType($setting['type'], ($setting['value'] ?? ''));
                //Check if the fields is an assets type or a text type.
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
        //Get field prefix to append to field handle
        $fieldName = (!empty($field['columnPrefix'])) ? $field['columnPrefix'] . '_' : 'field_';
        $fieldName .= $field['handle'];
        $fieldName .= (!empty($field['columnSuffix'])) ? '_' . $field['columnSuffix'] : '';
        
        try {
            $results = Yii::$app->db->createCommand(
                                                     "UPDATE ".$this->tablePrefix."content 
                                                     SET " . $fieldName . "=:fieldName 
                                                     WHERE " . $fieldName . " IS NOT NULL"
                                                    )
                                    ->bindValue(':fieldName', $setting['value'])
                                    ->execute();

            Console::stdout("Custom field - " . $fieldName . " - Items affected : " . $results . "\n");
        } catch (Exception $e) {
            Craft::warning("Unable to clean field {$setting['handle']}: {$e->getMessage()}", __METHOD__);
        }
    }

    private function updateAssetField($field, $setting) 
    {
        //get all assets ids for a specific field ID
        $assetsIds = Yii::$app->db->createCommand(
                                                    "SELECT DISTINCT(targetId) 
                                                    FROM ".$this->tablePrefix."relations 
                                                    WHERE fieldId =:fieldId AND targetId <> :dummyFileId"
                                                )
                                        ->bindValue(':fieldId', $field->id)
                                        ->bindValue(':dummyFileId', $setting['value']->id)
                                        ->queryColumn();

        try {
            //Replace all relation to dummy file
            Yii::$app->db->createCommand("UPDATE ".$this->tablePrefix."relations
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

            Console::stdout("Assets - " . $setting['handle'] . " - Items affected - " . count($assets) . "\n");
        } catch (Exception $e) {
            Craft::warning("Unable to clean assets for field {$setting['handle']}: {$e->getMessage()}", __METHOD__);
        }
        
    }

    private function updateTitleField()
    {
        $sectionsSettings = collect($this->settings->section_title ?? []);

        foreach ($sectionsSettings as $section) {

            if(!isset($section['handle'])) { continue; }

            $sectionCraft = Craft::$app->getSections()->getSectionByHandle($section['handle']);
            if (!$sectionCraft) {
                throw new InvalidConfigException("Invalid section handle:". $section['handle']);
            }

            $value = (new DummyDataHelpers)->getFieldDataByType($section['type'], ($section['value'] ?? ''));

            $contentIds = Yii::$app->db->createCommand(
                                                    "SELECT distinct(".$this->tablePrefix."elements.id)
                                                    FROM ".$this->tablePrefix."sections 
                                                    INNER JOIN ".$this->tablePrefix."entries ON ".$this->tablePrefix."entries.sectionId = ".$this->tablePrefix."sections.id
                                                    INNER JOIN ".$this->tablePrefix."elements ON ".$this->tablePrefix."elements.id = ".$this->tablePrefix."entries.id
                                                    INNER JOIN ".$this->tablePrefix."content ON ".$this->tablePrefix."content.elementId = ".$this->tablePrefix."elements.id
                                                    WHERE handle = :sectionHandle"
                                                )
                                    ->bindValue(':sectionHandle', $section['handle'])
                                    ->queryColumn();
            
            try {
                if(!$contentIds) {
                    return;
                }

                //Replace title content for section
                $results = Yii::$app->db->createCommand("UPDATE ".$this->tablePrefix."content
                                                            SET title = :title
                                                            WHERE elementId IN ( '" . implode( "', '" , $contentIds ) . "' )")
                    ->bindValue(':title', $value)
                    ->execute();
    
                Console::stdout("Section titles - " . $section['handle'] . " - Items affected - " . $results . "\n");

                //Replace slug and uri for section
                if ($section['slug']) {
                    
                    $slug = ElementHelper::generateSlug($value);

                    //loop all sites to rewrite the slug in uri
                    foreach($sectionCraft->getSiteSettings() as $site) {
                        $uri = str_replace('{slug}', $slug, $site->uriFormat);

                        $results = Yii::$app->db->createCommand("UPDATE ".$this->tablePrefix."elements_sites
                                                                SET slug = CONCAT('" . $slug ."-', id),
                                                                    uri = CONCAT('" . $uri ."-', id)
                                                                WHERE elementId IN ( '" . implode( "', '" , $contentIds ) . "' )")
                                                ->execute();
        
                        Console::stdout("Section slug - " . $section['handle'] . " - Site Id : " . $site->id . " - Items affected - " . $results . "\n");
                    }
                }


            } catch (Exception $e) {
                Craft::warning("Unable to clean title and/or slug for section {$section['handle']}: {$e->getMessage()}", __METHOD__);
            }
        }
    }

}
