<?php

namespace App\Controller;

use App\Entity\PurchaseOrder;
use App\Repository\PurchaseOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/purchase-order')]
#[IsGranted('ROLE_ADMIN')]
final class PurchaseOrderController extends AbstractController
{
    #[Route('/{id}/pdf', name: 'app_purchase_order_pdf', methods: ['GET'])]
    public function pdf(PurchaseOrder $purchaseOrder): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('purchase_order/pdf.html.twig', [
            'po' => $purchaseOrder,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="PO-' . $purchaseOrder->getOrderNumber() . '.pdf"',
            ]
        );
    }
    #[Route('/', name: 'app_purchase_order_index', methods: ['GET'])]
    public function index(PurchaseOrderRepository $purchaseOrderRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $purchaseOrderRepository->createQueryBuilder('po')
            ->orderBy('po.createdAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('purchase_order/index.html.twig', [
            'purchase_orders' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_purchase_order_show', methods: ['GET'])]
    public function show(PurchaseOrder $purchaseOrder): Response
    {
        return $this->render('purchase_order/show.html.twig', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }
}
