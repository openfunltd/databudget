<?php

class TypeHelper
{
    public static function getTypeConfig()
    {
        return [
            'unit' => [
                'name' => '預算單位',
                'icon' => 'fas fa-fw fa-calendar-day',
                'cols' => [
                    '機關編號',
                    '機關名稱',
                ],
                'default_aggs' => [
                ],
                'item_features' => [
                ],
            ],
            'proposed_budget_project' => [
                'name' => '預算案-工作計畫',
                'icon' => 'fas fa-fw fa-calendar-day',
                'cols' => [
                    '單位代碼',
                    '年度',
                    '單位',
                    '工作計畫編號',
                    '工作計畫名稱',
                ],
                'default_aggs' => [
                    '單位代碼',
                    '年度',
                    '單位',
                ],
                'item_features' => [
                ],
            ],
            'proposed_budget_branch_project' => [
                'name' => '預算案-分支計畫',
                'icon' => 'fas fa-fw fa-calendar-day',
                'cols' => [
                    '單位代碼',
                    '年度',
                    '單位',
                    '工作計畫編號',
                    '工作計畫名稱',
                    '分支計畫編號',
                    '分支計畫名稱',
                    '金額',
                    '承辦單位',
                ],
                'default_aggs' => [
                    '單位代碼',
                    '年度',
                    '單位',
                ],
                'item_features' => [
                ],
            ],
            'proposed_budget_sub_branch_project' => [
                'name' => '預算案-子分支計畫',
                'icon' => 'fas fa-fw fa-calendar-day',
                'cols' => [
                    '單位代碼',
                    '年度',
                    '單位',
                    '工作計畫編號',
                    '工作計畫名稱',
                    '分支計畫編號',
                    '分支計畫名稱',
                    '金額',
                ],
                'default_aggs' => [
                    '單位代碼',
                    '年度',
                    '單位',
                ],
                'item_features' => [
                ],
            ],
        ];
    }

    public static function getColumns($type)
    {
        $config = self::getTypeConfig();
        return $config[$type]['cols'] ?? [];
    }

    public static function getDataColumn($type)
    {
        $type = str_replace('_', '', $type);
        return $type . 's';
    }

    public static function getDataByID($type, $id)
    {
        $ret = LYAPI::apiQuery("/{$type}/" . urlencode($id), "抓取 {$type} 的 {$id} 資料");
        return $ret;
    }

    public static function getData($data, $type)
    {
        return $data->{self::getDataColumn($type)} ?? [];
    }

    public static function getAPIURL($type)
    {
        if (getenv('LYAPI_HOST')) {
            $url = 'https://' . getenv('LYAPI_HOST');
        } else {
            $url = 'https://v2.ly.govapi.tw';
        }
        return "{$url}/{$type}s";
    }

    public static function getDataFromAPI($type)
    {
        $agg = self::getCurrentAgg($type);
        $url = self::getAPIURL($type);
        $terms = [];
        foreach ($agg as $field) {
            $terms[] = "agg=" . urlencode($field);
        }
        if ($terms) {
            $url .= '?' . implode('&', $terms);
        }
        return LYAPI::apiQuery($url, "抓取 {$type} 的資料");
    }

    public static function getCurrentFilter()
    {
        $config = self::getTypeConfig();
        $query_string = $_SERVER['QUERY_STRING'];
        $terms = explode('&', $query_string);
        $filter = [];
        foreach ($terms as $term) {
            list($k, $v) = array_map('urldecode', explode('=', $term));
            if ($k === 'filter') {
                $filter[] = explode(':', $v, 2);
            }
        }
        return $filter;
    }

    public static function getCurrentAgg($type)
    {
        $config = self::getTypeConfig();
        $query_string = $_SERVER['QUERY_STRING'];
        $terms = explode('&', $query_string);
        $agg = [];
        foreach ($terms as $term) {
            list($k, $v) = array_map('urldecode', explode('=', $term));
            if ($k === 'agg') {
                $agg[] = $v;
            }
        }
        if ($agg) {
            return $agg;
        }

        return $config[$type]['default_aggs'] ?? [];
    }

    public static function getRecordList($data, $prefix = '')
    {
        if (is_scalar($data)) {
            return [[
                'key' => rtrim($prefix, '.'),
                'value' => $data,
            ]];
        }

        if (is_array($data)) {
            $ret = [];
            foreach ($data as $idx => $item) {
                $ret = array_merge(
                    $ret,
                    self::getRecordList($item, rtrim($prefix, '.') . "[{$idx}].")
                );
            }
            return $ret;
        }

        $ret = [];
        foreach ($data as $k => $v) {
            $ret = array_merge(
                $ret,
                self::getRecordList($v, "{$prefix}{$k}.")
            );
        }
        return $ret;
    }

    public static function getItemFeatures($type)
    {
        $config = self::getTypeConfig();
        $features = $config[$type]['item_features'] ?? [];
        $features['rawdata'] = '原始資料';
        return $features;
    }

    public static function getCollectionFeatures($type)
    {
        $config = self::getTypeConfig();
        $features = $config[$type]['collection_features'] ?? [];
        $features['table'] = '列表';
        return $features;
    }
}
