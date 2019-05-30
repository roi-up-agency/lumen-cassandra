<?php

namespace RoiupAgency\LumenCassandra\Helpers;

class Helper
{
    /**
     * Check if the parameter is a valid UUID
     *
     * @param $value
     * @return bool
     */
    public static function isUuid($value) {
        $pHexa = "[0-9A-F]";
        $pIsUuid = "/^{$pHexa}{8}-{$pHexa}{4}-{$pHexa}{4}-{$pHexa}{4}-{$pHexa}{12}$/i";
        return is_string($value) && (bool)preg_match($pIsUuid, $value);
    }


    public static function isAvoidingQuotes($binding)
    {
        return (
            !strtolower(gettype($binding))
            || Helper::isUuid($binding)
            || is_integer($binding)
            || is_float($binding)
        );
    }
}
