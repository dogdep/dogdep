<?php return [
    'max_attempts' => env('CHECK_TIMES', 40),
    'attempt_delay' => env('CHECK_DELAY', 30),
];
