<?php

namespace App\Middleware;

//use opis json schema
use Opis\JsonSchema\{
    Validator,
    ValidationResult,
    Errors\ErrorFormatter,
    ValidationError
};
use Psr\Log\LoggerInterface;

class JsonCheck
{
    private $logger;
    private $validator;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->validator = new Validator();
    }

    public function validateJson($json)
    {
        //get the absolute path of the schema in the config folder
        $urlschema = realpath(__DIR__ . '/../../config/schema.json');
        $schema = file_get_contents($urlschema);

        $this->logger->debug('Validating JSON');
        $this->logger->debug('JSON: ' . json_encode($json));
        $this->logger->debug('Schema: ' . $schema);

        $result = $this->validator->validate($json, $schema);

        if ($result->isValid()) {
            $this->logger->debug('JSON is valid');
            return true;
        }

        if ($result->hasError()) {
            $this->logger->debug('JSON is not valid');
            $error = $result->error();
            $this->logger->debug('Errors: ' . $error);
            return false;
        }
    }
}
