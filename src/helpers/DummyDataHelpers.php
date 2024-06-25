<?php

namespace quatrecentquatre\dummydata\helpers;

use Craft;
use Faker;
use craft\base\Component;
use quatrecentquatre\dummydata\services\DummyAssetsService;

class DummyDataHelpers extends Component
{
    /*
    * Return value of a specific field type
    */
    public function getFieldDataByType($type, $default = null)
    {
        $faker = Faker\Factory::create();
        $assetsService = new DummyAssetsService();

        switch ($type) {
            case 'address' :
                $return_value = $faker->streetAddress();
            break;
            case 'userAgent' :
                $return_value = $faker->userAgent();
            break;
            case 'ip' :
                $return_value = $faker->ipv4();
            break;
            case 'url' :
                $return_value = $faker->url();
            break;
            case 'userName' :
                $return_value = $faker->userName();
            break;
            case 'name' :
                $return_value = $faker->name();
            break;
            case 'email' :
                $return_value = $faker->email();
            break;
            case 'firstName' :
                $return_value = $faker->firstName();
            break;
            case 'lastName' :
                $return_value = $faker->lastName();
            break;
            case 'stateAbbr' :
                $return_value = $faker->stateAbbr();
            break;
            case 'streetName' :
                $return_value = $faker->streetName();
            break;
            case 'secondaryAddress' :
                $return_value = $faker->secondaryAddress();
            break;
            case 'postcode' :
                $return_value = $faker->postcode();
            break;
            case 'city' :
                $return_value = $faker->city();
            break;
            case 'latitude' :
                $return_value = $faker->latitude($min = -90, $max = 90);
            break;
            case 'longitude' :
                $return_value = $faker->longitude($min = -180, $max = 180);
            break;
            case 'phoneNumber' :
                $return_value = $faker->phoneNumber();
            break;
            case 'date' :
                $return_value = $faker->date();
            break;
            case 'text' :
                $return_value = $faker->text(100);
            break;
            case 'image' :
                $return_value = $assetsService->getAsset('jpg');
            break;
            case 'word' :
                $return_value = $assetsService->getAsset('doc');
            break;
            case 'txt' :
                $return_value = $assetsService->getAsset('txt');
            break;
            case 'pdf' :
                $return_value = $assetsService->getAsset('pdf');
            break;
            case 'excel' :
                $return_value = $assetsService->getAsset('xlsx');
            break;
            case 'compressed' :
                $return_value = $assetsService->getAsset('zip');
            break;
            case 'video' :
                $return_value = $assetsService->getAsset('mp4');
            break;
            default :
            $return_value = $default ?? '';
        }

        return $return_value;
    }

    public function getAllFieldsCraft()
    {
        $fields = [
            ['label' => 'Select a field', 'value' => '']
        ];
        foreach (Craft::$app->getFields()->getAllFields() as $field) {
            $fields[] = [
                'label' => $field['name'] . ' (' . $field['handle'] . ')',
                'value' => $field['handle'],
            ];
        }
        return $fields;
    }
    
    public function getAllSectionsCraft()
    {
        $fields = [
            ['label' => 'Select a section', 'value' => '']
        ];
        
        foreach (Craft::$app->getSections()->getAllSections() as $field) {
            $fields[] = [
                'label' => $field['name'] . ' (' . $field['handle'] . ')',
                'value' => $field['handle'],
            ];
        }
        return $fields;
    }

}
