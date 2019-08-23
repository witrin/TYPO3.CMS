<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Context;

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

use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The Aspect is usually available as "date.*" properties in the Context.
 *
 * Contains the current time + date + timezone,
 * and needs a DateTimeImmutable object
 *
 * Allowed properties:
 * - timestamp - unix timestamp number
 * - timezone - America/Los_Angeles
 * - iso - datetime as string in ISO 8601 format, e.g. `2004-02-12T15:19:21+00:00`
 * - full - the DateTimeImmutable object
 */
class DateTimeAspect implements AspectInterface, RestrictionAwareInterface
{
    public const TIMESTAMP = 'timestamp';
    public const ISO = 'iso';
    public const TIMEZONE = 'timezone';
    public const FULL = 'full';

    /**
     * @var \DateTimeImmutable
     */
    protected $dateTimeObject;

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     */
    public function __construct(\DateTimeImmutable $dateTimeImmutable)
    {
        $this->dateTimeObject = $dateTimeImmutable;
    }

    /**
     * Fetch a property of the date time object or the object itself ("full").
     *
     * @param string $name
     * @return \DateTimeImmutable|string
     * @throws AspectPropertyNotFoundException
     */
    public function get(string $name)
    {
        switch ($name) {
            case static::TIMESTAMP:
                return $this->dateTimeObject->format('U');
            case static::ISO:
                return $this->dateTimeObject->format('c');
            case static::TIMEZONE:
                return $this->dateTimeObject->format('e');
            case static::FULL:
                return $this->dateTimeObject;
        }
        throw new AspectPropertyNotFoundException('Property "' . $name . '" not found in Aspect "' . __CLASS__ . '".', 1527778767);
    }

    /**
     * Return the full date time object
     *
     * @return \DateTimeImmutable
     */
    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTimeObject;
    }

    public function resolveRestrictions(): array
    {
        return [
            GeneralUtility::makeInstance(StartTimeRestriction::class, $this->get(static::TIMESTAMP)),
            GeneralUtility::makeInstance(EndTimeRestriction::class, $this->get(static::TIMESTAMP)),
        ];
    }
}
