<?php declare(strict_types=1);

namespace SanalposproPayment\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class IapiController extends StorefrontController
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    // This is the Internal API bridge that PayThor CDN app communicates with after OTP/login.
    #[Route(path: '/sanalpospro/iapi/index', name: 'frontend.sanalpospro.iapi', defaults: ['csrf_protected' => false, 'XmlHttpRequest' => true], methods: ['POST', 'OPTIONS'])]
    public function index(Request $request, Context $context): JsonResponse
    {
        // Handle CORS Preflight request from PayThor React App
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Origin, etc-program-id, etc-app-id');
            return $response;
        }

        $data = json_decode($request->getContent(), true);

        return $this->actionCheckApiKeys($data);
    }

    private function actionCheckApiKeys(?array $data): JsonResponse
    {
        $corsHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
        ];

        try {
            $publicKey = $this->systemConfigService->getString('SanalPosPro.config.publicApiKey');
            $secretKey = $this->systemConfigService->getString('SanalPosPro.config.secretApiKey');

            if (!$publicKey || !$secretKey) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Api keys not found'
                ], 400, $corsHeaders);
            }

            $accessToken = $data['iapi_accessToken'] ?? null;

            $client = new Client([
                'base_uri' => 'https://api.paythor.com',
                'timeout' => 15.0,
            ]);

            $response = $client->post('/check/accesstoken', [
                'json' => [
                    'accesstoken' => $accessToken
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            return new JsonResponse($responseData, $response->getStatusCode(), $corsHeaders);

        } catch (GuzzleException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'API request failed: ' . $e->getMessage()
            ], 500, $corsHeaders);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500, $corsHeaders);
        }
    }
}
