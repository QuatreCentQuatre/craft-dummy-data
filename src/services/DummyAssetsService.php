<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use craft\elements\Asset;
use craft\models\VolumeFolder;
use quatrecentquatre\dummydata\DummyData;
use quatrecentquatre\dummydata\services\DummyService;

class DummyAssetsService extends DummyService
{
    private string $folderName = 'dummy-files';
    private string $filename = 'test';
    private string $dummyFilesPath;
    private string $pluginPath;
    private $volumes;
    private $assetsService;

    public function init() :void
    {
        parent::init();

        $this->volumes = Craft::$app->getVolumes()->getAllVolumes();
        $this->pluginPath = DummyData::getInstance()->getBasePath();
        $this->assetsService = Craft::$app->getAssets();

        $this->dummyFilesPath = $this->pluginPath . '/dummy-files/';
    }

    public function getAsset($fileExtension)
    {
        $fileFullName = $this->filename . '.' . $fileExtension;

        foreach ($this->volumes as $volume) {
           $folder = $this->getFolder($volume);

            //check if file exists in plugin folder
            $asset = Asset::find()->filename($fileFullName)->folderId($folder->id)->one();

            if (!$asset) {
                //create tmp file to upload (Deleted by default after upload)
                $tmpFile = Craft::$app->getPath()->getTempAssetUploadsPath() . '/tmp.' . $fileExtension;
                copy($this->dummyFilesPath . $fileFullName, $tmpFile);
                
                // Upload tmp file to plugin folder.
                $asset = $this->uploadFile($tmpFile, $fileFullName, $folder);
            }

            return $asset;
        }
    }

    private function getFolder($volume)
    {
        $rootFolder = $this->assetsService->getRootFolderByVolumeId($volume->id);
            
        $folder = $this->assetsService->findFolder([
            'volumeId' => $volume->id,
            'parentId' => $rootFolder->id,
            'name' => $this->folderName,
        ]);

        //Create empty folder if plugin hasn't created one.
        if(!$folder) {
            $folder = $this->createEmptyFolder($volume, $rootFolder);
        }

        return $folder;
    }

    private function createEmptyFolder($volume, $rootFolder)
    {
        $folder = new VolumeFolder();
        $folder->name = $this->folderName;
        $folder->volumeId = $volume->id;
        $folder->path = $this->folderName .'/';
        $folder->parentId = $rootFolder->id;
        $this->assetsService->createFolder($folder);

        return $folder;
    }

    private function uploadFile($tmpFile, $fileFullName, $folderDummyData)
    {
        $asset = new Asset();
        $asset->tempFilePath =  $tmpFile;
        $asset->filename = $fileFullName;
        $asset->newFolderId = $folderDummyData->id;
        $asset->volumeId = $folderDummyData->volumeId;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        Craft::$app->getElements()->saveElement($asset);

        return $asset;
    }
}
