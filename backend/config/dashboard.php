<?php

return [
    'attention_days' => (int) env('DASHBOARD_ATTENTION_DAYS', 14),

    // Reference date for demo: payments older than attention_days from this date show "needs_attention"
    'reference_date' => env('DASHBOARD_REFERENCE_DATE', null),

    'import_exclude_keywords' => [
        'НДФЛ',
        'ЕНС',
        'депозит',
        'процент',
        'Комиссия',
        'Заработная плата',
        'аренд',
        'Перевод средств предпринимателя',
        'Перечисление средств во вклад',
        'Возврат депозита',
    ],
];
