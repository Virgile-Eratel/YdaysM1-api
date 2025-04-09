<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReviewsByPlaceProvider implements ProviderInterface
{
    private ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables['placeId'])) {
            throw new NotFoundHttpException('Place ID is required');
        }

        $placeId = $uriVariables['placeId'];
        
        return $this->reviewRepository->findByPlaceId($placeId);
    }
}
