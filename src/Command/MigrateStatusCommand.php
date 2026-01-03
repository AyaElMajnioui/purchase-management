<?php

namespace App\Command;

use App\Entity\PurchaseRequest;
use App\Repository\PurchaseRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-status',
    description: 'Migrates legacy French statuses to English workflow keys',
)]
class MigrateStatusCommand extends Command
{
    public function __construct(
        private PurchaseRequestRepository $purchaseRequestRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $purchaseRequests = $this->purchaseRequestRepository->findAll();
        $count = 0;

        foreach ($purchaseRequests as $pr) {
            $status = $pr->getStatus();
            $newStatus = null;

            switch ($status) {
                case 'En attente':
                    $newStatus = 'submitted'; // Assuming pending requests are already submitted awaiting approval
                    break;
                case 'Validée':
                case 'Approuvée': // Handle potential variants
                    $newStatus = 'approved';
                    break;
                case 'Rejetée':
                    $newStatus = 'rejected';
                    break;
                default:
                    // If it's already a valid key or unknown
                    if (in_array($status, ['draft', 'submitted', 'approved', 'rejected'])) {
                        continue 2;
                    }
                    $io->warning("Unknown status found: $status for PR #{$pr->getId()}");
                    break;
            }

            if ($newStatus) {
                $pr->setStatus($newStatus);
                $count++;
            }
        }

        $this->entityManager->flush();

        $io->success("Migrated $count purchase requests to new status keys.");

        return Command::SUCCESS;
    }
}
