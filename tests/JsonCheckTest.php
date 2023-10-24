<?php

use App\Middleware\JsonCheck;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class JsonCheckTest extends TestCase
{
    public function testInvalidJson()
    {
        $json = '{"invalid": "data"}';

        $logger = $this->createMock(LoggerInterface::class);

        $jsonCheck = new JsonCheck($logger);

        $isValid = $jsonCheck->validateJson($json);

        $this->assertFalse($isValid);
    }
}
