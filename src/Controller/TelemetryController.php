<?php

namespace App\Controller;

use App\Entity\GlpiPlugin;
use App\Entity\Telemetry;
use App\Entity\TelemetryGlpiPlugin;
use App\Repository\GlpiPluginRepository;
use App\Repository\TelemetryRepository;
use App\Service\TelemetryJsonValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TelemetryController extends AbstractController
{
    #[Route('/telemetry', name: 'app_telemetry_post', methods: ['POST'])]
    public function post(
        Request $request,
        TelemetryRepository $telemetryRepository,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        GlpiPluginRepository $glpiPluginRepository,
        TelemetryJsonValidator $jsonValidator
    ): Response {
        $logger->debug('POST request received');
        $logger->debug('POST request content: ' . $request->getContent());
        $validation = false;

        //check if the content type is json
        if ($request->headers->get('Content-Type') != 'application/json') {
            $logger->debug('POST request content type is not json');
            return new Response('status: Content-Type must be application/json', Response::HTTP_BAD_REQUEST);
        } else {
            $logger->debug('POST request content type is json');
        }

        //Decode request content
        $data = json_decode($request->getContent());
        $logger->debug('POST request content decoded', ['data' => $data]);
        $logger->debug('POST request decoded');

        //Validate JSON
        $logger->debug('POST request middleware created');

        if ($jsonValidator->validateJson($request->getContent())) {
            $logger->debug('POST request middleware validated');
            $validation = true;
        } else {
            $logger->debug('POST request middleware not validated');
            return new Response('status: JSON is not valid', Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        $logger->debug('Save data to database');

        try {
            $this->registerData($data, $entityManager, $glpiPluginRepository);
        } catch (\Exception $e) {
            $logger->debug('Error saving data to database : ' . $e->getMessage());
            return new Response('status: Error saving data to database', Response::HTTP_BAD_REQUEST);
        }

        return new Response('status: OK', Response::HTTP_OK);
    }

    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(TelemetryRepository $telemetryRepository): Response
    {
        return $this->render('telemetry/index.html.twig', [
            'controller_name' => 'controller-name',
        ]);
    }

    public function registerData($data, $entityManager, $glpiPluginRepository): bool
    {
        $entityManager->beginTransaction();

        try {
            $telemetry = new Telemetry();

            $telemetry->setGlpiUuid($data['data']['glpi']['uuid']);
            $telemetry->setGlpiVersion($data['data']['glpi']['version']);
            $telemetry->setGlpiDefaultLanguage($data['data']['glpi']['default_language']);
            $telemetry->setGlpiAvgEntities($data['data']['glpi']['usage']['avg_entities']);
            $telemetry->setGlpiAvgComputers($data['data']['glpi']['usage']['avg_computers']);
            $telemetry->setGlpiAvgNetworkequipments($data['data']['glpi']['usage']['avg_networkequipments']);
            $telemetry->setGlpiAvgTickets($data['data']['glpi']['usage']['avg_tickets']);
            $telemetry->setGlpiAvgProblems($data['data']['glpi']['usage']['avg_problems']);
            $telemetry->setGlpiAvgChanges($data['data']['glpi']['usage']['avg_changes']);
            $telemetry->setGlpiAvgProjects($data['data']['glpi']['usage']['avg_projects']);
            $telemetry->setGlpiAvgUsers($data['data']['glpi']['usage']['avg_users']);
            $telemetry->setGlpiAvgGroups($data['data']['glpi']['usage']['avg_groups']);
            $telemetry->setGlpiLdapEnabled($data['data']['glpi']['usage']['ldap_enabled']);
            $telemetry->setGlpiMailcollectorEnabled($data['data']['glpi']['usage']['mailcollector_enabled']);
            $telemetry->setGlpiNotifications(json_encode($data['data']['glpi']['usage']['notifications']));
            $telemetry->setDbEngine($data['data']['system']['db']['engine']);
            $telemetry->setDbVersion($data['data']['system']['db']['version']);
            $telemetry->setDbSize(intval($data['data']['system']['db']['size']));
            $telemetry->setDbLogSize(intval($data['data']['system']['db']['log_size']));
            $telemetry->setDbSqlMode($data['data']['system']['db']['sql_mode']);
            $telemetry->setWebEngine($data['data']['system']['web_server']['engine']);
            $telemetry->setWebVersion($data['data']['system']['web_server']['version']);
            $telemetry->setPhpVersion($data['data']['system']['php']['version']);
            $telemetry->setPhpModules(json_encode($data['data']['system']['php']['modules']));
            $telemetry->setPhpConfigMaxExecutionTime($data['data']['system']['php']['setup']['max_execution_time']);
            $telemetry->setPhpConfigMemoryLimit($data['data']['system']['php']['setup']['memory_limit']);
            $telemetry->setPhpConfigPostMaxSize($data['data']['system']['php']['setup']['post_max_size']);
            $telemetry->setPhpConfigSafeMode($data['data']['system']['php']['setup']['safe_mode']);
            $telemetry->setPhpConfigSession($data['data']['system']['php']['setup']['session']);
            $telemetry->setPhpConfigUploadMaxFilesize($data['data']['system']['php']['setup']['upload_max_filesize']);
            $telemetry->setOsFamily($data['data']['system']['os']['family']);
            $telemetry->setOsVersion($data['data']['system']['os']['version']);
            $telemetry->setInstallMode($data['data']['glpi']['install_mode']);
            $telemetry->setCreatedAt(new \DateTimeImmutable());
            $telemetry->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($telemetry);
            $entityManager->flush();

            $plugins = $data['data']['glpi']['plugins'];
            $glpiPlugins = [];

            foreach ($plugins as $pluginData) {
                $pluginKey  = $pluginData['key'];

                $glpiPlugin = $glpiPluginRepository->findOneBy(['pkey' => $pluginKey]);

                if (!$glpiPlugin) {
                    $glpiPlugin = new GlpiPlugin();
                    $glpiPlugin->setPkey($pluginKey);
                    $entityManager->persist($glpiPlugin);
                    $glpiPlugins[] = $glpiPlugin;
                }

                $telemetryGlpiPlugin = new TelemetryGlpiPlugin();
                $telemetryGlpiPlugin->setTelemetryEntry($telemetry);
                $telemetryGlpiPlugin->setGlpiPlugin($glpiPlugin);
                $telemetryGlpiPlugin->setVersion($pluginData['version']);
                $telemetryGlpiPlugin->setCreatedAt(new \DateTimeImmutable());
                $telemetryGlpiPlugin->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->persist($telemetryGlpiPlugin);

            }

            $entityManager->flush();

            $entityManager->commit();

        } catch (\Exception $e) {
            $entityManager->rollback();
            throw $e;
        }
        return true;
    }
}
