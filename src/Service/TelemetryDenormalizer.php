<?php

namespace App\Service;

use App\Entity\Telemetry;
use App\Entity\GlpiPlugin;
use App\Entity\TelemetryGlpiPlugin;
use App\Repository\GlpiPluginRepository;
use DateTimeImmutable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TelemetryDenormalizer implements DenormalizerInterface
{
    private PropertyAccessorInterface $_propertyAccessor;
    private TelemetryJsonValidator $_telemetryJsonValidator;
    private GlpiPluginRepository $_pluginRepository;

    public function __construct(TelemetryJsonValidator $_telemetryJsonValidator, GlpiPluginRepository $_pluginRepository)
    {
        $this->_propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $this->_telemetryJsonValidator = $_telemetryJsonValidator;
        $this->_pluginRepository = $_pluginRepository;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (!$this->supportsDenormalization($data, $type, $format, $context)) {
            throw new \Symfony\Component\Serializer\Exception\InvalidArgumentException();
        }

        $telemetry = new Telemetry();
        $telemetry->setGlpiUuid($this->_propertyAccessor->getValue($data, 'data.glpi.uuid'));
        $telemetry->setGlpiVersion($this->_propertyAccessor->getValue($data, 'data.glpi.version'));
        $telemetry->setGlpiDefaultLanguage($this->_propertyAccessor->getValue($data, 'data.glpi.default_language'));
        $telemetry->setGlpiAvgEntities($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_entities'));
        $telemetry->setGlpiAvgComputers($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_computers'));
        $telemetry->setGlpiAvgNetworkequipments($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_networkequipments'));
        $telemetry->setGlpiAvgTickets($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_tickets'));
        $telemetry->setGlpiAvgProblems($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_problems'));
        $telemetry->setGlpiAvgChanges($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_changes'));
        $telemetry->setGlpiAvgProjects($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_projects'));
        $telemetry->setGlpiAvgUsers($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_users'));
        $telemetry->setGlpiAvgGroups($this->_propertyAccessor->getValue($data, 'data.glpi.usage.avg_groups'));
        $telemetry->setGlpiLdapEnabled($this->_propertyAccessor->getValue($data, 'data.glpi.usage.ldap_enabled'));
        $telemetry->setGlpiMailcollectorEnabled($this->_propertyAccessor->getValue($data, 'data.glpi.usage.mailcollector_enabled'));
        $telemetry->setGlpiNotifications(json_encode($this->_propertyAccessor->getValue($data, 'data.glpi.usage.notifications')));
        $telemetry->setDbEngine($this->_propertyAccessor->getValue($data, 'data.system.db.engine'));
        $telemetry->setDbVersion($this->_propertyAccessor->getValue($data, 'data.system.db.version'));
        $telemetry->setDbSize(intval($this->_propertyAccessor->getValue($data, 'data.system.db.size')));
        $telemetry->setDbLogSize(intval($this->_propertyAccessor->getValue($data, 'data.system.db.log_size')));
        $telemetry->setDbSqlMode($this->_propertyAccessor->getValue($data, 'data.system.db.sql_mode'));
        $telemetry->setWebEngine($this->_propertyAccessor->getValue($data, 'data.system.web_server.engine'));
        $telemetry->setWebVersion($this->_propertyAccessor->getValue($data, 'data.system.web_server.version'));
        $telemetry->setPhpVersion($this->_propertyAccessor->getValue($data, 'data.system.php.version'));
        $telemetry->setPhpModules(json_encode($this->_propertyAccessor->getValue($data, 'data.system.php.modules')));
        $telemetry->setPhpConfigMaxExecutionTime($this->_propertyAccessor->getValue($data, 'data.system.php.setup.max_execution_time'));
        $telemetry->setPhpConfigMemoryLimit($this->_propertyAccessor->getValue($data, 'data.system.php.setup.memory_limit'));
        $telemetry->setPhpConfigPostMaxSize($this->_propertyAccessor->getValue($data, 'data.system.php.setup.post_max_size'));
        $telemetry->setPhpConfigSafeMode($this->_propertyAccessor->getValue($data, 'data.system.php.setup.safe_mode'));
        $telemetry->setPhpConfigSession($this->_propertyAccessor->getValue($data, 'data.system.php.setup.session'));
        $telemetry->setPhpConfigUploadMaxFilesize($this->_propertyAccessor->getValue($data, 'data.system.php.setup.upload_max_filesize'));
        $telemetry->setOsFamily($this->_propertyAccessor->getValue($data, 'data.system.os.family'));
        $telemetry->setOsVersion($this->_propertyAccessor->getValue($data, 'data.system.os.version'));
        $telemetry->setInstallMode($this->_propertyAccessor->getValue($data, 'data.glpi.install_mode'));
        $telemetry->setCreatedAt(new DateTimeImmutable());
        $telemetry->setUpdatedAt(new DateTimeImmutable());

        $plugins = $this->_propertyAccessor->getValue($data, 'data.glpi.plugins');

        foreach ($plugins as $plugin) {

            $telemetryGlpiPlugin = new TelemetryGlpiPlugin();

            $glpiPlugin = $this->_pluginRepository->findOneBy(['pkey' => $plugin->key]);

            if ($glpiPlugin === null) {
                $glpiPlugin = new GlpiPlugin();
                $glpiPlugin->setPkey($plugin->key);
                $glpiPlugin->setCreatedAt(new DateTimeImmutable());
                $glpiPlugin->setUpdatedAt(new DateTimeImmutable());

                $this->_pluginRepository->save($glpiPlugin, true);
            }

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
        return Telemetry::class === $type && $this->_telemetryJsonValidator->validateJson($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Telemetry::class => true,
        ];
    }
}
