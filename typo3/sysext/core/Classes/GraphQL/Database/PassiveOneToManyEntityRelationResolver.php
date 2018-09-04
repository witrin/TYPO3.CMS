<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use TYPO3\CMS\Core\Configuration\MetaModel\ActivePropertyRelation;
use TYPO3\CMS\Core\Configuration\MetaModel\PropertyDefinition;
use Webmozart\Assert\Assert;

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

class PassiveOneToManyEntityRelationResolver extends AbstractPassiveEntityRelationResolver
{

    public static function canResolve(PropertyDefinition $propertyDefinition)
    {
        if ($propertyDefinition->isManyToManyRelationProperty()) {
            return false;
        }

        foreach ($propertyDefinition->getActiveRelations() as $activeRelation) {
            if (!($activeRelation instanceof ActivePropertyRelation)) {
                return false;
            }
        }

        return true;
    }

    protected function getTable(): string
    {
        return reset($this->getPropertyDefinition()->getRelationTableNames());
    }

    protected function getType(array $source): string
    {
        return reset($this->getPropertyDefinition()->getRelationTableNames());
    }

    protected function getForeignKeyField(): string
    {
        Assert::count($this->getPropertyDefinition()->getActiveRelations(), 1);

        $activeRelation = reset($this->getPropertyDefinition()->getActiveRelations());

        Assert::isInstanceOf($activeRelation, ActivePropertyRelation::class);

        return $activeRelation->getTo()->getName();
    }
}