<?php
// api/src/Encoder/MultipartDecoder.php

namespace App\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        // Récupère les champs texte
        $textFields = $request->request->all();
        // Convertir les champs numériques en float
        foreach ($textFields as $key => $value) {
            // Si c'est un string qui représente un nombre, on le cast en float
            if (is_numeric($value)) {
                $textFields[$key] = (float) $value;
            }
        }

        // Récupère les fichiers
        $fileFields = $request->files->all();

        // Combine le tout
        return $textFields + $fileFields;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
