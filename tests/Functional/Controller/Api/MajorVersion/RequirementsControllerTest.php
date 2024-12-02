<?php

declare(strict_types=1);

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

namespace App\Tests\Functional\Controller\Api\MajorVersion;

use App\DataFixtures\MajorVersionFixtures;
use App\DataFixtures\ReleaseFixtures;
use App\DataFixtures\RequirementFixtures;
use App\Tests\Functional\Controller\Api\ApiCase;
use Symfony\Component\HttpFoundation\Response;

class RequirementsControllerTest extends ApiCase
{
    /**
     * @test
     */
    public function addRequirementUnauthorized(): void
    {
        $response = $this->createRequirementFromJson('Json/Requirement-10-0.json', '10');
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function addRequirementAuthorized(): void
    {
        $this->logIn();
        $this->createMajorVersionFromJson('Json/MajorVersion-10.json');

        $response = $this->createRequirementFromJson('Json/Requirement-10-0.json', '10');
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame(['status' => 'success', 'Location' => '/v1/api/major/10'], $this->decodeResponse($response));

        $response = $this->createRequirementFromJson('Json/Requirement-10-1.json', '10');
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame(['status' => 'success', 'Location' => '/v1/api/major/10'], $this->decodeResponse($response));
    }

    /**
     * @test
     */
    public function getRequirementsByMajorVersionStructureTest(): void
    {
        $this->addFixture(new MajorVersionFixtures());
        $this->addFixture(new ReleaseFixtures());
        $this->addFixture(new RequirementFixtures());
        $this->executeFixtures();

        $this->client->request('GET', '/api/v1/major/10/requirements');

        $response = $this->client->getResponse();
        $responseContent = json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($responseContent);
        $this->assertArrayStructure(
            [
                [
                    'category' => 'string',
                    'name' => 'string',
                    '?min' => 'string',
                    '?max' => 'string',
                ],
            ],
            $responseContent
        );
    }
}
