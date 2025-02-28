<?php

namespace Imdhemy\GooglePlay\ValueObjects\V2;

use JsonSerializable;

/**
 * Subscription purchase class
 * Information specific to cancellations initiated by users.
 *
 * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.subscriptionsv2#UserInitiatedCancellation
 */
class UserInitiatedCancellation implements JsonSerializable
{

    /**
     * @var CancelSurveyResult|null
     */
    protected $cancelSurveyResult;

    /**
     * @var string|null
     */
    protected $cancelTime;

    /**
     * @var array
     */
    protected $rawData;

    protected $casts = [
        'cancelSurveyResult' => CancelSurveyResult::class
    ];

    /**
     * Subscription Purchase Constructor.
     */
    public function __construct(array $responseBody = [])
    {
        $attributes = array_keys(get_class_vars(self::class));
        foreach ($attributes as $attribute) {
            if (isset($responseBody[$attribute])) {
                if (isset($this->casts[$attribute])) {
                    $this->$attribute = $this->casts[$attribute]::fromArray($responseBody[$attribute]);
                    continue;
                }
                $this->$attribute = $responseBody[$attribute];
            }
        }
        $this->rawData = $responseBody;
    }

    public function getCancelSurveyResult(): ?CancelSurveyResult
    {
        return $this->cancelSurveyResult;
    }

    public function getCancelTime(): ?string
    {
        return $this->cancelTime;
    }

    public static function fromArray(array $responseBody): self
    {
        return new self($responseBody);
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function toArray(): array
    {
        return $this->getRawData();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
