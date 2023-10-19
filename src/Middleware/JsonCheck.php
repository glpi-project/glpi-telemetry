<?php

namespace App\Middleware;

use Opis\JsonSchema\{
    Validator,
    SchemaLoader
};
use Opis\JsonSchema\Errors\{
    ErrorFormatter,
};
use Psr\Log\LoggerInterface;

class JsonCheck
{
    private $logger;
    private $validator;
    private $loader;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->validator = new Validator();
        $this->loader = new SchemaLoader();
    }

    public function validateJson($json): bool
    {
        //get the absolute path of the schema in the config folder
        $schemaFile = __DIR__ . '/../../config/schema.json';
        $resolver   = $this->validator->resolver();
        $resolver->registerFile('https://example.com/schema.json', $schemaFile);

        $this->logger->debug('Validating JSON');
        $this->logger->debug('JSON: ' . json_encode($json));

        $result = $this->validator->validate($json, 'https://example.com/schema.json');

        if ($result->isValid()) {
            $this->logger->debug('JSON is valid');
            $valid = true;
        }

        if ($result->hasError()) {
            $this->logger->debug('JSON is not valid');
            $error = $result->error();

            $formatter = new ErrorFormatter();
            $this->logger->debug(json_encode($formatter->formatOutput($error, "detailed")));
            $valid = false;
        }

        return $valid;
    }
}
