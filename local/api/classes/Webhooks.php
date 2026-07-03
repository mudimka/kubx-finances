<?php


namespace Legacy\API;

use \Bitrix\Main\Loader;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\SectionTable;
use Legacy\General\Constants;
use Bitrix\Sale;

class Webhooks
{
    public static function changeOrderStatus($arRequest)
    {
        $status = $arRequest['status'];
        $orderId = $arRequest['order_id'];
        $dealId = $arRequest['id_deal'];

        \Bitrix\Main\Diag\Debug::writeToFile(
            [
                'data' => $arRequest,
            ],
            'Выполнен запрос от '.(new \DateTime())->format('d.m.Y H:i:s'),
            'b24_hook.log'
        );

        if (Loader::includeModule('iblock') && Loader::includeModule('sale')) {
            $order = Sale\Order::load($orderId);

            if($order){
                \CSaleOrder::StatusOrder($orderId, $status);
                $propertyB24ID = null;
                $propertyCollection = $order->getPropertyCollection();
                foreach ($propertyCollection as $property) {
                    if ($property->getField('CODE') == 'B24ID') {
                        $propertyB24ID = $property;
                    }
                }
                if ($propertyB24ID && $dealId){
                    $propertyB24ID->setValue($dealId);
                    $order->save();
                }
            }


        }

//        $arLoadProperties = Array(
//            'NAME' => 'Элемент' . date('c'),
//            'IBLOCK_ID'=> Constants::IB_TEST,
//            'ACTIVE'=>'Y',
//            'PROPERTY_VALUES'=>[
//                'PROPERTY'=> $dealId,
//            ]
//        );
//
//        $el = new \CIBlockElement;
//        $el->Add($arLoadProperties);
    }

}
