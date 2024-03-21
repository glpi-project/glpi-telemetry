<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ReferenceController;
use App\Repository\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;

class ReferenceControllerTest extends WebTestCase
{
    public function testMapDataRoute(): Void
    {
        $client = static::createClient();
        $client->request('GET', '/reference/map/data');

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMapData(): Void
    {
        $data = [
            'France'    => ['key' => 'fr', 'value' => 100],
            'Brazil'    => ['key' => 'br', 'value' => 200],
            'Belgium'   => ['key' => 'be', 'value' => 300],
        ];

        $referenceRepositoryMock = $this->createMock(ReferenceRepository::class);
        $referenceRepositoryMock->method('getReferencesCountbyCountries')
            ->willReturn(array_combine(array_column($data, 'key'), array_column($data, 'value')));

        $controller = new ReferenceController();
        $controller->setContainer(self::getContainer());
        $result = $controller->mapData($referenceRepositoryMock);

        self::assertInstanceOf(JsonResponse::class, $result);

        $content = $result->getContent();
        self::assertIsString($content);

        $decodedContent = json_decode($content);
        self::assertIsArray($decodedContent);

        foreach ($decodedContent as $entry) {
            self::assertIsObject($entry);
            self::assertObjectHasProperty('name', $entry);
            self::assertIsString($entry->name);
            self::assertObjectHasProperty('value', $entry);

            $expectedvalue = $data[$entry->name]['value'] ?? 0;
            self::assertEquals($expectedvalue, $entry->value, $entry->name);
        }
    }

    public function testMapCountriesRoute(): Void
    {
        $client = static::createClient();
        $client->request('GET', '/reference/map/countries');

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMapCountries(): Void
    {
        $cache = new NullAdapter(); // Prevents cache to be used

        $controller = new ReferenceController();
        $controller->setContainer(self::getContainer());
        $result = $controller->mapCountries($cache);

        self::assertInstanceOf(JsonResponse::class, $result);

        $content = $result->getContent();
        self::assertIsString($content);

        $decodedContent = json_decode($content);
        self::assertIsObject($decodedContent);

        // Has `type: "Feature"` property
        self::assertObjectHasProperty('type', $decodedContent);
        self::assertEquals('FeatureCollection', $decodedContent->type);

        // Has `features` array property
        self::assertObjectHasProperty('features', $decodedContent);
        self::assertIsArray($decodedContent->features);

        foreach ($decodedContent->features as $entry) {
            self::assertIsObject($entry);

            // Each entry has `type: "Feature"`
            self::assertObjectHasProperty('type', $entry, json_encode($entry, JSON_PRETTY_PRINT));
            self::assertEquals('Feature', $entry->type);

            // Each entry has `properties: {cca2: stringname: string}`
            self::assertObjectHasProperty('properties', $entry);
            self::assertIsObject($entry->properties);
            self::assertObjectHasProperty('cca2', $entry->properties);
            self::assertIsString($entry->properties->cca2);
            self::assertMatchesRegularExpression('/^[a-z]{2}$/', $entry->properties->cca2);
            self::assertObjectHasProperty('name', $entry->properties);
            self::assertIsString($entry->properties->name);

            // Each entry has a `geometry` array property
            self::assertObjectHasProperty('geometry', $entry);
            self::assertIsObject($entry->geometry);

            // `geometry` has a `type: "(Multi)Polygon"` property
            self::assertObjectHasProperty('type', $entry->geometry);
            self::assertMatchesRegularExpression('/^(Multi)?Polygon$/', $entry->geometry->type);

            // `geometry` has a `coordinates` array property
            self::assertObjectHasProperty('coordinates', $entry->geometry);
            self::assertIsArray($entry->geometry->coordinates);

            // `geometry` model depends on its type:
            // - for a `Polygon`, `coordinates` is a list of zones, each containing a list of lon/lat entries;
            // - for a `MultiPolygon`, `coordinates` is a list of zones, each corresponding to a `Polygon`.
            foreach ($entry->geometry->coordinates as $zoneKey => $zoneEntries) {
                self::assertIsInt($zoneKey);
                self::assertIsArray($zoneEntries);

                $validateLonLatCollection = static function ($lonLatCollection): void {
                    foreach ($lonLatCollection as $key => $lonLat) {
                        self::assertIsInt($key);
                        self::assertIsArray($lonLat);
                        self::assertArrayHasKey(0, $lonLat);
                        self::assertIsNumeric($lonLat[0]);
                        self::assertArrayHasKey(1, $lonLat);
                        self::assertIsNumeric($lonLat[1]);
                    }
                };

                if ($entry->geometry->type === 'MultiPolygon') {
                    foreach ($zoneEntries as $subZoneKey => $subZoneEntries) {
                        self::assertIsInt($subZoneKey);
                        $validateLonLatCollection($subZoneEntries);
                    }
                } else {
                    $validateLonLatCollection($zoneEntries);
                }
            }
        }
    }
}
