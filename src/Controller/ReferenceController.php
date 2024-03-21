<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\GlpiReference;
use App\Entity\Reference;
use App\Form\ReferenceFormType;
use App\Repository\ReferenceRepository;
use App\Service\CaptchaValidator;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class ReferenceController extends AbstractController
{
    #[Route('/reference', name: 'app_reference')]
    public function index(ReferenceRepository $referenceRepository, Request $request): Response
    {
        if ($request->query->get('showmodal') !== null) {
            // `showmodal` is the parameter passed by GLPI in the `Register your GLPI instance` link
            $uuid = $request->query->get('uuid');
            return $this->redirectToRoute('app_reference_register', ['uuid' => $uuid]);
        }

        $references = $referenceRepository->findBy([], ['created_at' => 'DESC']);

        return $this->render(
            'reference/index.html.twig',
            [
                'references' => $references,
            ]
        );
    }

    #[Route('/reference/register', name: 'app_reference_register')]
    public function register(
        Request $request,
        EntityManagerInterface $manager,
        CaptchaValidator $captchaValidator,
        string $captchaSiteKey
    ): Response {
        $form = $this->createForm(ReferenceFormType::class);
        if ($request->query->has('uuid')) {
            $form->setData(['uuid' => $request->query->get('uuid')]);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = false;

            $captcha_token = $request->request->get('captcha_token');
            if ($captcha_token !== null && $captchaValidator->validateToken((string) $captcha_token)) {
                try {
                    /**
                     * @var array{
                     *          uuid: ?string,
                     *          name: ?string,
                     *          url: ?string,
                     *          country: ?string,
                     *          phone: ?string,
                     *          email: ?string,
                     *          referent: ?string,
                     *          comment: ?string,
                     *          nb_assets: ?int,
                     *          nb_helpdesk: ?int
                     *      } $data
                     */
                    $data = $form->getData();

                    $reference = new Reference();
                    $reference->setUuid($data['uuid']);
                    $reference->setName($data['name']);
                    $reference->setUrl($data['url']);
                    $reference->setCountry($data['country'] !== null ? strtolower($data['country']) : null);
                    $reference->setPhone($data['phone']);
                    $reference->setEmail($data['email']);
                    $reference->setReferent($data['referent']);
                    $reference->setComment($data['comment']);
                    $reference->setCreatedAt(new \DateTimeImmutable());

                    $glpiReference = new GlpiReference();
                    $glpiReference->setNumAssets($data['nb_assets']);
                    $glpiReference->setNumHelpdesk($data['nb_helpdesk']);
                    $glpiReference->setReference($reference);
                    $glpiReference->setCreatedAt(new \DateTimeImmutable());
                    $reference->setGlpiReference($glpiReference);

                    $manager->persist($reference);
                    $manager->flush();

                    $success = true;
                } catch (\Throwable $e) {
                    $success = false;
                }
            }

            if ($success) {
                $this->addFlash('success', 'Your reference has been added successfully');
                return $this->redirectToRoute('app_reference');
            } else {
                $this->addFlash('danger', 'An error occurred while adding your reference');
            }

        }
        return $this->render('reference/register.html.twig', [
            'form'  => $form,
            'captchaSiteKey' => $captchaSiteKey,
        ]);
    }

    #[Route('/reference/map/data')]
    public function mapData(ReferenceRepository $referenceRepository): JsonResponse
    {
        $data = $referenceRepository->getReferencesCountByCountries();

        $result = [];
        foreach ($this->getCountries() as $country) {
            $result[] = [
                'name'  => $country['name'],
                'value' => $data[$country['cca2']] ?? 0,
            ];
        }
        return $this->json($result);
    }

    #[Route('/reference/map/countries')]
    public function mapCountries(CacheInterface $cache): JsonResponse
    {
        $compiledGeoJson = $cache->get("countries.geo.json", function () {
            $countries = $this->getCountries();

            $compiledGeoJson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach ($countries as $country) {
                $features = $this->getCountryGeometryFeatures($country['cca3'], $country['name']);

                foreach (array_keys($features) as $key) {
                    $features[$key]->properties->name = $country['name'];
                }

                array_push($compiledGeoJson['features'], ...$features);
            }

            return json_encode($compiledGeoJson);
        });

        return new JsonResponse($compiledGeoJson, json: true);
    }

    /**
     * Get countries base properties.
     *
     * @return array<array{cca2: string, cca3: string, name: string}>
     */
    private function getCountries(): array
    {
        $countriesFileData = file_get_contents(__DIR__ . '/../../vendor/mledoze/countries/dist/countries.json');
        if ($countriesFileData === false) {
            throw new \RuntimeException();
        }

        $countriesData = json_decode($countriesFileData, flags: JSON_THROW_ON_ERROR);
        if (!is_array($countriesData)) {
            throw new \RuntimeException();
        }

        $result = [];
        foreach ($countriesData as $countryData) {
            if (
                !($countryData instanceof stdClass)
                || !isset($countryData->cca2, $countryData->cca3, $countryData->name)
                || !($countryData->name instanceof stdClass)
                || !isset($countryData->name->common)
                || !is_string($countryData->cca2)
                || !is_string($countryData->cca3)
                || !is_string($countryData->name->common)
            ) {
                // Ignore countries with missing or invalid data
                continue;
            }

            if (strtolower($countryData->cca3) === 'ata') {
                // Ignore antartica to have a better map display
                continue;
            }

            $result[] = [
                'cca2' => strtolower($countryData->cca2),
                'cca3' => strtolower($countryData->cca3),
                'name' => $countryData->name->common,
            ];
        }

        return $result;
    }

    /**
     * Get geometry features for the given country.
     *
     * @return array<int, object{type: string, properties: \stdClass, geometry: \stdClass}>
     */
    private function getCountryGeometryFeatures(string $cca3, string $countryName): array
    {
        $geoJsonPath = __DIR__ . sprintf('/../../vendor/mledoze/countries/data/%s.geo.json', $cca3);

        if (!file_exists($geoJsonPath)) {
            return [];
        }

        $geoJsonFileData = file_get_contents($geoJsonPath);
        if ($geoJsonFileData === false) {
            throw new \RuntimeException();
        }

        $geoJsonData = json_decode($geoJsonFileData, flags: JSON_THROW_ON_ERROR);
        if (!is_object($geoJsonData)) {
            throw new \RuntimeException();
        }

        if (
            !property_exists($geoJsonData, 'features')
            || !is_array($geoJsonData->features)
        ) {
            // Some countries files does not contains enough data (e.g. `unk.geo.json`).
            return [];
        }

        $features = [];
        foreach ($geoJsonData->features as $feature) {
            if (
                !($feature instanceof stdClass)
                || !property_exists($feature, 'type')
                || !is_string($feature->type)
                || !property_exists($feature, 'properties')
                || !($feature->properties instanceof stdClass)
                || !property_exists($feature, 'geometry')
                || !($feature->geometry instanceof stdClass)
            ) {
                // Keep only valid geometry features.
                continue;
            }
            /** @var object{type: string, properties: \stdClass, geometry: \stdClass} $feature */
            $features[] = $feature;
        }

        return $features;
    }
}
