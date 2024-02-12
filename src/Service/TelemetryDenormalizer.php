<?php

namespace App\Service;

use App\Entity\Telemetry;
use App\Entity\GlpiPlugin;
use App\Entity\TelemetryGlpiPlugin;
use App\Repository\GlpiPluginRepository;
use DateTimeImmutable;
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

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (!$this->supportsDenormalization($data, $type, $format, $context)) {
            throw new \Symfony\Component\Serializer\Exception\InvalidArgumentException();
        }
        if (!$this->validateTelemetryJson($data)) {
            return null;
        }

        $telemetry = new Telemetry();
        $telemetry->setGlpiUuid($this->propertyAccessor->getValue($data, 'data.glpi.uuid'));
        $telemetry->setGlpiVersion($this->propertyAccessor->getValue($data, 'data.glpi.version'));
        $telemetry->setGlpiDefaultLanguage($this->propertyAccessor->getValue($data, 'data.glpi.default_language'));
        $telemetry->setGlpiAvgEntities($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_entities'));
        $telemetry->setGlpiAvgComputers($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_computers'));
        $telemetry->setGlpiAvgNetworkequipments($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_networkequipments'));
        $telemetry->setGlpiAvgTickets($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_tickets'));
        $telemetry->setGlpiAvgProblems($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_problems'));
        $telemetry->setGlpiAvgChanges($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_changes'));
        $telemetry->setGlpiAvgProjects($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_projects'));
        $telemetry->setGlpiAvgUsers($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_users'));
        $telemetry->setGlpiAvgGroups($this->propertyAccessor->getValue($data, 'data.glpi.usage.avg_groups'));
        $telemetry->setGlpiLdapEnabled($this->propertyAccessor->getValue($data, 'data.glpi.usage.ldap_enabled'));
        $telemetry->setGlpiMailcollectorEnabled($this->propertyAccessor->getValue($data, 'data.glpi.usage.mailcollector_enabled'));
        $telemetry->setGlpiNotifications(json_encode($this->propertyAccessor->getValue($data, 'data.glpi.usage.notifications_modes')));
        $telemetry->setDbEngine($this->propertyAccessor->getValue($data, 'data.system.db.engine'));
        $telemetry->setDbVersion($this->propertyAccessor->getValue($data, 'data.system.db.version'));
        $telemetry->setDbSize(intval($this->propertyAccessor->getValue($data, 'data.system.db.size')));
        $telemetry->setDbLogSize(intval($this->propertyAccessor->getValue($data, 'data.system.db.log_size')));
        $telemetry->setDbSqlMode($this->propertyAccessor->getValue($data, 'data.system.db.sql_mode'));
        $telemetry->setWebEngine($this->propertyAccessor->getValue($data, 'data.system.web_server.engine'));
        $telemetry->setWebVersion($this->propertyAccessor->getValue($data, 'data.system.web_server.version'));
        $telemetry->setPhpVersion($this->propertyAccessor->getValue($data, 'data.system.php.version'));
        $telemetry->setPhpModules(json_encode($this->propertyAccessor->getValue($data, 'data.system.php.modules')));
        $telemetry->setPhpConfigMaxExecutionTime($this->propertyAccessor->getValue($data, 'data.system.php.setup.max_execution_time'));
        $telemetry->setPhpConfigMemoryLimit($this->propertyAccessor->getValue($data, 'data.system.php.setup.memory_limit'));
        $telemetry->setPhpConfigPostMaxSize($this->propertyAccessor->getValue($data, 'data.system.php.setup.post_max_size'));
        $telemetry->setPhpConfigSafeMode($this->propertyAccessor->getValue($data, 'data.system.php.setup.safe_mode'));
        $telemetry->setPhpConfigSession($this->propertyAccessor->getValue($data, 'data.system.php.setup.session'));
        $telemetry->setPhpConfigUploadMaxFilesize($this->propertyAccessor->getValue($data, 'data.system.php.setup.upload_max_filesize'));
        $telemetry->setOsFamily($this->propertyAccessor->getValue($data, 'data.system.os.family'));
        $telemetry->setOsDistribution($this->propertyAccessor->getValue($data, 'data.system.os.distribution'));
        $telemetry->setOsVersion($this->propertyAccessor->getValue($data, 'data.system.os.version'));
        $telemetry->setInstallMode($this->propertyAccessor->getValue($data, 'data.glpi.install_mode'));
        $telemetry->setCreatedAt(new DateTimeImmutable());
        $telemetry->setUpdatedAt(new DateTimeImmutable());

        $plugins = $this->propertyAccessor->getValue($data, 'data.glpi.plugins');

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

        return $telemetry;
    }

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
        $this->validator
            ->resolver()
            ->registerFile(
                'https://telemetry.glpi-project.org/schema/v1.json',
                $this->schemaDir . '/telemetry.v1.json'
            );

        $result = $this->validator->validate($contents, 'https://telemetry.glpi-project.org/schema/v1.json');

        return $result->isValid();
    }
}
