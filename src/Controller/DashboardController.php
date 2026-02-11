<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\PurchaseOrderRepository;
use App\Repository\PurchaseRequestRepository;
use App\Repository\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        PurchaseRequestRepository $prRepo,
        ProductRepository $productRepo,
        PurchaseOrderRepository $poRepo,
        SupplierRepository $supplierRepo
    ): Response {
        $user = $this->getUser();
        $statusSearch = 'pending';
        $stats = [
            'totalRequests' => $this->isGranted('ROLE_ADMIN')
                ? $prRepo->count([])
                : $prRepo->count(['requester' => $user]),

            'pendingRequests' => $this->isGranted('ROLE_ADMIN')
                ? $prRepo->count(['status' => $statusSearch])
                : $prRepo->count(['requester' => $user, 'status' => $statusSearch]),

            'totalProducts' => $productRepo->count([]),
        ];

        $lastRequests = $prRepo->findBy(
            $this->isGranted('ROLE_ADMIN') ? [] : ['requester' => $user],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'lastRequests' => $lastRequests,
            'prCount' => $prRepo->count([]),
            'poCount' => $poRepo->count([]),
            'productCount' => $productRepo->count([]),
            'supplierCount' => $supplierRepo->count([]),
        ]);
    }
}
