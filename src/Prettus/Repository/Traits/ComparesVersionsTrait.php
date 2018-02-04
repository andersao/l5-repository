<?php
namespace Prettus\Repository\Traits;

/**
 * Trait ComparesVersionsTrait
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contato@andersonandra.de>
 */
trait ComparesVersionsTrait
{
    /**
     * Version compare function that can compare both Laravel and Lumen versions.
     *
     * @param   string      $frameworkVersion
     * @param   string      $compareVersion
     * @param   string|null $operator
     * @return  mixed
     */
    public function versionCompare($frameworkVersion, $compareVersion, $operator = null)
    {
        // Lumen (5.5.2) (Laravel Components 5.5.*)
        $lumenPattern = '/Lumen \((\d\.\d\.[\d|\*])\)( \(Laravel Components (\d\.\d\.[\d|\*])\))?/';

        if (preg_match($lumenPattern, $frameworkVersion, $matches)) {
            $frameworkVersion = isset($matches[3]) ? $matches[3] : $matches[1]; // Prefer Laravel Components version.
        }

        return version_compare($frameworkVersion, $compareVersion, $operator);
    }
}
