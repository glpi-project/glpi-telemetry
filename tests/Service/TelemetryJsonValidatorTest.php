<?php

use App\Service\TelemetryJsonValidator;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class TelemetryJsonValidatorTest extends TestCase
{
    public function testInvalidJson(): void
    {
        $validator = new TelemetryJsonValidator(new Validator(), __DIR__ . '/../../resources/schema');
        $this->assertFalse($validator->validateJson('{"invalid": "data"}'));
    }
}
