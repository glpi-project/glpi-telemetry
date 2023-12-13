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
     * Validate a Telemetry JSON string against the expected schema.
     *
     * @param string $json
     * @return bool
     */
    public function validateJson(string $json): bool
    {
        $this->validator
            ->resolver()
            ->registerFile(
                'https://telemetry.glpi-project.org/schema/v1.json',
                $this->schemaDir . '/telemetry.v1.json'
            );

        $result = $this->validator->validate(json_decode($json), 'https://telemetry.glpi-project.org/schema/v1.json');

        return $result->isValid();
    }
}
