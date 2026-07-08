<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Type\Date;

class Finances
{
    private static function processData($items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'contragent' => $item['UF_CONTRAGENT'],
                'date_update' => $item['UF_DATE_UPDATE'] instanceof DateTime
                    ? $item['UF_DATE_UPDATE']->format('d.m.Y H:i') : null,
                'credit_limit' => $item['UF_CREDIT_LIMIT'],
                'delay' => $item['UF_DELAY'],
                'credit_limit_free' => $item['UF_CREDIT_LIMIT_FREE'],
                'credit_limit_used' => $item['UF_CREDIT_LIMIT_USED'],
                'overdue_sum' => $item['UF_OVERDUE_SUM'],
                'advance_sum' => $item['UF_ADVANCE_SUM'],
                'overdue_days' => $item['UF_OVERDUE_DAYS'],
                'debt' => $item['UF_DEBT'],
                'shipment_ban' => (bool) $item['UF_SHIPMENT_BAN'],
                'next_payment_date' => $item['UF_NEXT_PAYMENT_DATE'] instanceof Date
                    ? $item['UF_NEXT_PAYMENT_DATE']->format('d.m.Y') : null,
                'credit_available' => (bool) $item['UF_CREDIT_AVAILABLE'],
            ];
        }
        return $result;
    }

    public static function get($arRequest)
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            throw new \Exception('Необходима авторизация.');
        }

        $items = Entity::getInstance()->getList(
            Constants::HLBLOCK_B_HL_FINANCES,
            [
                'order' => ['UF_DATE_UPDATE' => 'DESC'],
                'filter' => ['UF_CONTRAGENT' => $USER->GetID()],
            ]
        );

        return [
            'count' => count($items ?? []),
            'items' => self::processData($items ?? []),
        ];
    }
}