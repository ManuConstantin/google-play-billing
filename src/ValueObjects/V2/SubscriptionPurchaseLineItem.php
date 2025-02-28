<?php

namespace Imdhemy\GooglePlay\ValueObjects\V2;

use JsonSerializable;

/**
 * Subscription purchase class
 * Item-level info for a subscription purchase.
 *
 * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.subscriptionsv2#SubscriptionPurchaseLineItem
 */
class SubscriptionPurchaseLineItem implements JsonSerializable
{
    /**
     * @var string|null
     */
    protected $productId;

    /**
     * @var string|null
     */
    protected $expiryTime;

    /**
     * @var string|null
     */
    protected $autoRenewingPlan;

    /**
     * @var PrepaidPlan|null
     */
    protected $prepaidPlan;


    /**
     * @var OfferDetails|null
     */
    protected $offerDetails;

    /**
     * @var array
     */
    protected $rawData;

    protected $casts = [
        'autoRenewingPlan' => AutoRenewingPlan::class,
        'prepaidPlan' => PrepaidPlan::class,
        'offerDetails' => OfferDetails::class,
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

    public function getNewPrice(): ?Money
    {
        return $this->newPrice;
    }

    public function getPriceChangeMode(): ?string
    {
        return $this->priceChangeMode;
    }

    public function getPriceChangeState(): ?string
    {
        return $this->priceChangeState;
    }

    public function getExpectedNewPriceChargeTime(): ?string
    {
        return $this->expectedNewPriceChargeTime;
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
