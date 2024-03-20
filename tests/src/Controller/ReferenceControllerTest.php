<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ReferenceController;
use App\Repository\ReferenceRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferenceControllerTest extends WebTestCase
{
    private CacheInterface $cache;
    private ReferenceRepository&MockObject $referenceRepositoryMock;
    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);

        $this->referenceRepositoryMock = $this->createMock(ReferenceRepository::class);

    }

    public function testMapGraphRoute(): Void
    {
        $client = static::createClient();
        $client->request('GET', '/map/graph');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testGetDataForMapGraph(): Void
    {

        $data = [
            'fr' => 100,
            'br' => 200,
            'be' => 300
        ];

        $this->referenceRepositoryMock->expects($this->once())
                ->method('getReferencesCountbyCountries')
                ->willReturn($data);


        $controller = new ReferenceController($this->cache);
        $controller->setContainer(self::getContainer());
        $result = $controller->getDataForMapGraph($this->referenceRepositoryMock);

        $this->assertInstanceOf(JsonResponse::class, $result);

        $decodedResult = json_decode($result->getContent(), true);

        foreach ($decodedResult as $result) {
            $this->assertArrayHasKey("name", $result);
            $this->assertArrayHasKey("value", $result);
                if ($result['name'] === "France") {
                    $this->assertEquals(100, $result['value']);
                }
                if ($result['name'] === "Brazil") {
                    $this->assertEquals(200, $result['value']);
                }
                if ($result['name'] === "Belgium") {
                    $this->assertEquals(300, $result['value']);
                }
        }
    }
}
