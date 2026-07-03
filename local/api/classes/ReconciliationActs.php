<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Legacy\HighLoadBlock\Entity;
use Bitrix\Main\Type\Date;

class ReconciliationActs
{
    private static function processData($items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'contragent' => $item['UF_CONTRAGENT'],
                'contract_number' => $item['UF_CONTRACT_NUMBER'],
                'period_from' => $item['UF_PERIOD_FROM'] instanceof Date
                    ? $item['UF_PERIOD_FROM']->format('d.m.Y') : null,
                'period_to' => $item['UF_PERIOD_TO'] instanceof Date
                    ? $item['UF_PERIOD_TO']->format('d.m.Y') : null,
                'date_formed' => $item['UF_DATE_FORMED'] instanceof Date
                    ? $item['UF_DATE_FORMED']->format('d.m.Y') : null,
                'file' => getFilePath($item['UF_FILE']),
            ];
        }
        return $result;
    }

    public static function get($arRequest)
    {
        global $USER;

        $page = (int) ($arRequest['page'] ?? 1);
        $limit = (int) ($arRequest['limit'] ?? 10);

        $params = [
            'order' => ['UF_DATE_FORMED' => 'DESC'],
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
            'filter' => [
                'UF_CONTRAGENT' => $USER->GetID(),
            ],
        ];

        if (!empty($arRequest['contract'])) {
            $params['filter']['UF_CONTRACT_NUMBER'] = $arRequest['contract'];
        }

        $items = Entity::getInstance()->getList(
            Constants::HLBLOCK_B_HL_RECONCILIATION_ACTS,
            $params
        );

        return [
            'count' => count($items),
            'items' => self::processData($items),
        ];
    }
}