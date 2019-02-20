
<?php

if (!function_exists('storage_path')) {
    function storage_path($path)
    {
        return __DIR__ . "/../storage/$path";
    }
}