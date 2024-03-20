<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ReferenceController;
use App\Repository\ReferenceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferenceControllerTest extends WebTestCase
{
    public function testMapDataRoute(): Void
    {
        $client = static::createClient();
        $client->request('GET', 'reference/map/data');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMapData(): Void
    {

        $data = [
            'fr' => 100,
            'br' => 200,
            'be' => 300
        ];

        $referenceRepositoryMock = $this->createMock(ReferenceRepository::class);
        $referenceRepositoryMock->expects($this->once())
            ->method('getReferencesCountbyCountries')
            ->willReturn($data);

        $controller = new ReferenceController();
        $controller->setContainer(self::getContainer());
        $result = $controller->mapData($referenceRepositoryMock);

        $this->assertInstanceOf(JsonResponse::class, $result);

        $decodedResult = json_decode($result->getContent(), true);

        foreach ($decodedResult as $result) {
            $this->assertArrayHasKey("name", $result);
            $this->assertIsString($result['name']);
            $this->assertArrayHasKey("value", $result);

            $expectedvalue = 0;
            match ($result['name']) {
                'France' => $data['fr'],
                'Brazil' => $data['br'],
                'Belgium' => $data['be'],
                default => 0,
            };
            $this->assertEquals($expectedvalue, $result['value']);
        }
    }
}
