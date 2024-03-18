<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ReferenceController;
use App\Repository\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferenceControllerTest extends WebTestCase
{
    private CacheInterface $cache;
    private ReferenceRepository $referenceRepository;
    private Request $request;
    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);

        $this->referenceRepository = $this->getMockBuilder(ReferenceRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getReferencesCountbyCountries'])
            ->getMock();

        $this->request = $this->createMock(Request::class);

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

        $referenceRepositoryMock = $this->referenceRepository->expects($this->once())
                                        ->method('getReferencesCountbyCountries')
                                        ->willReturn($data);

        $expectedData = [
            [
                'name' => 'France',
                'value' => 100
            ],
            [
                'name' => 'Brazil',
                'value' => 200
            ],
            [
                'name' => 'Belgium',
                'value' => 300
            ]
        ];

        $controller = new ReferenceController($this->cache);
        $result = $controller->getDataForMapGraph($referenceRepositoryMock);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedData), $result->getContent());


    }
}
