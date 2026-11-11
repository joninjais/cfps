<?php

return [
    'op pp' => ['label' => 'ข้อมูล OP / PP', 'route' => '/opdatainfo/import', 'post' => true],
    'ip' => ['label' => 'ข้อมูล IP', 'route' => '/ipdatainfo/import', 'post' => true],
    // 'fwfdata' => ['label' => 'ข้อมูล FWF', 'route' => '/fwfdatabase/import', 'post' => false],
    // 'fwfhealth' => ['label' => 'บัตรประกันสุขภาพ', 'route' => '/fwfhealth/import', 'post' => false],
    'insurance' => ['label' => 'กรมธรรม์ (FDH)', 'route' => '/insurancepolicies/import', 'post' => true],
];
