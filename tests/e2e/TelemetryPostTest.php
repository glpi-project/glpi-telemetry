<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Entity\GlpiPlugin;
use App\Entity\Telemetry;
use App\Entity\TelemetryGlpiPlugin;
use App\Tests\PantherTestCase;
use DirectoryIterator;
use Symfony\Component\HttpFoundation\Response;

class TelemetryPostTest extends PantherTestCase
{
    public function testInvalidHeaderPost(): void
    {
        $client = $this->getHttpClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'text/html']);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getInternalResponse()->getStatusCode());
        $content = $client->getInternalResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString('{"error":"Bad request"}', $content);
    }

    public function testInvalidJsonPost(): void
    {
        $client = $this->getHttpClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'application/json'], '{"test": "test"}');
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getInternalResponse()->getStatusCode());
        $content = $client->getInternalResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString('{"error":"Bad request"}', $content);
    }

    public function testMultiplePostWithinTheSameDay(): void
    {
        $client = $this->getHttpClient();
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        $repository = $doctrine->getManager()->getRepository(Telemetry::class);

        $postContent = file_get_contents(__DIR__ . '/../../tests/fixtures/telemetry/10.0.10.json');
        self::assertIsString($postContent);
        self::assertJson($postContent);

        // Ensure UUID is unique to prevent POST to be ignored du to an existing previous entry on same day
        $uniqueUuid = bin2hex(random_bytes(20));
        $postContent = preg_replace('/"uuid"\s*:\s*"[^"]+"/', sprintf('"uuid": "%s"', $uniqueUuid), $postContent);

        for ($i = 0; $i < 5; $i++) {
            // Send the request
            $client->request(
                method: 'POST',
                uri: '/telemetry',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: $postContent,
            );
            self::assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());
            $responseContent = $client->getInternalResponse()->getContent();
            self::assertIsString($responseContent);
            self::assertJsonStringEqualsJsonString(
                $i === 0 ? '{"message":"OK"}' : '{"message":"The report was ignored because a previous report has already been sent today."}',
                $responseContent,
            );

            // Validates that only the first telemetry POST is saved in DB
            $telemetryEntries = $repository->findBy(['glpi_uuid' => $uniqueUuid]);
            self::assertCount(1, $telemetryEntries);
            self::assertInstanceOf(Telemetry::class, reset($telemetryEntries));
        }
    }

    public function testSuccessfullPost(): void
    {
        $uniqueUuid = bin2hex(random_bytes(20));

        $postContent = <<<JSON
            {
                "data": {
                    "glpi": {
                        "uuid": "{$uniqueUuid}",
                        "version": "10.0.10",
                        "plugins": [
                            {
                                "key": "fields",
                                "version": "1.21.4"
                            },
                            {
                                "key": "formcreator",
                                "version": "2.13.7"
                            }
                        ],
                        "default_language": "fr_FR",
                        "install_mode": "TARBALL",
                        "usage": {
                            "avg_entities": "0-10",
                            "avg_computers": "10-20",
                            "avg_networkequipments": "20-30",
                            "avg_tickets": "30-40",
                            "avg_problems": "40-50",
                            "avg_changes": "50-60",
                            "avg_projects": "60-70",
                            "avg_users": "70-80",
                            "avg_groups": "80-90",
                            "ldap_enabled": false,
                            "mailcollector_enabled": true,
                            "notifications_modes": ["mailing", "custom"]
                        }
                    },
                    "system": {
                        "db": {
                            "engine": "MySQL Community Server - GPL",
                            "version": "8.0.35",
                            "size": "39.2",
                            "log_size": "",
                            "sql_mode": "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"
                        },
                        "web_server": {
                            "engine": "Apache",
                            "version": "2.4.25"
                        },
                        "php": {
                            "version": "8.2.18",
                            "modules": [
                                "Core",
                                "date",
                                "libxml",
                                "openssl",
                                "pcre",
                                "sqlite3",
                                "zlib",
                                "ctype",
                                "curl",
                                "dom",
                                "fileinfo",
                                "filter",
                                "ftp",
                                "hash",
                                "iconv",
                                "json",
                                "mbstring",
                                "SPL",
                                "PDO",
                                "pdo_sqlite",
                                "bz2",
                                "posix",
                                "Reflection",
                                "session",
                                "SimpleXML",
                                "standard",
                                "tokenizer",
                                "xml",
                                "xmlreader",
                                "xmlwriter",
                                "mysqlnd",
                                "apache2handler",
                                "Phar",
                                "exif",
                                "gd",
                                "intl",
                                "ldap",
                                "memcached",
                                "mysqli",
                                "pcntl",
                                "redis",
                                "soap",
                                "sodium",
                                "xmlrpc",
                                "zip",
                                "Zend OPcache"
                            ],
                            "setup": {
                                "max_execution_time": "30",
                                "memory_limit": "128M",
                                "post_max_size": "8M",
                                "safe_mode": false,
                                "session": "files",
                                "upload_max_filesize": "2M"
                            }
                        },
                        "os": {
                            "family": "Linux",
                            "distribution": "Ubuntu",
                            "version": "5.15.0-91-generic"
                        }
                    }
                }
            }
        JSON;

        $client = $this->getHttpClient();
        $client->request(
            method: 'POST',
            uri: '/telemetry',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $postContent,
        );
        self::assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());
        $responseContent = $client->getInternalResponse()->getContent();
        self::assertIsString($responseContent);
        self::assertJsonStringEqualsJsonString('{"message":"OK"}', $responseContent);

        // Validates that the telemetry entry is saved in DB
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        $repository = $doctrine->getManager()->getRepository(Telemetry::class);
        /** @var \App\Entity\Telemetry $telemetry */
        $telemetry = $repository->findOneBy(['glpi_uuid' => $uniqueUuid]);
        self::assertInstanceOf(Telemetry::class, $telemetry);
        self::assertEquals('10.0.10', $telemetry->getGlpiVersion());
        self::assertEquals($uniqueUuid, $telemetry->getGlpiUuid());

        $expectedPlugins = [
            'fields' => '1.21.4',
            'formcreator' => '2.13.7',
        ];
        $actualPlugins = $telemetry->getTelemetryGlpiPlugins()->toArray();
        self::assertCount(count($expectedPlugins), $actualPlugins);
        foreach ($expectedPlugins as $key => $version) {
            $telemetryGlpiPlugin = array_shift($actualPlugins);
            self::assertInstanceOf(TelemetryGlpiPlugin::class, $telemetryGlpiPlugin);
            $glpiPlugin = $telemetryGlpiPlugin->getGlpiPlugin();
            self::assertInstanceOf(GlpiPlugin::class, $glpiPlugin);
            self::assertEquals($key, $glpiPlugin->getPkey());
            self::assertEquals($version, $telemetryGlpiPlugin->getVersion());
        }

        self::assertEquals('fr_FR', $telemetry->getGlpiDefaultLanguage());
        self::assertEquals('TARBALL', $telemetry->getInstallMode());
        self::assertEquals('0-10', $telemetry->getGlpiAvgEntities());
        self::assertEquals('10-20', $telemetry->getGlpiAvgComputers());
        self::assertEquals('20-30', $telemetry->getGlpiAvgNetworkequipments());
        self::assertEquals('30-40', $telemetry->getGlpiAvgTickets());
        self::assertEquals('40-50', $telemetry->getGlpiAvgProblems());
        self::assertEquals('50-60', $telemetry->getGlpiAvgChanges());
        self::assertEquals('60-70', $telemetry->getGlpiAvgProjects());
        self::assertEquals('70-80', $telemetry->getGlpiAvgUsers());
        self::assertEquals('80-90', $telemetry->getGlpiAvgGroups());
        self::assertFalse($telemetry->isGlpiLdapEnabled());
        self::assertTrue($telemetry->isGlpiMailcollectorEnabled());
        self::assertEquals('["mailing","custom"]', $telemetry->getGlpiNotifications());

        self::assertEquals('MySQL Community Server - GPL', $telemetry->getDbEngine());
        self::assertEquals('8.0.35', $telemetry->getDbVersion());
        self::assertEquals(39, $telemetry->getDbSize());
        self::assertEquals(0, $telemetry->getDbLogSize());
        self::assertEquals(
            'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION',
            $telemetry->getDbSqlMode(),
        );

        self::assertEquals('Apache', $telemetry->getWebEngine());
        self::assertEquals('2.4.25', $telemetry->getWebVersion());

        self::assertEquals('8.2.18', $telemetry->getPhpVersion());
        self::assertEquals(
            '["Core","date","libxml","openssl","pcre","sqlite3","zlib","ctype","curl","dom","fileinfo","filter","ftp","hash","iconv","json","mbstring","SPL","PDO","pdo_sqlite","bz2","posix","Reflection","session","SimpleXML","standard","tokenizer","xml","xmlreader","xmlwriter","mysqlnd","apache2handler","Phar","exif","gd","intl","ldap","memcached","mysqli","pcntl","redis","soap","sodium","xmlrpc","zip","Zend OPcache"]',
            $telemetry->getPhpModules(),
        );

        self::assertEquals(30, $telemetry->getPhpConfigMaxExecutionTime());
        self::assertEquals('128M', $telemetry->getPhpConfigMemoryLimit());
        self::assertEquals('8M', $telemetry->getPhpConfigPostMaxSize());
        self::assertFalse($telemetry->isPhpConfigSafeMode());
        self::assertEquals('files', $telemetry->getPhpConfigSession());
        self::assertEquals('2M', $telemetry->getPhpConfigUploadMaxFilesize());

        self::assertEquals('Linux', $telemetry->getOsFamily());
        self::assertEquals('Ubuntu', $telemetry->getOsDistribution());
        self::assertEquals('5.15.0-91-generic', $telemetry->getOsVersion());
    }

    public function testSuccessfullPostFromFiles(): void
    {
        $directory_iterator = new DirectoryIterator(__DIR__ . '/../../tests/fixtures/telemetry');
        /** @var \SplFileObject $file */
        foreach ($directory_iterator as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $postContent = file_get_contents($file->getPathname());
            self::assertIsString($postContent);
            self::assertJson($postContent);

            // Ensure UUID is unique to prevent POST to be ignored du to an existing previous entry on same day
            $uniqueUuid = bin2hex(random_bytes(20));
            $postContent = preg_replace('/"uuid"\s*:\s*"[^"]+"/', sprintf('"uuid": "%s"', $uniqueUuid), $postContent);

            $client = $this->getHttpClient();
            $client->request(
                method: 'POST',
                uri: '/telemetry',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: $postContent,
            );
            self::assertEquals(Response::HTTP_OK, $client->getInternalResponse()->getStatusCode());
            $responseContent = $client->getInternalResponse()->getContent();
            self::assertIsString($responseContent);
            self::assertJsonStringEqualsJsonString('{"message":"OK"}', $responseContent);

            // Validates that the telemetry entry and the telemetry plugins entries are saved in DB
            /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
            $doctrine = static::getContainer()->get('doctrine');
            $repository = $doctrine->getManager()->getRepository(Telemetry::class);
            $telemetry = $repository->findOneBy(['glpi_uuid' => $uniqueUuid]);
            self::assertInstanceOf(Telemetry::class, $telemetry);
            self::assertEquals($uniqueUuid, $telemetry->getGlpiUuid());
            self::assertNotEmpty($telemetry->getTelemetryGlpiPlugins());
        }
    }
}
