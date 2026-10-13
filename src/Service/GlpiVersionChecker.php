<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GlpiVersionChecker
{
    private const GLPI_RELEASES_CACHE_KEY = 'glpi_releases';
    private const INVALID_VERSIONS_CACHE_KEY = 'invalid_version';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
        private string $githubApiToken,
    ) {}

    public function isGlpiVersionValid(string $version): bool
    {
        if (\preg_match('/-(dev|alpha\d*|beta\d*|rc\d*)/', $version) === 1) {
            // Ignore unstable versions.
            return false;
        }

        $version = $this->getNormalizedVersion($version);

        if ($version === null) {
            // Invalid version.
            return false;
        }

        $glpiReleasesCacheItem = $this->cache->getItem(self::GLPI_RELEASES_CACHE_KEY);

        $cachedGlpiReleases = $glpiReleasesCacheItem->isHit() ? $glpiReleasesCacheItem->get() : [];
        if (\is_array($cachedGlpiReleases) === false) {
            throw new \LogicException();
        }

        if (\in_array($version, $cachedGlpiReleases, true)) {
            // Release has been found in cached releases.
            return true;
        }

        // It may be a new release, check against refreshed release list.
        $releases = $this->getReleases();

        if (\in_array($version, $releases, true)) {
            $glpiReleasesCacheItem->set($releases);
            $this->cache->save($glpiReleasesCacheItem);

            return true;
        }

        // Release is not valid, cache it in refused releases to not fetch the GitHub API each time.
        $invalidVersionsCacheItem = $this->cache->getItem(self::INVALID_VERSIONS_CACHE_KEY);

        $cachedInvalidVersion = $invalidVersionsCacheItem->isHit() ? $invalidVersionsCacheItem->get() : [];
        if (\is_array($cachedInvalidVersion) === false) {
            throw new \LogicException();
        }

        $invalidVersionsCacheItem->set(\array_merge($cachedInvalidVersion, [$version]));
        $this->cache->save($invalidVersionsCacheItem);

        return false;
    }

    /**
     * @phpstan-return list<string>
     */
    private function getReleases(): array
    {
        $releases = [];

        $page = 1;
        do {
            $response = $this->httpClient->request(
                'GET',
                'https://api.github.com/repos/glpi-project/glpi/releases',
                [
                    'headers' => [
                        'Authorization' => \sprintf('Bearer %s', $this->githubApiToken),
                    ],
                    'query' => [
                        'page'      => $page,
                        'per_page'  => 100,
                    ],
                ],
            );
            $page++;

            $data = $response->toArray();

            /** @phpstan-var list<array{prerelease: bool, tag_name: string}> $data */
            foreach ($data as $releaseData) {
                if ($releaseData['prerelease']) {
                    // Ignore pre-releases.
                    continue;
                }

                $version = $this->getNormalizedVersion($releaseData['tag_name']);

                if ($version === null) {
                    // GLPI releases tags are not supposed to be invalid.
                    throw new \LogicException();
                }

                $releases[] = $version;
            }
        } while ($data !== []);

        return $releases;
    }

    private function getNormalizedVersion(string $version): ?string
    {
        $version_pattern = implode(
            '',
            [
                '/^',
                '(?<major>\d+)', // Major release numero, always present
                '\.(?<minor>\d+)', // Minor release numero, always present
                '(\.(?<bugfix>\d+))?', // Bugfix numero, not always present (e.g. GLPI 9.2)
                '(?<suffix>.*)?', // Suffix, can be a tag redo (e.g. in `9.4.1.1`), a suffix added by a packager (e.g. `11.0.3.1-deb`)
                '$/',
            ],
        );

        $version_matches = [];
        if (preg_match($version_pattern, $version, $version_matches) !== 1) {
            // Cannot normalize version, consider it invalid.
            return null;
        }

        $major = $version_matches['major'];
        $minor = $version_matches['minor'];
        $bugfix = \array_key_exists('bugfix', $version_matches) && $version_matches['bugfix'] !== ''
            ? $version_matches['bugfix']
            : 0;

        return \implode('.', [$major, $minor, $bugfix]);
    }
}
