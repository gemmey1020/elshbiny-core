<?php

return [
    'pass_gate' => [
        'enabled' => env('OMEGA_PASS_GATE_ENABLED', true),
        'secret' => env('OMEGA_PASS_GATE_SECRET'),
    ],
];
