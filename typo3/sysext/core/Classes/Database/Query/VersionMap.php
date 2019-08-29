<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Database\Query;

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

class VersionMap
{
    /**
     * @var array
     */
    private $map = [];

    public static function fromArray(array $array)
    {
        $target = new static();
        $target->map = $array;
        return $target;
    }

    private function __construct()
    {
    }

    public function has(int $versionId)
    {
        return isset($this->map[$versionId]);
    }

    public function getLiveId(int $versionId): ?int
    {
        return $this->map[$versionId]['t3ver_oid'] ?? null;
    }

    public function getPageId(int $versionId): ?int
    {
        return $this->map[$versionId]['pid'] ?? null;
    }
}
