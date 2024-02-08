<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\models\VolumeFolder;
use quatrecentquatre\dummydata\DummyData;

class DummyAssetsService extends Component
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

    public function getAsset($fileExtension)
    {
        $pluginPath = DummyData::getInstance()->getBasePath();
        
        //@TODO Params settings plugin
        $folderName = 'dummy-files';
        $filename = 'test';
        $fileFullName = $filename . '.' . $fileExtension;
        $dummyFilesPath = $pluginPath . '/dummy-files/';

        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        $assetsService = Craft::$app->getAssets();

        foreach ($volumes as $volume) {
            $rootFolder = $assetsService->getRootFolderByVolumeId($volume->id);
            
            $folderDummyData = $assetsService->findFolder([
                'volumeId' => $volume->id,
                'parentId' => $rootFolder->id,
                'name' => $folderName,
            ]);

            //Create empty folder if plugin hasn't created one.
            if(!$folderDummyData) {
                $folder = new VolumeFolder();
                $folder->name = $folderName;
                $folder->volumeId = $volume->id;
                $folder->path = $folderName .'/';
                $folder->parentId = $rootFolder->id;
                $folderDummyData = $assetsService->createFolder($folder);

                $folderDummyData = $folder;
            }

            //check if file exists in plugin folder
            $asset = Asset::find()->filename($fileFullName)->folderId($folderDummyData->id)->one();

            if (!$asset) {
                $tmpFile = Craft::$app->getPath()->getTempAssetUploadsPath() . '/tmp.' . $fileExtension;
                //create tmp file to upload (Deleted by default after upload)
                copy($dummyFilesPath . $fileFullName, $tmpFile);
                
                // Check the permissions to upload in the resolved folder.
                $asset = new Asset();
                $asset->tempFilePath =  $tmpFile;
                $asset->filename = $fileFullName;
                $asset->newFolderId = $folderDummyData->id;
                $asset->setVolumeId($folderDummyData->volumeId);
                $asset->uploaderId = 1;
                $asset->avoidFilenameConflicts = true;
                $asset->setScenario(Asset::SCENARIO_CREATE);

                Craft::$app->getElements()->saveElement($asset);
            }

            return $asset;
        }
    }
    
}
