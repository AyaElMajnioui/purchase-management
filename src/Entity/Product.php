<?php

namespace App\Entity;

use App\Entity\PurchaseRequest as EntityPurchaseRequest;
use App\Entity\User\PurchaseRequest;
use App\Entity\User\PurchaseRequest as UserPurchaseRequest;
use App\Entity\User\PurchaseRequest as EntityUserPurchaseRequest;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
     #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 3, minMessage: "Le nom doit faire au moins 3 caractères")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

   #[ORM\Column (type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    private ?string $price = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez choisir un fournisseur")]
    private ?Supplier $supplier = null;

    /**
     * @var Collection<int, PurchaseRequest>
     */
    #[ORM\OneToMany(targetEntity: EntityPurchaseRequest::class, mappedBy: 'product')]
    private Collection $purchaseRequests;

    public function __construct()
    {
        $this->purchaseRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Collection<int, PurchaseRequest>
     */
    public function getPurchaseRequests(): Collection
    {
        return $this->purchaseRequests;
    }

    public function addPurchaseRequest(EntityPurchaseRequest $purchaseRequest): static
    {
        if (!$this->purchaseRequests->contains($purchaseRequest)) {
            $this->purchaseRequests->add($purchaseRequest);
            $purchaseRequest->setProduct($this);
        }

        return $this;
    }

    public function removePurchaseRequest(EntityPurchaseRequest $purchaseRequest): static
    {
        if ($this->purchaseRequests->removeElement($purchaseRequest)) {
            // set the owning side to null (unless already changed)
            if ($purchaseRequest->getProduct() === $this) {
                $purchaseRequest->setProduct(null);
            }
        }

        return $this;
    }
}
