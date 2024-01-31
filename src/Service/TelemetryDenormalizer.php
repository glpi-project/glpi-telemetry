<?php

namespace App\Service;

use App\Entity\Telemetry;
use App\Service\TelemetryJsonValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TelemetryDenormalizer implements DenormalizerInterface
{
    private PropertyAccessor $propertyAccessor;
    private TelemetryJsonValidator $TelemetryJsonValidator;

    public function __construct(TelemetryJsonValidator $telemetryJsonValidator)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $this->TelemetryJsonValidator = $telemetryJsonValidator;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (!$this->supportsDenormalization($data, $type, $format, $context)) {
            throw new \Symfony\Component\Serializer\Exception\InvalidArgumentException();
        }

        $telemetry = new Telemetry();
        $telemetry->setGlpiUuid($this->propertyAccessor->getValue($data, 'data.glpi.uuid'));
        /*
        $telemetry->setGlpiVersion($data['glpi']['version']);
        $telemetry->setGlpiDefaultLanguage($data['glpi']['default_language']);
        $telemetry->setGlpiAvgEntities($data['glpi']['usage']['avg_entities']);
        $telemetry->setGlpiAvgComputers($data['glpi']['usage']['avg_computers']);
        $telemetry->setGlpiAvgNetworkequipments($data['glpi']['usage']['avg_networkequipments']);
        $telemetry->setGlpiAvgTickets($data['glpi']['usage']['avg_tickets']);
        $telemetry->setGlpiAvgProblems($data['glpi']['usage']['avg_problems']);
        $telemetry->setGlpiAvgChanges($data['glpi']['usage']['avg_changes']);
        $telemetry->setGlpiAvgProjects($data['glpi']['usage']['avg_projects']);
        $telemetry->setGlpiAvgUsers($data['glpi']['usage']['avg_users']);
        $telemetry->setGlpiAvgGroups($data['glpi']['usage']['avg_groups']);
        $telemetry->setGlpiLdapEnabled($data['glpi']['usage']['ldap_enabled']);
        $telemetry->setGlpiMailcollectorEnabled($data['glpi']['usage']['mailcollector_enabled']);
        $telemetry->setGlpiNotifications(json_encode($data['glpi']['usage']['notifications']));
        $telemetry->setDbEngine($data['system']['db']['engine']);
        $telemetry->setDbVersion($data['system']['db']['version']);
        $telemetry->setDbSize(intval($data['system']['db']['size']));
        $telemetry->setDbLogSize(intval($data['system']['db']['log_size']));
        $telemetry->setDbSqlMode($data['system']['db']['sql_mode']);
        $telemetry->setWebEngine($data['system']['web_server']['engine']);
        $telemetry->setWebVersion($data['system']['web_server']['version']);
        $telemetry->setPhpVersion($data['system']['php']['version']);
        $telemetry->setPhpModules(json_encode($data['system']['php']['modules']));
        $telemetry->setPhpConfigMaxExecutionTime($data['system']['php']['setup']['max_execution_time']);
        $telemetry->setPhpConfigMemoryLimit($data['system']['php']['setup']['memory_limit']);
        $telemetry->setPhpConfigPostMaxSize($data['system']['php']['setup']['post_max_size']);
        $telemetry->setPhpConfigSafeMode($data['system']['php']['setup']['safe_mode']);
        $telemetry->setPhpConfigSession($data['system']['php']['setup']['session']);
        $telemetry->setPhpConfigUploadMaxFilesize($data['system']['php']['setup']['upload_max_filesize']);
        $telemetry->setOsFamily($data['system']['os']['family']);
        $telemetry->setOsVersion($data['system']['os']['version']);
        $telemetry->setInstallMode($data['glpi']['install_mode']);
        $telemetry->setCreatedAt(new \DateTimeImmutable());
        $telemetry->setUpdatedAt(new \DateTimeImmutable());
        */

        return $telemetry;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return Telemetry::class === $type && $this->TelemetryJsonValidator->validateJson($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Telemetry::class => true,
        ];
    }
}
