<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: "/get-all-reservations",
            security: "is_granted('ROLE_ADMIN')"
        ),
        new GetCollection(
            uriTemplate: "/user/{userId}/reservations",
            uriVariables: [
                'userId' => new Link(fromProperty: 'id', fromClass: User::class),
            ],
            provider: 'App\\State\\ReservationsByUserProvider'
        ),
        new Post(
            security: "is_granted('ROLE_USER')"
        ),
        new Get(
            uriTemplate: "/get-one-reservation/{id}",
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user) or (is_granted('ROLE_OWNER') and object.getPlace().getHost() == user)"
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and previous_object.getUser() == user and object.getStatus() == 'cancelled')"
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user)"
        ),
    ],
    normalizationContext: ['groups' => ['reservation:read', 'user:read', 'place:read']],
    denormalizationContext: ['groups' => ['reservation:write']]
)]
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?Place $place = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotBlank(message: "La date de début ne peut pas être vide")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date de début doit être aujourd'hui ou ultérieure"
    )]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotBlank(message: "La date de fin ne peut pas être vide")]
    #[Assert\GreaterThan(
        propertyPath: "startDate",
        message: "La date de fin doit être postérieure à la date de début"
    )]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le nombre de personnes ne peut pas être vide")]
    #[Assert\Positive(message: "Le nombre de personnes doit être positif")]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?int $numberOfGuests = null;

    #[ORM\Column(type: 'float')]
    #[Groups(['reservation:read'])]
    private ?float $totalPrice = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['reservation:read'])]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripePaymentId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['reservation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['reservation:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;
        $this->calculateTotalPrice();

        return $this;
    }

    public function getNumberOfGuests(): ?int
    {
        return $this->numberOfGuests;
    }

    public function setNumberOfGuests(int $numberOfGuests): static
    {
        $this->numberOfGuests = $numberOfGuests;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED])) {
            throw new \InvalidArgumentException("Statut invalide");
        }

        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStripePaymentId(): ?string
    {
        return $this->stripePaymentId;
    }

    public function setStripePaymentId(?string $stripePaymentId): static
    {
        $this->stripePaymentId = $stripePaymentId;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Calcule le nombre de jours entre la date de début et la date de fin
     */
    #[Groups(['reservation:read'])]
    public function getDurationInDays(): ?int
    {
        if (!$this->startDate || !$this->endDate) {
            return null;
        }

        $interval = $this->startDate->diff($this->endDate);
        return $interval->days;
    }

    /**
     * Calcule le prix total de la réservation
     */
    private function calculateTotalPrice(): void
    {
        if (!$this->place || !$this->startDate || !$this->endDate) {
            $this->totalPrice = 0;
            return;
        }

        $placePrice = $this->place->getPrice() ?? 0;
        $days = $this->getDurationInDays() ?? 0;
        $this->totalPrice = $placePrice * $days;
    }
}
