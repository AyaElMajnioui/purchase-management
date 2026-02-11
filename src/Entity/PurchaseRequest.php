<?php

namespace App\Entity;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\PurchaseOrder;
use App\Repository\PurchaseRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Comment;

#[ORM\Entity(repositoryClass: PurchaseRequestRepository::class)]
class PurchaseRequest
{
    public const STATUS_DRAFT    = 'draft';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    #[ORM\Column(length: 20)]
    private ?string $status =  'draft';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchaseRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requester = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'purchaseRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\OneToOne(targetEntity: PurchaseOrder::class, mappedBy: 'purchaseRequest', cascade: ['persist'])]
    private ?PurchaseOrder $purchaseOrder = null;


    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThanOrEqual(1)]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min: 10)]
    private ?string $justification = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachment = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'purchaseRequest', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requestedby): static
    {
        $this->requester = $requestedby;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(string $justification): static
    {
        $this->justification = $justification;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): static
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
    public function getPurchaseOrder(): ?PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(?PurchaseOrder $purchaseOrder): self
    {
        $this->purchaseOrder = $purchaseOrder;
        if ($purchaseOrder !== null && $purchaseOrder->getPurchaseRequest() !== $this) {
            $purchaseOrder->setPurchaseRequest($this);
        }
        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPurchaseRequest($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPurchaseRequest() === $this) {
                $comment->setPurchaseRequest(null);
            }
        }

        return $this;
    }
}
