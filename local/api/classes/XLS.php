<?php

namespace Legacy\API;

use \Bitrix\Main\Loader;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\SectionTable;
use Legacy\General\Constants;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing;

class XLS
{
    public static function getBasket($arRequest)
    {
        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);

            throw new \Exception('Не удалось скачать XLS-файл.');
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Корзина');
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5); //№
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30); //Номенклатура
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20); //Артикул
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30); //Картинка
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30); //Хар-ки
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15); //Цена
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(10); //Кол-во
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20); //Цена итог


        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/xlsphotos/xls_header.jpg')){
            $drawing->setPath($_SERVER["DOCUMENT_ROOT"] . '/upload/xlsphotos/xls_header.jpg');
        } else{
            $drawing->setPath($_SERVER["DOCUMENT_ROOT"] . '/upload/xlsphotos/xls_header.png');
        }
        $drawing->setWidth(1120);
        $drawing->setCoordinates('A1');

        $spreadsheet->getActiveSheet()->getRowDimension('1')
            ->setRowHeight(self::getRowWithImageHeight($drawing));

        //Информация перед таблицей
        $today = date("d.m.Y");

        $spreadsheet->setActiveSheetIndex(0)
            ->mergeCells('A4:B4')
            ->mergeCells('C4:E4')
            ->mergeCells('F4:H4')
            ->setCellValue('A4', 'Дата: ' . $today)
            ->mergeCells('A5:H5')
            ->setCellValue('A5', 'КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ')
            ->mergeCells('A6:H6')
        ;

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('A5')->applyFromArray($styleArray);

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A8', '№ п/п')
            ->setCellValue('B8', 'Номенклатура')
            ->setCellValue('C8', 'Артикул')
            ->setCellValue('D8', 'Внешний вид')
            ->setCellValue('E8', 'Технические характеристики')
            ->setCellValue('F8', 'Цена с НДС (руб.)')
            ->setCellValue('G8', 'Кол-во')
            ->setCellValue('H8', 'Итого (руб.)')
        ;

        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFF',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'ff1a56db',
                ],
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('A8:H8')->applyFromArray($styleArray);

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $rowNumber = 8;
        $basket = Basket::get([]);

        $basketItems = array_values($basket['items']);

        for ($i = 0; $i < $basket['count']; $i++) {
            $basketGood = $basketItems[$i];
            $rowNumber++;

            $characteristics = '';

            foreach ($basketGood['properties'] as $property){
                if($property){
                    $value = '';
                    if($property['value']['alias'] || $property['value']['code']){
                        $value = $property['value']['alias'] ?? $property['value']['code'];
                    } else {
                        foreach ($property['value'] as $propertyValue){
                            $value .= ', ' . ($propertyValue['alias'] ?? $propertyValue['code']);
                        }
                        $value = substr($value, 2);
                    }
                    $characteristics .= $property['name'] . ': ' . $value . "\n";
                }
            }

            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $rowNumber, $i+1)
                ->setCellValue('B' . $rowNumber, $basketGood['name'])
                ->setCellValue('C' . $rowNumber, $basketGood['properties']['offer_article'] ? $basketGood['properties']['offer_article']['value']['alias'] : $basketGood['properties']['product_article']['value']['alias'])
                ->setCellValue('E' . $rowNumber, $characteristics)
                ->setCellValue('F' . $rowNumber, $basketGood['discount_price'] ?: $basketGood['price'])
                ->setCellValue('G' . $rowNumber, $basketGood['quantity'])
                ->setCellValue('H' . $rowNumber, $basketGood['sum_discount_price'] ?: $basketGood['sum_price'])
            ;

            $imgPath = $basketGood['image'];
            if($imgPath){
                $imgPath = $_SERVER["DOCUMENT_ROOT"].str_replace(getServerName(), '', $imgPath['url']);

                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setWorksheet($spreadsheet->getActiveSheet());
                $drawing->setName('image' . $basketGood['name']);
                $drawing->setDescription('image' . $basketGood['name']);
                $drawing->setPath($imgPath);
                $drawing->setHeight(200);
                $drawing->setWidth(self::getRowImageWidth($drawing));
                $drawing->setCoordinates('D' . $rowNumber);
                $drawing->setOffsetX((210-$drawing->getWidth())/2 + 1);
                $drawing->setOffsetY(1);
                $spreadsheet->getActiveSheet()->getRowDimension($rowNumber)
                    ->setRowHeight(self::getRowWithImageMinHeight($drawing));
            }
        }

        $spreadsheet->getActiveSheet()->getStyle('A9:H' . $rowNumber)->applyFromArray($styleArray);

        $sumRow = $rowNumber+1;
        $countRow = $rowNumber+2;
        $sumRowWritten = $rowNumber+3;
        $managerRow = $rowNumber+6;
        $signatureRow = $rowNumber+6;

        $spreadsheet->setActiveSheetIndex(0)
            ->mergeCells('A' . $sumRow . ':B' . $sumRow)
            ->mergeCells('C' . $sumRow . ':H' . $sumRow)
            ->setCellValue('A' . $sumRow, 'Итого')
            ->setCellValue('C' . $sumRow, $basket['discount_price'] ?: $basket['price'])
            ->mergeCells('A' . $countRow . ':B' . $countRow)
            ->mergeCells('C' . $countRow . ':H' . $countRow)
            ->setCellValue('A' . $countRow, 'Общее количество')
            ->setCellValue('C' . $countRow, $basket['count'])
            ->mergeCells('A' . $sumRowWritten . ':B' . $sumRowWritten)
            ->mergeCells('C' . $sumRowWritten . ':H' . $sumRowWritten)
            ->setCellValue('A' . $sumRowWritten, 'Итого к оплате прописью (руб.)')
            ->setCellValue('C' . $sumRowWritten, num2str($basket['discount_price'] ?: $basket['price']))
            ->setCellValue('B' . $managerRow, 'Ответственный менеджер')
            ->mergeCells('E' . $managerRow . ':G' . $managerRow)
            ->mergeCells('E' . $signatureRow . ':G' . $signatureRow)
            ->setCellValue('E' . $signatureRow, 'Ф.И.О. (подпись)')
        ;

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('E' . $signatureRow . ':G' . $signatureRow)->applyFromArray($styleArray);

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'fff0f1f1',
                ],
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('A' . $sumRow .':H' . $sumRowWritten)->applyFromArray($styleArray);

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('C' . $sumRow .':H' . $sumRowWritten)->applyFromArray($styleArray);


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Корзина.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:H' . $signatureRow);
        $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToPage(false);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(true);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(false);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    private static function getRowWithImageHeight($drawing)
    {
        return $drawing->getHeight()/1.33532934 + 2;
    }

    private static function getRowWithImageMinHeight($drawing)
    {
        $height = 50;
        $rowWithImageHeight = self::getRowWithImageHeight($drawing);
        if($rowWithImageHeight > $height ){
            $height = $rowWithImageHeight;
        }
        return $height;
    }

    private static function getRowImageWidth($drawing)
    {
        $width = $drawing->getWidth();
        if($width >= 210) {
            return 209;
        }
        else{
            return $width;
        }
    }

    public static function getPriceList($arRequest)
    {
        $helper = new Sample();
        if ($helper->isCli()) {
            $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
            throw new \Exception('Не удалось скачать XLS-файл.');
        }

        $catalogItems = $arRequest['catalog_items'];

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Каталог');
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(10); //ID
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30); //Раздел
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15); //Артикул
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40); //Название
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15); //Базовая цена
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15); //Ваша цена
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30); //Количество для заказа

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Раздел')
            ->setCellValue('C1', 'Артикул')
            ->setCellValue('D1', 'Название')
            ->setCellValue('E1', 'Базовая цена')
            ->setCellValue('F1', 'Ваша цена')
            ->setCellValue('G1', 'Количество для заказа')
        ;

        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFF',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'ff1a56db',
                ],
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);

        $rowNumber = 1;

        for ($i = 0; $i < count($catalogItems); $i++) {
            $item = $catalogItems[$i];
            $rowNumber++;


            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $rowNumber, $item['id'])
                ->setCellValue('B' . $rowNumber, $item['section_name'])
                ->setCellValue('C' . $rowNumber, $item['article'])
                ->setCellValue('D' . $rowNumber, $item['name'])
                ->setCellValue('E' . $rowNumber, $item['base_price'])
                ->setCellValue('F' . $rowNumber, $item['price'])
            ;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Кубикс корзина.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:G' . $rowNumber);
        $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToPage(false);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(true);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(false);

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
        exit;
    }

    public static function uploadPriceList($arRequest)
    {
        if (empty($_FILES['file'])) {
            throw new \Exception('Не выбран файл для загрузки.');
        }

        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);
        if ($fileContent === false) {
            throw new \Exception('Не удалось прочитать файл.');
        }

        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet()
            ->toArray()
        ;

        return $sheet;
    }

}
