<?php

use App\Entity\Telemetry;
use App\Service\TelemetryDenormalizer;
use App\Service\TelemetryJsonValidator;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

class TelemetryDenormalizerTest extends TestCase
{
    /**
     * Ensure that all telemetry files are considered as valid and are parsed without errors.
     */
    public function testTelemetryFiles(): void
    {
        $directory_iterator = new DirectoryIterator(__DIR__ . '/../../tests/fixtures/telemetry');
        /** @var \SplFileObject $file */
        foreach ($directory_iterator as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $contents = file_get_contents($file->getRealPath());
            $this->assertJson($contents);

            $data = json_decode($contents);

            $telemetry = $this->getDenormalizedData($data);

            $this->assertMatchesRegularExpression('/^[a-z0-9]{40}$/i', $telemetry->getGlpiUuid());
            if ($file->getBasename('.json') === '9.2.0') {
                // Special case for GLPI 9.2.0, `glpi.install_mode` was missing.
                $this->assertNull($telemetry->getInstallMode());
            } else {
                $this->assertEquals('TARBALL', $telemetry->getInstallMode());
            }
            $this->assertInstanceOf(DateTimeImmutable::class, $telemetry->getCreatedAt());
            $this->assertInstanceOf(DateTimeImmutable::class, $telemetry->getUpdatedAt());

            // TODO Add some more basic assertions
        }
    }

    /**
     * @dataProvider installModeProvider
     */
    public function testInstallModeValues(string $value): void
    {
        $data = $this->getBaseTelemetryV1Data();
        $data->data->glpi->install_mode = $value;

        $telemetry = $this->getDenormalizedData($data);

        $this->assertEquals($value, $telemetry->getInstallMode());
    }

    /**
     * @return array<array{value: string}>
     */
    public static function installModeProvider(): iterable
    {
        // Expected modes
        yield ['value' => 'TARBALL'];
        yield ['value' => 'GIT'];
        yield ['value' => 'CLOUD'];
        yield ['value' => 'DOCKER'];

        // Other modes
        yield ['value' => 'APT'];
        yield ['value' => 'RPM'];
        yield ['value' => 'TARBALL to FHS'];
    }

    /**
     * Returns a `TelemetryDenormalizer` instance.
     *
     * @param mixed $data
     *
     * @return Telemetry
     */
    private function getDenormalizedData(mixed $data): Telemetry
    {
        $denormalizer = new TelemetryDenormalizer(
            new TelemetryJsonValidator(new Validator(), __DIR__ . '/../../resources/schema')
        );

        $this->assertTrue($denormalizer->supportsDenormalization($data, Telemetry::class));

        $telemetry = $denormalizer->denormalize($data, Telemetry::class);
        $this->assertInstanceOf(Telemetry::class, $telemetry);

        return $telemetry;
    }

    /**
     * Returns base data for Telemetry V1 format.
     *
     * @return stdClass
     */
    private function getBaseTelemetryV1Data(): stdClass
    {
        $data = [
            'data' => [
                'glpi' => [
                    'uuid' => 'FIEttOyzhKyj6bL7nvFBSc1laedyJ2VLUILLnEMW',
                    'version' => '10.0.3',
                    'plugins' => [
                        [
                            'key' => 'myplugin',
                            'version' => '1.0.0',
                        ]
                    ],
                    'default_language' => 'en_GB',
                    'install_mode' => 'TARBALL',
                    'usage' => [
                        'avg_entities' => '0-500',
                        'avg_computers' => '0-500',
                        'avg_networkequipments' => '0-500',
                        'avg_tickets' => '0-500',
                        'avg_problems' => '0-500',
                        'avg_changes' => '0-500',
                        'avg_projects' => '0-500',
                        'avg_users' => '0-500',
                        'avg_groups' => '0-500',
                        'ldap_enabled' => false,
                        'mailcollector_enabled' => false,
                        'notifications_modes' => [],
                    ],
                ],
                'system' => [
                    'db' => [
                        'engine' => 'MySQL Community Server - GPL',
                        'version' => '8.0.35',
                        'size' => '39.2',
                        'log_size' => '',
                        'sql_mode' => 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION',
                    ],
                    'web_server' => [
                        'engine' => 'Apache',
                        'version' => '2.4.56',
                    ],
                    'php' => [
                        'version' => '8.0.30',
                        'modules' => [
                            'Core',
                            'date',
                            'libxml',
                            'openssl',
                            'pcre',
                            'sqlite3',
                            'zlib',
                            'ctype',
                            'curl',
                            'dom',
                            'fileinfo',
                            'filter',
                            'ftp',
                            'hash',
                            'iconv',
                            'json',
                            'mbstring',
                            'SPL',
                            'PDO',
                            'pdo_sqlite',
                            'bz2',
                            'posix',
                            'Reflection',
                            'session',
                            'SimpleXML',
                            'standard',
                            'tokenizer',
                            'xml',
                            'xmlreader',
                            'xmlwriter',
                            'mysqlnd',
                            'apache2handler',
                            'Phar',
                            'exif',
                            'gd',
                            'intl',
                            'ldap',
                            'memcached',
                            'mysqli',
                            'pcntl',
                            'redis',
                            'soap',
                            'sodium',
                            'xmlrpc',
                            'zip',
                            'Zend OPcache',
                        ],
                        'setup' => [
                            'max_execution_time' => '30',
                            'memory_limit' => '128M',
                            'post_max_size' => '8M',
                            'safe_mode' => false,
                            'session' => 'files',
                            'upload_max_filesize' => '2M',
                        ],
                    ],
                    'os' => [
                        'family' => 'Linux',
                        'distribution' => '',
                        'version' => '5.15.0-91-generic',
                    ],
                ],
            ]
        ];

        // Convert array structure to object structure.
        $data = json_decode(json_encode($data));

        return $data;
    }
}
