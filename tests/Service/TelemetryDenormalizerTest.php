<?php

use App\Service\TelemetryDenormalizer;
use App\Service\TelemetryJsonValidator;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use App\Entity\Telemetry;

class TelemetryDenormalizerTest extends TestCase
{
    /**
     * Ensure that all telemetry files are considered as valid and are parsed without errors.
     */
    public function testTelemetryFiles(): void
    {
        $denormalizer = new TelemetryDenormalizer(
            new TelemetryJsonValidator(new Validator(), __DIR__ . '/../../resources/schema')
        );

        $directory_iterator = new DirectoryIterator(__DIR__ . '/../../tests/fixtures/telemetry');
        /** @var \SplFileObject $file */
        foreach ($directory_iterator as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $contents = file_get_contents($file->getRealPath());
            $this->assertJson($contents);

            $data = json_decode($contents);

            $this->assertTrue($denormalizer->supportsDenormalization($data, Telemetry::class));

            $telemetry = $denormalizer->denormalize($data, Telemetry::class);
            $this->assertInstanceOf(Telemetry::class, $telemetry);
        }
    }
}
