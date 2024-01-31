<?php

namespace App\Service;

use Opis\JsonSchema\Validator;

class TelemetryJsonValidator
{
    /**
     * Directory containing schema files.
     */
    private string $schemaDir;

    /**
     * JSON data/schema validator.
     */
    private Validator $validator;

    public function __construct(Validator $validator, string $schemaDir)
    {
        $this->validator = $validator;
        $this->schemaDir = $schemaDir;
    }

    /**
     * Validate a Telemetry request contents against the expected schema.
     *
     * @param mixed $contents
     * @return bool
     */
    public function validateJson(mixed $contents): bool
    {
        $this->validator
            ->resolver()
            ->registerFile(
                'https://telemetry.glpi-project.org/schema/v1.json',
                $this->schemaDir . '/telemetry.v1.json'
            );

        $result = $this->validator->validate($contents, 'https://telemetry.glpi-project.org/schema/v1.json');

        return $result->isValid();
    }
}
