<?php

if (! function_exists('is_associative')) {
    function is_associative($array)
    {
        if (! is_array($array) || empty($array)) {
            return false;
        }
        foreach ($array as $key => $value) {
            if (! is_string($key)) {
                return false;
            }
        }
        return true;
    }
}