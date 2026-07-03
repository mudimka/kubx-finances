<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;


class CKubxUpload extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    protected $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
    }

    private function uploadFiles()
    {
        global $USER;
        if (!$USER->IsAdmin()) {
            $this->errorCollection[] = new Error(Loc::getMessage("CLS_KUBX_SETTINGS_ACCESS_DENIED"), 403);
            return null;
        }

        $res = [];
        foreach ($_FILES as $file) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $base = pathinfo($file['name'], PATHINFO_FILENAME);
            $base = preg_replace('/[^\p{L}\p{N}\.]+/u', '_', $base);
            $file['name'] = $base . ($ext !== '' ? '.' . $ext : '');

            $fileID = CFile::SaveFile(
                $file,
                'kubx.settings/'
            );
            $res[] = \CFile::GetPath($fileID);
        }

        $jsonResult = ['pathes' => $res];
        return $jsonResult;
    }

    public function uploadFilesAction()
    {
        if ($this->startResultCache()) {
            return $this->uploadFiles();
        }
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function configureActions()
    {
        return [
            'uploadFiles' => [
                'prefilters' => []
            ]
        ];
    }
}
