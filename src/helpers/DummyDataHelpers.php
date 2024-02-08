<?php

namespace quatrecentquatre\dummydata\helpers;

use Faker;
use craft\base\Component;
use quatrecentquatre\dummydata\services\DummyAssetsService;

class DummyDataHelpers extends Component
{

    public function getFieldDataByType($type, $default = null)
    {
        $faker = Faker\Factory::create();
        $assetsService = new DummyAssetsService();

        $return_value = match ($type) {
            'address' => $faker->streetAddress(),
            'userAgent' => $faker->userAgent(),
            'ip' => $faker->ipv4(),
            'url' => $faker->url(),
            'userName' => $faker->userName(),
            'name' => $faker->name(),
            'email' => $faker->email(),
            'firstName' => $faker->firstName(),
            'lastName' => $faker->lastName(),
            'stateAbbr' => $faker->stateAbbr(),
            'streetName' => $faker->streetName(),
            'streetAddress' => $faker->streetAddress(),
            'secondaryAddress' => $faker->secondaryAddress(),
            'postcode' => $faker->postcode(),
            'city' => $faker->city(),
            'latitude' => $faker->latitude($min = -90, $max = 90),
            'longitude' => $faker->longitude($min = -180, $max = 180),
            'phoneNumber' => $faker->phoneNumber(),
            'date' => $faker->date('Y-m-d H:i:s'),
            'text' => $faker->text(100),

            'image' => $assetsService->getAsset('jpg'),
            'word' => $assetsService->getAsset('doc'),
            'txt' => $assetsService->getAsset('txt'),
            'pdf' => $assetsService->getAsset('pdf'),
            'excel' => $assetsService->getAsset('xlsx'),
            'compressed' => $assetsService->getAsset('zip'),

            default => $default ?? '',
        };

        return $return_value;
    }

}
