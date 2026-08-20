<?php

namespace App\Services\Ai\Images;

use App\Domains\Engine\Services\FalAIService as FalAIApiService;
use App\Domains\Entity\Enums\EntityEnum;
use App\Services\Ai\Images\Contracts\ImageGeneratorInterface;
use InvalidArgumentException;

class FalAIImageService implements ImageGeneratorInterface
{
    protected FalAIApiService $falService;

    public function __construct(FalAIApiService $falService)
    {
        $this->falService = $falService;
    }

    public function generate(array $options): array
    {
        $model = EntityEnum::fromSlug($options['model'] ?? 'flux-pro') ?? EntityEnum::FLUX_PRO;
        $prompt = $options['prompt'] ?? throw new InvalidArgumentException('Prompt is required');

        // Generate request ID
        $requestId = match ($model) {
            EntityEnum::IDEOGRAM => $this->falService->ideogramGenerate($prompt, $model, $options),
            EntityEnum::FLUX_PRO_KONTEXT,
            EntityEnum::FLUX_PRO_KONTEXT_MAX_MULTI => $this->falService->generateKontext(
                $prompt,
                $model,
                $options['image_src'] ?? null
            ),
            default => $this->falService->generate($prompt, $model, $options),
        };

        // Return placeholder for async processing
        return [
            'request_id' => $requestId,
            'status'     => 'IN_QUEUE',
            'model'      => $model->value,
        ];
    }

    public function supportsAsync(): bool
    {
        return true;
    }

    public function checkStatus(string $requestId, EntityEnum $entityEnum): ?array
    {
        return $this->falService->check($requestId, $entityEnum);
    }
}
