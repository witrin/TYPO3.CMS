<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Configuration\MetaModel;

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

class EntityRelationMap
{
    /**
     * @var EntityDefinition[]
     */
    private $entityDefinitions = [];

    private function __construct(EntityDefinition ...$entityDefinitions)
    {
        $this->entityDefinitions = $entityDefinitions;
    }

    /**
     * @param string $name
     * @return null|EntityDefinition
     */
    public function getEntityDefinition(string $name): ?EntityDefinition {
        return ($this->entityDefinitions[$name] ?? null);
    }

    /**
     * @return EntityDefinition[]
     */
    public function getEntityDefinitions()
    {
        return $this->entityDefinitions;
    }
}
