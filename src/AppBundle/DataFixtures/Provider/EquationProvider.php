<?php

namespace AppBundle\DataFixtures\Provider;

class EquationProvider
{
    public static function subtract($current, $var)
    {
        $newVar = (int) $var;
        return ($current - $newVar);
    }
}
