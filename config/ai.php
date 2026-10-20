<?php

return [
    'enabled' => (bool) env('AI_API_ENABLED', false),
    'per_token_per_minute' => 60,
    'per_user_per_minute' => 120,
];
