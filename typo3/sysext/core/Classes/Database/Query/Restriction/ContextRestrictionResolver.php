<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Database\Query\Restriction;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\RestrictionAwareInterface;

class ContextRestrictionResolver
{
    public static function resolveRestrictions(?Context $context = null): ?array
    {
        if ($context === null) {
            return null;
        }

        $restrictions = [];
        foreach ($context->getAllAspects() as $aspect) {
            if ($aspect instanceof RestrictionAwareInterface) {
                array_merge($restrictions, $aspect->resolveRestrictions());
            }
        }

        return $restrictions;
    }
}
