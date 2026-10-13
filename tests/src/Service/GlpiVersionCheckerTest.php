<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\GlpiVersionChecker;
use App\Tests\KernelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionMethod;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GlpiVersionCheckerTest extends KernelTestCase
{
    /**
     * @return list<array{version: string, normalized: string|null, valid: bool}>
     */
    public static function versionProvider(): iterable
    {
        // Known unexpected cases
        yield [
            'version'    => 'V1',
            'normalized' => null,
            'valid'      => false,
        ];

        yield [
            'version'    => '1.6.0',
            'normalized' => '1.6.0',
            'valid'      => false,
        ];

        yield [
            'version'    => '2.0.3',
            'normalized' => '2.0.3',
            'valid'      => false,
        ];

        // Expected cases
        yield [
            'version'    => '9.2',
            'normalized' => '9.2.0',
            'valid'      => true,
        ];

        yield [
            'version'    => '9.4.1.1',
            'normalized' => '9.4.1',
            'valid'      => true,
        ];

        yield [
            'version'    => '9.5.3snap',
            'normalized' => '9.5.3',
            'valid'      => true,
        ];

        yield [
            'version'    => '10.0.6',
            'normalized' => '10.0.6',
            'valid'      => true,
        ];

        yield [
            'version'    => '11.0.3.1-deb',
            'normalized' => '11.0.3',
            'valid'      => true,
        ];

        yield [
            'version'    => '12.0.0-rc3',
            'normalized' => '12.0.0',
            'valid'      => false, // pre-releases are considered invalid
        ];
    }

    #[DataProvider('versionProvider')]
    public function testIsGlpiVersionValid(string $version, ?string $normalized, bool $valid): void
    {
        $page1ResponseStub = self::createStub(ResponseInterface::class);
        $page1ResponseStub->method('toArray')->willReturn(
            [
                [
                    'name'              => '12.0.0',
                    'tag_name'          => '12.0.0',
                    'target_commitish'  => '12.0/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '11.0.11',
                    'tag_name'          => '11.0.11',
                    'target_commitish'  => '11.0/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '12.0.0-rc3',
                    'tag_name'          => '12.0.0-rc3',
                    'target_commitish'  => '12.0/bugfixes',
                    'prerelease'        => true,
                ],
                [
                    'name'              => '11.0.10',
                    'tag_name'          => '11.0.10',
                    'target_commitish'  => '11.0/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '10.0.28',
                    'tag_name'          => '10.0.28',
                    'target_commitish'  => '10.0/bugfixes',
                    'prerelease'        => false,
                ],
            ],
        );
        $page2ResponseStub = self::createStub(ResponseInterface::class);
        $page2ResponseStub->method('toArray')->willReturn(
            [
                [
                    'name'              => '11.0.3',
                    'tag_name'          => '11.0.3',
                    'target_commitish'  => '11.0/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '10.0.6',
                    'tag_name'          => '10.0.6',
                    'target_commitish'  => '10.0/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '9.5.3',
                    'tag_name'          => '9.5.3',
                    'target_commitish'  => '9.5/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '9.4.1 (9.4.1.1)',
                    'tag_name'          => '9.4.1.1',
                    'target_commitish'  => '9.4/bugfixes',
                    'prerelease'        => false,
                ],
                [
                    'name'              => '9.2.0',
                    'tag_name'          => '9.2',
                    'target_commitish'  => '9.2/bugfixes',
                    'prerelease'        => false,
                ],
            ],
        );
        $page3ResponseStub = self::createStub(ResponseInterface::class);
        $page3ResponseStub->method('toArray')->willReturn([]); // simulate end of results

        $httpClientStub = self::createStub(HttpClientInterface::class);
        $httpClientStub->method('request')->willReturn(
            $page1ResponseStub,
            $page2ResponseStub,
            $page3ResponseStub,
        );

        $checker = new GlpiVersionChecker(
            self::createStub(CacheItemPoolInterface::class),
            $httpClientStub,
            'github_pat_XXX',
        );

        $result = $checker->isGlpiVersionValid($version);

        self::assertEquals($valid, $result);
    }

    #[DataProvider('versionProvider')]
    public function testGetNormalizedVersion(string $version, ?string $normalized, bool $valid): void
    {
        $checker = new GlpiVersionChecker(
            self::createStub(CacheItemPoolInterface::class),
            self::createStub(HttpClientInterface::class),
            'github_pat_XXX',
        );

        $result = (new ReflectionMethod($checker, 'getNormalizedVersion'))->invoke($checker, $version);

        self::assertEquals($normalized, $result);
    }
}
