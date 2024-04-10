<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ReferenceController;
use App\Repository\ReferenceRepository;
use App\Tests\KernelTestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReferenceControllerTest extends KernelTestCase
{
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
            self::assertTrue(property_exists($entry, 'name'));
            self::assertIsString($entry->name);
            self::assertTrue(property_exists($entry, 'value'));

            $expectedvalue = $data[$entry->name]['value'] ?? 0;
            self::assertEquals($expectedvalue, $entry->value, $entry->name);
        }
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
        self::assertTrue(property_exists($decodedContent, 'type'));
        self::assertEquals('FeatureCollection', $decodedContent->type);

        // Has `features` array property
        self::assertTrue(property_exists($decodedContent, 'features'));
        self::assertIsArray($decodedContent->features);

        foreach ($decodedContent->features as $entry) {
            self::assertIsObject($entry);

            // Each entry has `type: "Feature"`
            self::assertTrue(property_exists($entry, 'type'));
            self::assertEquals('Feature', $entry->type);

            // Each entry has `properties: {cca2: stringname: string}`
            self::assertTrue(property_exists($entry, 'properties'));
            self::assertIsObject($entry->properties);
            self::assertTrue(property_exists($entry->properties, 'cca2'));
            self::assertIsString($entry->properties->cca2);
            self::assertMatchesRegularExpression('/^[a-z]{2}$/', $entry->properties->cca2);
            self::assertTrue(property_exists($entry->properties, 'name'));
            self::assertIsString($entry->properties->name);

            // Each entry has a `geometry` array property
            self::assertTrue(property_exists($entry, 'geometry'));
            self::assertIsObject($entry->geometry);

            // `geometry` has a `type: "(Multi)Polygon"` property
            self::assertTrue(property_exists($entry->geometry, 'type'));
            self::assertIsString($entry->geometry->type);
            self::assertMatchesRegularExpression('/^(Multi)?Polygon$/', $entry->geometry->type);

            // `geometry` has a `coordinates` array property
            self::assertTrue(property_exists($entry->geometry, 'coordinates'));
            self::assertIsArray($entry->geometry->coordinates);

            // `geometry` model depends on its type:
            // - for a `Polygon`, `coordinates` is a list of zones, each containing a list of lon/lat entries;
            // - for a `MultiPolygon`, `coordinates` is a list of zones, each corresponding to a `Polygon`.
            foreach ($entry->geometry->coordinates as $zoneKey => $zoneEntries) {
                self::assertIsInt($zoneKey);
                self::assertIsArray($zoneEntries);

                $validateLonLatCollection = static function ($lonLatCollection): void {
                    self::assertIsArray($lonLatCollection);
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
