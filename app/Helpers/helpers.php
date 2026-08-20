<?php

if (!function_exists('formatNumber')) {
    function formatNumber($value)
    {
        $value = $value ?? 0;
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }
}
