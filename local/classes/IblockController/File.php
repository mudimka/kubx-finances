<?php

namespace Legacy\IblockController;

use Bitrix\Main\IO\File as IOFile;

class File
{
    public static function upload($arRequest)
    {
        $file = $arRequest['file'] ?? null;
        $savePath = $arRequest['save_path'] ?? '';
        $emptyError = $arRequest['empty_error'] ?? 'Файл отсутствует.';
        $save_error = $arRequest['save_error'] ?? 'Не удалось сохранить файл.';
        if(!isset($file)){
            throw new \Exception($emptyError);
        }

        if (IOFile::isFileExists($file['tmp_name'])) {
            $arFile = \CFile::MakeFileArray($file['tmp_name']);
            $arFile['name'] = $file['name'];
            $fileID = \CFile::SaveFile($arFile, $savePath);
            return $fileID;
        }

        throw new \Exception($save_error);
    }

    public static function delete($arRequest)
    {
        $file = \CFile::getById($arRequest['file_id'])->fetch();
        if(!$file){
            throw new \Exception('Файл не найден');
        }
        \CFile::Delete($arRequest['file_id']);

        return true;
    }

    public static function processArray($arRequest)
    {
        $files = $arRequest['files'];
        $count = count($files['name']);

        $result = [];
        for($i = 0; $i < $count; $i++){
            $result[] = [
                'name' => $files['name'][$i],
                'full_path' => $files['full_path'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
        }

        return $result;
    }

    public static function getFilesInfo($fileIds, $needKeys = false, $multiple = true)
    {
        $filesInfo = [];

        if (empty($fileIds)) {
            return $filesInfo;
        }

        if(!is_array($fileIds)){
            $fileIds = [$fileIds];
        }

        $fileIds = array_filter($fileIds); // убираем пустые значения

        $result = \CFile::GetList([], ['@ID' => implode(',', $fileIds)]);

        while ($file = $result->Fetch()) {
            $fileExtension = pathinfo($file['ORIGINAL_NAME'], PATHINFO_EXTENSION);
            $fileNameWithoutExtension = pathinfo($file['ORIGINAL_NAME'], PATHINFO_FILENAME);
            $filesInfo[$file['ID']] = [
                'id' => $file['ID'],
                'description' => $file['DESCRIPTION'],
                'name' => $fileNameWithoutExtension,
                'size' => self::formatFileSize($file['FILE_SIZE']),
                'type' => $fileExtension,
                'url' => getFilePath($file['ID']),
            ];
        }

        if (!$needKeys) {
            $filesInfo = array_values($filesInfo);
        }

        if(!$multiple){
            return array_values($filesInfo)[0];
        }

        return $filesInfo;
    }

    private static function formatFileSize($size): string
    {
        if ($size < 1024) {
            return $size . ' Б';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024)) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024)) . ' GB';
        }
    }

}
