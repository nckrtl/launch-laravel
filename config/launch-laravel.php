<?php

declare(strict_types=1);

return [
    'defaults' => [
        'strict_models' => true,
        'auto_eager_load' => true,
        'immutable_dates' => true,
        'force_https' => true,
        'prohibit_destructive_commands' => true,
        'aggressive_prefetching' => true,
        'prevent_stray_requests' => true,
        'fake_sleep' => true,
        'default_password_rules' => true,
    ],
];
