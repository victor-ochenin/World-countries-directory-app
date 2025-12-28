<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(path:'api', name:'app_api_root')]
final class StatusController extends AbstractController
{
    #[Route('/', name: 'app_status', methods:['GET'])]
    public function index(Request $request): JsonResponse
    {
        return $this->json(data: [
            'message' => 'server is running',
            'host' => $request->getHttpHost(),
            'protocol' => $request->getScheme(),
        ]);
    }

    #[Route(path:'/ping', name: 'app_api_root_ping', methods:['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json(data: [
            'message' => 'pong',
        ]);
    }
}
