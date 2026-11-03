<?php

if (!function_exists('parse_ini_custom')) {
    function parse_ini_custom(string $filepath): array {
        if (file_exists($filepath)) {
            return parse_ini_file($filepath, true) ?: [];
        }
        return [];
    }
}

