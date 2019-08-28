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

/**
 * This is the container with restrictions that are added based on the context
 */
class ContextRestrictionContainer extends AbstractRestrictionContainer
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Creates instances of restrictions based on the Context
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        foreach ($context->getAllAspects() as $aspect) {
            if ($aspect instanceof RestrictionAwareInterface) {
                foreach ($aspect->resolveRestrictions() as $restriction) {
                    $this->add($restriction);
                }
            }
        }
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
