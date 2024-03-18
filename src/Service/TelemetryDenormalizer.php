<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Telemetry;
use App\Entity\GlpiPlugin;
use App\Entity\TelemetryGlpiPlugin;
use App\Repository\GlpiPluginRepository;
use DateTimeImmutable;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Validator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TelemetryDenormalizer implements DenormalizerInterface
{
    /**
     * Directory containing schema files.
     */
    private string $schemaDir;

    /**
     * JSON data/schema validator.
     */
    private Validator $validator;

    /**
     * Property accessor.
     */
    private PropertyAccessorInterface $propertyAccessor;

    /**
     * GlpiPlugin entity repository.
     */
    private GlpiPluginRepository $glpiPluginRepository;

    public function __construct(Validator $validator, string $schemaDir, GlpiPluginRepository $glpiPluginRepository)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $this->validator = $validator;
        $this->schemaDir = $schemaDir;
        $this->glpiPluginRepository = $glpiPluginRepository;
    }

    /**
    * @param mixed $data
    * @param string $type
    * @param string|null $format
    * @param array<mixed> $context
    * @return Telemetry|null
    * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
    */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (
            (!is_object($data) && !is_array($data))
            || !$this->supportsDenormalization($data, $type, $format, $context)
        ) {
            throw new \Symfony\Component\Serializer\Exception\InvalidArgumentException();
        }
        if (!$this->validateTelemetryJson($data)) {
            return null;
        }

        $telemetry = new Telemetry();
        $telemetry->setGlpiUuid(
            is_string($uuid = $this->propertyAccessor->getValue($data, 'data.glpi.uuid'))
                ? $uuid
                : null
        );
        $telemetry->setGlpiVersion(
            is_string($version = $this->propertyAccessor->getValue($data, 'data.glpi.version'))
                ? $version
                : null
        );
        $telemetry->setGlpiDefaultLanguage(
            is_string($language = $this->propertyAccessor->getValue($data, 'data.glpi.default_language'))
                ? $language
                : null
        );
        $telemetry->setGlpiAvgEntities(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_entities'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgComputers(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_computers'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgNetworkequipments(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_networkequipments'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgTickets(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_tickets'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgProblems(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_problems'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgChanges(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_changes'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgProjects(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_projects'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgUsers(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_users'))
                ? $avg
                : null
        );
        $telemetry->setGlpiAvgGroups(
            is_string($avg = $this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_groups'))
                ? $avg
                : null
        );
        $telemetry->setGlpiLdapEnabled(
            is_bool($enabled = $this->propertyAccessor->getValue($data, 'data.glpi.usage.ldap_enabled'))
                ? $enabled
                : null
        );
        $telemetry->setGlpiMailcollectorEnabled(
            is_bool($enabled = $this->propertyAccessor->getValue($data, 'data.glpi.usage.mailcollector_enabled'))
                ? $enabled
                : null
        );
        $telemetry->setGlpiNotifications(
            is_array($modes = $this->propertyAccessor->getValue($data, 'data.glpi.usage.notifications_modes'))
                ? json_encode($modes, flags: JSON_THROW_ON_ERROR)
                : null
        );
        $telemetry->setDbEngine(
            is_string($engine = $this->propertyAccessor->getValue($data, 'data.system.db.engine'))
                ? $engine
                : null
        );
        $telemetry->setDbVersion(
            is_string($version = $this->propertyAccessor->getValue($data, 'data.system.db.version'))
                ? $version
                : null
        );
        $size = $this->propertyAccessor->getValue($data, 'data.system.db.size');
        $telemetry->setDbSize(
            is_int($size) || is_float($size) || (is_string($size) && preg_match('/^\d+(\.\d+)?/', $size))
                ? (int) $size
                : null
        );
        $size = $this->propertyAccessor->getValue($data, 'data.system.db.log_size');
        $telemetry->setDbLogSize(
            is_int($size) || is_float($size) || (is_string($size) && preg_match('/^\d+(\.\d+)?/', $size))
                ? (int) $size
                : null
        );
        $telemetry->setDbSqlMode(
            is_string($mode = $this->propertyAccessor->getValue($data, 'data.system.db.sql_mode'))
                ? $mode
                : null
        );
        $telemetry->setWebEngine(
            is_string($engine = $this->propertyAccessor->getValue($data, 'data.system.web_server.engine'))
                ? $engine
                : null
        );
        $telemetry->setWebVersion(
            is_string($version = $this->propertyAccessor->getValue($data, 'data.system.web_server.version'))
                ? $version
                : null
        );
        $telemetry->setPhpVersion(
            is_string($version = $this->propertyAccessor->getValue($data, 'data.system.php.version'))
                ? $version
                : null
        );
        $telemetry->setPhpModules(
            is_array($modules = $this->propertyAccessor->getValue($data, 'data.system.php.modules'))
                ? json_encode($modules, flags: JSON_THROW_ON_ERROR)
                : null
        );
        $maxExecutionTime = $this->propertyAccessor->getValue($data, 'data.system.php.setup.max_execution_time');
        $telemetry->setPhpConfigMaxExecutionTime(
            is_int($maxExecutionTime) || (is_string($maxExecutionTime) && preg_match('/^\d+(\.\d+)?/', $maxExecutionTime))
                ? (int) $maxExecutionTime
                : null
        );
        $telemetry->setPhpConfigMemoryLimit(
            is_string($limit = $this->propertyAccessor->getValue($data, 'data.system.php.setup.memory_limit'))
                ? $limit
                : null
        );
        $telemetry->setPhpConfigPostMaxSize(
            is_string($maxSize = $this->propertyAccessor->getValue($data, 'data.system.php.setup.post_max_size'))
                ? $maxSize
                : null
        );
        $telemetry->setPhpConfigSafeMode(
            is_bool($safeMode = $this->propertyAccessor->getValue($data, 'data.system.php.setup.safe_mode'))
                ? $safeMode
                : null
        );
        $telemetry->setPhpConfigSession(
            is_string($session = $this->propertyAccessor->getValue($data, 'data.system.php.setup.session'))
                ? $session
                : null
        );
        $telemetry->setPhpConfigUploadMaxFilesize(
            is_string($maxSize = $this->propertyAccessor->getValue($data, 'data.system.php.setup.upload_max_filesize'))
                ? $maxSize
                : null
        );
        $telemetry->setOsFamily(
            is_string($os = $this->propertyAccessor->getValue($data, 'data.system.os.family'))
                ? $os
                : null
        );
        $telemetry->setOsDistribution(
            is_string($distribution = $this->propertyAccessor->getValue($data, 'data.system.os.distribution'))
                ? $distribution
                : null
        );
        $telemetry->setOsVersion(
            is_string($version = $this->propertyAccessor->getValue($data, 'data.system.os.version'))
                ? $version
                : null
        );
        $telemetry->setInstallMode(
            is_string($mode = $this->propertyAccessor->getValue($data, 'data.glpi.install_mode'))
                ? $mode
                : null
        );
        $telemetry->setCreatedAt(new DateTimeImmutable());
        $telemetry->setUpdatedAt(new DateTimeImmutable());

        $plugins = $this->propertyAccessor->getValue($data, 'data.glpi.plugins');

        if (is_array($plugins)) {
            /** @var object{key: string, version: string} $plugin */
            foreach ($plugins as $plugin) {

                $glpiPlugin = $this->glpiPluginRepository->findOneByPluginKey($plugin->key);

                if ($glpiPlugin === null) {
                    $glpiPlugin = new GlpiPlugin();
                    $glpiPlugin->setPkey($plugin->key);
                    $glpiPlugin->setCreatedAt(new DateTimeImmutable());
                    $glpiPlugin->setUpdatedAt(new DateTimeImmutable());
                }

                $telemetryGlpiPlugin = new TelemetryGlpiPlugin();
                $telemetryGlpiPlugin->setGlpiPlugin($glpiPlugin);
                $telemetryGlpiPlugin->setTelemetryEntry($telemetry);
                $telemetryGlpiPlugin->setVersion($plugin->version);
                $telemetryGlpiPlugin->setCreatedAt(new DateTimeImmutable());
                $telemetryGlpiPlugin->setUpdatedAt(new DateTimeImmutable());

                $telemetry->addTelemetryGlpiPlugin($telemetryGlpiPlugin);
            }
        }

        return $telemetry;
    }

    /**
    * @param mixed $data
    * @param string $type
    * @param string|null $format
    * @param array<mixed> $context
    * @return bool
    */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return Telemetry::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Telemetry::class => true,
        ];
    }

    /**
     * Validate a Telemetry request contents against the expected schema.
     *
     * @param mixed $contents
     * @return bool
     */
    private function validateTelemetryJson(mixed $contents): bool
    {
        $resolver = $this->validator->resolver();
        if (!($resolver instanceof SchemaResolver)) {
            throw new \RuntimeException();
        }

        $resolver->registerFile(
            'https://telemetry.glpi-project.org/schema/v1.json',
            $this->schemaDir . '/telemetry.v1.json'
        );

        $result = $this->validator->validate($contents, 'https://telemetry.glpi-project.org/schema/v1.json');

        return $result->isValid();
    }
}
