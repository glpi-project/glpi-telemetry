<?php

use App\Middleware\JsonCheck;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

class JsonCheckTest extends TestCase
{

    public function testInvalidJson()
    {
        $json = '{"invalid": "data"}';

        // Create a Monolog logger with a TestHandler to capture log messages
        $logger = new Logger('test_logger');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);

        // Create an instance of JsonCheck with the Monolog logger
        $jsonCheck = new JsonCheck($logger);

        $isValid = $jsonCheck->validateJson($json);

        // Assert that the JSON is not valid
        $this->assertFalse($isValid);

        // Assert that the log contains the expected error messages
        $logs = $testHandler->getRecords();
        $this->assertLogContains('Validating JSON', $logs);
        $this->assertLogContains('JSON is not valid', $logs);
    }

    private function assertLogContains($expectedMessage, $logs)
    {
        $messageFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], $expectedMessage) !== false) {
                $messageFound = true;
                break;
            }
        }
        $this->assertTrue($messageFound, "Log should contain: $expectedMessage");
    }
}
