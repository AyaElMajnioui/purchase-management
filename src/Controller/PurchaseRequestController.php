<?php

namespace App\Controller;

use App\Entity\PurchaseRequest;
use App\Entity\PurchaseOrder;
use App\Form\PurchaseRequestType;
use App\Repository\PurchaseRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Registry;
use App\Entity\Comment;
use App\Form\CommentType;

#[Route('/purchase/request')]
final class PurchaseRequestController extends AbstractController
{
    #[Route('/admin/purchase-request', name: 'app_purchase_request_all')]
    public function all(PurchaseRequestRepository $purchaseRequestRepository): Response
    {
        return $this->render('purchase_request/all.html.twig', [
            'purchase_requests' => $purchaseRequestRepository->findAll(),
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route(name: 'app_purchase_request_index', methods: ['GET'])]
    public function index(PurchaseRequestRepository $purchaseRequestRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $query = $purchaseRequestRepository->createQueryBuilder('pr')
            ->orderBy('pr.reference', 'ASC');
        if ($this->isGranted('ROLE_ADMIN')) {
            $purchaseRequests = $purchaseRequestRepository->findAll();
        } else {
            $purchaseRequests = $purchaseRequestRepository->findBy(['requester' => $user], ['createdAt' => 'DESC']);
        }
        $purchaseRequests = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('purchase_request/index.html.twig', [
            'purchase_requests' => $purchaseRequests,
            'isAdminView' => $this->isGranted('ROLE_ADMIN'),
        ]);
    }

    #[Route('/new', name: 'app_purchase_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $purchaseRequest = new PurchaseRequest();

        // On définit les valeurs par défaut
        $purchaseRequest->setRequester($this->getUser());
        $purchaseRequest->setStatus(PurchaseRequest::STATUS_DRAFT);

        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachmentFile = $form->get('attachment')->getData();

            if ($attachmentFile) {
                $newFilename = uniqid() . '.' . $attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/pdf',
                        $newFilename
                    );
                    // On enregistre le NOM du fichier dans la base de données
                    $purchaseRequest->setAttachment($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Erreur lors de l'upload");
                }
            }
            $entityManager->persist($purchaseRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('purchase_request/new.html.twig', [
            'purchase_request' => $purchaseRequest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_purchase_request_show', methods: ['GET', 'POST'])]
    public function show(PurchaseRequest $purchaseRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setPurchaseRequest($purchaseRequest);
        $comment->setUser($this->getUser());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_show', ['id' => $purchaseRequest->getId()]);
        }

        return $this->render('purchase_request/show.html.twig', [
            'purchase_request' => $purchaseRequest,
            'comment_form' => $form,
        ]);
    }
    #[Route('/{id}/submit', name: 'app_purchase_request_submit', methods: ['POST'])]
    public function submit(PurchaseRequest $purchaseRequest, Registry $registry, EntityManagerInterface $entityManager): Response
    {
        $workflow = $registry->get($purchaseRequest);

        if ($workflow->can($purchaseRequest, 'submit')) {
            $workflow->apply($purchaseRequest, 'submit');
            $entityManager->flush();
            $this->addFlash('success', 'Votre demande a été soumise avec succès.');
        } else {
            $this->addFlash('danger', 'Impossible de soumettre cette demande.');
        }

        return $this->redirectToRoute('app_purchase_request_show', ['id' => $purchaseRequest->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_purchase_request_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('purchase_request/edit.html.twig', [
            'purchase_request' => $purchaseRequest,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/approve', name: 'app_purchase_request_approve', methods: ['POST'])]
    public function approve(
        PurchaseRequest $purchaseRequest,
        Registry $registry,
        EntityManagerInterface $entityManager
    ): Response {
        $workflow = $registry->get($purchaseRequest);

        if (!$workflow->can($purchaseRequest, 'approve')) {
            $this->addFlash('warning', 'Cette demande ne peut pas être approuvée (statut incorrect).');
            return $this->redirectToRoute('app_purchase_request_show', ['id' => $purchaseRequest->getId()]);
        }

        // Sécurité : PO déjà existant
        if ($purchaseRequest->getPurchaseOrder() !== null) {
            $this->addFlash('warning', 'Un bon de commande existe déjà pour cette demande.');
            return $this->redirectToRoute('app_purchase_request_show', [
                'id' => $purchaseRequest->getId()
            ]);
        }

        // Apply transition
        $workflow->apply($purchaseRequest, 'approve');

        //Créer le Bon de Commande
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->setPurchaseRequest($purchaseRequest);

        // Numéro PO : PO-YYYY-XXXX
        $purchaseOrder->setOrderNumber(
            'PO-' . date('Y') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT)
        );

        //Calcul du total
        $totalAmount = $purchaseRequest->getProduct()->getPrice()
            * $purchaseRequest->getQuantity();

        $purchaseOrder->setTotalAmount($totalAmount);

        //Lier PR ↔ PO
        $purchaseRequest->setPurchaseOrder($purchaseOrder);

        //Sauvegarde
        $entityManager->persist($purchaseOrder);
        $entityManager->flush();

        $this->addFlash('success', 'Demande validée et bon de commande créé.');

        return $this->redirectToRoute('app_purchase_request_index');
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/reject', name: 'app_purchase_request_reject', methods: ['POST'])]
    public function reject(
        PurchaseRequest $purchaseRequest,
        Registry $registry,
        EntityManagerInterface $entityManager
    ): Response {
        $workflow = $registry->get($purchaseRequest);

        if (!$workflow->can($purchaseRequest, 'reject')) {
            $this->addFlash('warning', 'Cette demande ne peut pas être rejetée (statut incorrect).');
            return $this->redirectToRoute('app_purchase_request_show', ['id' => $purchaseRequest->getId()]);
        }

        $workflow->apply($purchaseRequest, 'reject');
        $entityManager->flush();

        $this->addFlash('danger', 'Demande rejetée.');

        return $this->redirectToRoute('app_purchase_request_index');
    }

    #[Route('/purchase-request/{id}', name: 'app_purchase_request_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        PurchaseRequest $purchaseRequest,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $purchaseRequest->getId(),
            $request->request->get('_token')
        )) {
            $em->remove($purchaseRequest);
            $em->flush();
            $this->addFlash('success', 'Demande supprimée avec succès.');
        }

        return $this->redirectToRoute('app_purchase_request_index');
    }
}
