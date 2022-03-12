<?php

namespace Imdhemy\GooglePlay\Tests\Subscriptions;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Imdhemy\GooglePlay\ClientFactory;
use Imdhemy\GooglePlay\Subscriptions\SubscriptionClient;
use Imdhemy\GooglePlay\Subscriptions\SubscriptionDeferralInfo;
use Imdhemy\GooglePlay\Subscriptions\SubscriptionPurchase;
use Imdhemy\GooglePlay\Tests\TestCase;
use Imdhemy\GooglePlay\ValueObjects\AcknowledgementState;
use Imdhemy\GooglePlay\ValueObjects\EmptyResponse;
use Imdhemy\GooglePlay\ValueObjects\Time;

class SubscriptionClientTest extends TestCase
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $subscriptionId;

    /**
     * @var string
     */
    private $token;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->packageName = 'com.some.thing';
        $this->subscriptionId = 'fake_id';
        $this->token = 'fake_token';
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_get()
    {
        $client = ClientFactory::mock(new Response(200, [], json_encode([])));
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');
        $this->assertInstanceOf(SubscriptionPurchase::class, $subscriptionClient->get());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_acknowledge()
    {
        $acknowledgeResponse = new Response(200, [], json_encode([]));
        $getResponse = new Response(
            200,
            [],
            json_encode(['acknowledgementState' => AcknowledgementState::ACKNOWLEDGED])
        );
        $client = ClientFactory::mockQueue([$acknowledgeResponse, $getResponse]);
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');

        $this->assertInstanceOf(EmptyResponse::class, $subscriptionClient->acknowledge());
        $this->assertTrue($subscriptionClient->get()->getAcknowledgementState()->isAcknowledged());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_cancel()
    {
        $cancelResponse = new Response();
        $client = ClientFactory::mock($cancelResponse);
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');
        $this->assertInstanceOf(EmptyResponse::class, $subscriptionClient->cancel());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_refund()
    {
        $refundResponse = new Response();
        $client = ClientFactory::mock($refundResponse);
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');
        $this->assertInstanceOf(EmptyResponse::class, $subscriptionClient->cancel());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_revoke()
    {
        $revokeResponse = new Response();
        $client = ClientFactory::mock($revokeResponse);
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');
        $this->assertInstanceOf(EmptyResponse::class, $subscriptionClient->cancel());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function test_defer()
    {
        $desiredExpiryTimeMillis = $this->faker->dateTime->getTimestamp() * 1000;
        $deferResponse = new Response(200, [], json_encode(['newExpiryTimeMillis' => $desiredExpiryTimeMillis]));
        $client = ClientFactory::mock($deferResponse);
        $subscriptionClient = new SubscriptionClient($client, 'com.some.thing', 'fake_id', 'fake_token');
        $deferralInfo = new SubscriptionDeferralInfo(0, $desiredExpiryTimeMillis);
        $newExpiryTime = $subscriptionClient->defer($deferralInfo);
        $this->assertInstanceOf(Time::class, $newExpiryTime);
        $this->assertEquals($desiredExpiryTimeMillis, $newExpiryTime->getCarbon()->getTimestampMs());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function refund()
    {
        $refundResponse = new Response();
        $transactions = [];
        $client = ClientFactory::mock($refundResponse, $transactions);

        $subscriptionClient = new SubscriptionClient($client, $this->packageName, $this->subscriptionId, $this->token);

        $response = $subscriptionClient->refund();
        $this->assertInstanceOf(EmptyResponse::class, $response);

        $uri = sprintf(SubscriptionClient::URI_REFUND, $this->packageName, $this->subscriptionId, $this->token);

        /** @var Request $request */
        $request = $transactions[0]['request'];
        $this->assertEquals($uri, (string)$request->getUri());
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function revoke()
    {
        $revokeResponse = new Response();
        $transactions = [];
        $client = ClientFactory::mock($revokeResponse, $transactions);

        $subscriptionClient = new SubscriptionClient($client, $this->packageName, $this->subscriptionId, $this->token);

        $response = $subscriptionClient->revoke();
        $this->assertInstanceOf(EmptyResponse::class, $response);

        $uri = sprintf(SubscriptionClient::URI_REVOKE, $this->packageName, $this->subscriptionId, $this->token);

        /** @var Request $request */
        $request = $transactions[0]['request'];
        $this->assertEquals($uri, (string)$request->getUri());
    }
}
