<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\GlpiPlugin;
use App\Entity\Telemetry;
use App\Entity\TelemetryGlpiPlugin;
use App\Repository\GlpiPluginRepository;
use App\Service\TelemetryDenormalizer;
use DateTimeImmutable;
use DirectoryIterator;
use Doctrine\ORM\EntityManager;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class TelemetryDenormalizerTest extends TestCase
{
    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
    }

    /**
     * Ensure that all telemetry files are considered as valid.
     */
    public function testValidateTelemetryJsonForTelemetryFiles(): void
    {
        $directory_iterator = new DirectoryIterator(__DIR__ . '/../../../tests/fixtures/telemetry');
        /** @var \SplFileObject $file */
        foreach ($directory_iterator as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertJson($contents);

            $denormalizer = $this->getDenormalizerInstance();
            $reflection = new ReflectionClass($denormalizer);
            $method = $reflection->getMethod('validateTelemetryJson');
            $method->setAccessible(true);

            $data = json_decode($contents);
            self::assertTrue($method->invokeArgs($denormalizer, [$data]));
        }
    }

    public function testValidateTelemetryJsonWithInvalidData(): void
    {
        $denormalizer = $this->getDenormalizerInstance();
        $reflection = new ReflectionClass($denormalizer);
        $method = $reflection->getMethod('validateTelemetryJson');
        $method->setAccessible(true);

        $data = json_decode('{"invalid": "data"}');
        self::assertFalse($method->invokeArgs($denormalizer, [$data]));
    }

    /**
     * Ensure that all telemetry files are processed as expected.
     */
    public function testTelemetryFiles(): void
    {
        $directory_iterator = new DirectoryIterator(__DIR__ . '/../../../tests/fixtures/telemetry');
        /** @var \SplFileObject $file */
        foreach ($directory_iterator as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertJson($contents);

            $data = json_decode($contents);

            $telemetry = $this->getDenormalizedData($data);

            if ($file->getBasename('.json') === '9.2.0') {
                // Special case for GLPI 9.2.0, `glpi.install_mode` was missing.
                self::assertNull($telemetry->getInstallMode());
            } else {
                self::assertEquals('TARBALL', $telemetry->getInstallMode());
            }
            self::assertInstanceOf(DateTimeImmutable::class, $telemetry->getCreatedAt());
            self::assertInstanceOf(DateTimeImmutable::class, $telemetry->getUpdatedAt());
            self::assertEquals('IZM6hxPNpegwAdAaErWCWyKaN7DCCWaGfdvUKuI6', $telemetry->getGlpiUuid());
            $glpiVersion = $telemetry->getGlpiVersion();
            self::assertIsString($glpiVersion);
            self::assertMatchesRegularExpression('/^\d+(\.\d+)+$/', $glpiVersion);
            self::assertEquals('fr_FR', $telemetry->getGlpiDefaultLanguage());
            self::assertEquals('0-500', $telemetry->getGlpiAvgEntities());
            self::assertEquals('0-500', $telemetry->getGlpiAvgComputers());
            self::assertEquals('0-500', $telemetry->getGlpiAvgNetworkequipments());
            self::assertEquals('0-500', $telemetry->getGlpiAvgTickets());
            self::assertEquals('0-500', $telemetry->getGlpiAvgProblems());
            self::assertEquals('0-500', $telemetry->getGlpiAvgChanges());
            self::assertEquals('0-500', $telemetry->getGlpiAvgProjects());
            self::assertEquals('0-500', $telemetry->getGlpiAvgUsers());
            self::assertEquals('0-500', $telemetry->getGlpiAvgGroups());
            self::assertFalse($telemetry->isGlpiLdapEnabled());
            self::assertFalse($telemetry->isGlpiMailcollectorEnabled());
            self::assertEquals('[]', $telemetry->getGlpiNotifications());
            self::assertEquals('MySQL Community Server - GPL', $telemetry->getDbEngine());
            self::assertEquals('8.0.35', $telemetry->getDbVersion());
            self::assertIsInt($telemetry->getDbSize());
            self::assertNull($telemetry->getDbLogSize());
            self::assertIsString($telemetry->getDbSqlMode());
            self::assertEquals('Apache', $telemetry->getWebEngine());
            self::assertEquals('2.4.25', $telemetry->getWebVersion());
            $phpVersion = $telemetry->getPhpVersion();
            self::assertIsString($phpVersion);
            self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $phpVersion);
            $phpModules = $telemetry->getPhpModules();
            self::assertIsString($phpModules);
            self::assertJson($phpModules);
            self::assertEquals(30, $telemetry->getPhpConfigMaxExecutionTime());
            self::assertEquals('128M', $telemetry->getPhpConfigMemoryLimit());
            self::assertEquals('8M', $telemetry->getPhpConfigPostMaxSize());
            self::assertFalse($telemetry->isPhpConfigSafeMode());
            self::assertEquals('files', $telemetry->getPhpConfigSession());
            self::assertEquals('2M', $telemetry->getPhpConfigUploadMaxFilesize());
            self::assertEquals('Linux', $telemetry->getOsFamily());
            self::assertEquals('', $telemetry->getOsDistribution());
            self::assertEquals('5.15.0-91-generic', $telemetry->getOsVersion());

            self::assertIsIterable($telemetry->getTelemetryGlpiPlugins());
            $plugins_keys = [];
            foreach ($telemetry->getTelemetryGlpiPlugins() as $plugin) {
                self::assertInstanceOf(TelemetryGlpiPlugin::class, $plugin);
                $glpiPlugin = $plugin->getGlpiPlugin();
                self::assertInstanceOf(GlpiPlugin::class, $glpiPlugin);
                $pluginVersion = $plugin->getVersion();
                self::assertIsString($pluginVersion);
                self::assertFalse(strlen($pluginVersion) === 0);
                $plugins_keys[] = $glpiPlugin->getPkey();
            }
            self::assertEquals(
                $plugins_keys,
                [
                    'fields',
                    'formcreator',
                    version_compare($glpiVersion, '10.0.0', '<') ? 'fusioninventory' : 'glpiinventory'
                ]
            );
        }
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
     * @dataProvider installModeProvider
     */
    public function testInstallModeValues(string $value): void
    {
        $data = $this->getBaseTelemetryV1Data();
        $this->propertyAccessor->setValue($data, 'data.glpi.install_mode', $value);

        $telemetry = $this->getDenormalizedData($data);

        self::assertEquals($value, $telemetry->getInstallMode());
    }

    /**
     * @return array<array{value: string}>
     */
    public static function defaultLanguageProvider(): iterable
    {
        // Known values exported from GLPI 10.0.
        $values = [
            'ar_SA',
            'bg_BG',
            'id_ID',
            'ms_MY',
            'ca_ES',
            'cs_CZ',
            'de_DE',
            'da_DK',
            'et_EE',
            'en_GB',
            'en_US',
            'es_AR',
            'es_EC',
            'es_CO',
            'es_ES',
            'es_419',
            'es_MX',
            'es_VE',
            'eu_ES',
            'fr_FR',
            'fr_CA',
            'fr_BE',
            'gl_ES',
            'el_GR',
            'he_IL',
            'hi_IN',
            'hr_HR',
            'hu_HU',
            'it_IT',
            'kn',
            'lv_LV',
            'lt_LT',
            'mn_MN',
            'nl_NL',
            'nl_BE',
            'nb_NO',
            'nn_NO',
            'fa_IR',
            'pl_PL',
            'pt_PT',
            'pt_BR',
            'ro_RO',
            'ru_RU',
            'sk_SK',
            'sl_SI',
            'sr_RS',
            'fi_FI',
            'sv_SE',
            'vi_VN',
            'th_TH',
            'tr_TR',
            'uk_UA',
            'ja_JP',
            'zh_CN',
            'zh_TW',
            'ko_KR',
            'zh_HK',
            'be_BY',
            'is_IS',
            'eo',
            'es_CL',
        ];

        foreach ($values as $value) {
            yield ['value' => $value];
        }
    }

    /**
     * @dataProvider defaultLanguageProvider
     */
    public function testGlpiDefaultLanguageValues(string $value): void
    {
        $data = $this->getBaseTelemetryV1Data();
        $this->propertyAccessor->setValue($data, 'data.glpi.default_language', $value);

        $telemetry = $this->getDenormalizedData($data);

        $defaultLanguage = $telemetry->getGlpiDefaultLanguage();
        self::assertIsString($defaultLanguage);
        self::assertMatchesRegularExpression('/^[a-z]{2}(_[A-Z0-9]{2,3})?$/', $defaultLanguage);
    }

    /**
     * Returns a `TelemetryDenormalizer` instance.
     *
     * @return TelemetryDenormalizer
     */
    private function getDenormalizerInstance(): TelemetryDenormalizer
    {
        return new TelemetryDenormalizer(
            new Validator(),
            __DIR__ . '/../../../resources/schema',
            $this->createMock(GlpiPluginRepository::class)
        );
    }

    /**
     * Returns denormalized data.
     *
     * @param mixed $data
     *
     * @return Telemetry
     */
    private function getDenormalizedData(mixed $data): Telemetry
    {
        $denormalizer = $this->getDenormalizerInstance();

        self::assertTrue($denormalizer->supportsDenormalization($data, Telemetry::class));

        $telemetry = $denormalizer->denormalize($data, Telemetry::class);
        self::assertInstanceOf(Telemetry::class, $telemetry);

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
                    'uuid' => 'IZM6hxPNpegwAdAaErWCWyKaN7DCCWaGfdvUKuI6',
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
        /** @var \stdClass $data */
        $data = json_decode(
            json_encode($data, flags: JSON_THROW_ON_ERROR),
            flags: JSON_THROW_ON_ERROR
        );

        return $data;
    }
}
