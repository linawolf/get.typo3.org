<?php

/*
 * This file is part of the package t3o/get.typo3.org.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace App\Validator;

use App\Entity\SitePackage;
use App\Service\SitePackageBaseService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BasePackageVersionSupportValidator extends ConstraintValidator
{
    private SitePackageBaseService $sitePackageBaseService;

    public function __construct(SitePackageBaseService $sitePackageBaseService)
    {
        $this->sitePackageBaseService = $sitePackageBaseService;
    }

    public function validate(mixed $object, Constraint $constraint)
    {
        if (!$constraint instanceof BasePackageVersionSupport) {
            throw new \LogicException('Invalid constraint type.');
        }

        if (!$object instanceof SitePackage) {
            throw new \LogicException('Object must be of type ' . SitePackage::class);
        }

        $basePackage = $object->getBasePackage();
        $typo3Version = $object->getTypo3Version();

        $package = $this->sitePackageBaseService->getPackage($basePackage);
        if ($package === null || !$package->hasVersionSupport($typo3Version)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ basePackage }}', $basePackage)
                ->setParameter('{{ typo3Version }}', (string)$typo3Version)
                ->addViolation();
        }
    }
}
