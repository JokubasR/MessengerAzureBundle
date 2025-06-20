<?php

declare(strict_types=1);

namespace AymDev\MessengerAzureBundle\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Azure Service Bus stamp for BrokerProperties
 */
class AzureBrokerPropertiesStamp implements StampInterface
{
    public function __construct(
        private ?string $contentType = null,
        private ?string $correlationId = null,
        private ?string $sessionId = null,
        private readonly ?int $deliveryCount = null,
        private readonly ?\DateTimeInterface $lockedUntilUtc = null,
        private readonly ?string $lockToken = null,
        private ?string $messageId = null,
        private ?string $label = null,
        private ?string $replyTo = null,
        private readonly ?\DateTimeInterface $enqueuedTimeUtc = null,
        private readonly ?int $sequenceNumber = null,
        private ?float $timeToLive = null,
        private ?string $to = null,
        private ?\DateTimeInterface $scheduledEnqueueTimeUtc = null,
        private ?string $replyToSessionId = null,
        private ?string $partitionKey = null
    ) {
    }


    /**
     * Create a stamp from an HTTP response in the receiver transport
     * @throws HttpExceptionInterface|TransportExceptionInterface|\Exception
     * @internal
     */
    public static function createFromResponse(ResponseInterface $response): self
    {
        $header = $response->getHeaders()['brokerproperties'][0] ?? '';
        return self::createFromJson($header);
    }

    /**
     * Create a stamp from a json string
     * @throws \Exception
     */
    public static function createFromJson(string $json): self
    {
        /**
         * @var null|array{
         *     ContentType?: string,
         *     CorrelationId?: string,
         *     SessionId?: string,
         *     DeliveryCount?: int,
         *     LockedUntilUtc?: string,
         *     LockToken?: string,
         *     MessageId?: string,
         *     Label?: string,
         *     ReplyTo?: string,
         *     EnqueuedTimeUtc?: string,
         *     SequenceNumber?: int,
         *     TimeToLive?: float,
         *     To?: string,
         *     ScheduledEnqueueTimeUtc?: string,
         *     ReplyToSessionId?: string,
         *     PartitionKey?: string
         * } $properties
         */
        $properties = json_decode($json, true);
        $defaultTimeZone = new \DateTimeZone(date_default_timezone_get());

        $lockedUntilUtc = null;
        if (isset($properties['LockedUntilUtc'])) {
            $lockedUntilUtc = new \DateTimeImmutable($properties['LockedUntilUtc']);
            $lockedUntilUtc = $lockedUntilUtc->setTimezone($defaultTimeZone);
        }

        $enqueuedTimeUtc = null;
        if (isset($properties['EnqueuedTimeUtc'])) {
            $enqueuedTimeUtc = new \DateTimeImmutable($properties['EnqueuedTimeUtc']);
            $enqueuedTimeUtc = $enqueuedTimeUtc->setTimezone($defaultTimeZone);
        }

        $scheduledEnqueueTimeUtc = null;
        if (isset($properties['ScheduledEnqueueTimeUtc'])) {
            $scheduledEnqueueTimeUtc = new \DateTimeImmutable($properties['ScheduledEnqueueTimeUtc']);
            $scheduledEnqueueTimeUtc = $scheduledEnqueueTimeUtc->setTimezone($defaultTimeZone);
        }

        $timeToLive = null;
        if (isset($properties['TimeToLive'])) {
            $timeToLive = floatval($properties['TimeToLive']);
        }

        return new self(
            $properties['ContentType'] ?? null,
            $properties['CorrelationId'] ?? null,
            $properties['SessionId'] ?? null,
            $properties['DeliveryCount'] ?? null,
            $lockedUntilUtc,
            $properties['LockToken'] ?? null,
            $properties['MessageId'] ?? null,
            $properties['Label'] ?? null,
            $properties['ReplyTo'] ?? null,
            $enqueuedTimeUtc,
            $properties['SequenceNumber'] ?? null,
            $timeToLive,
            $properties['To'] ?? null,
            $scheduledEnqueueTimeUtc,
            $properties['ReplyToSessionId'] ?? null,
            $properties['PartitionKey'] ?? null
        );
    }

    /**
     * Encode the broker properties in JSON for sending to Azure Service Bus
     * @throws \JsonException
     * @internal
     */
    public function encode(): string
    {
        $properties = [
            'ContentType' => $this->contentType,
            'CorrelationId' => $this->correlationId,
            'SessionId' => $this->sessionId,
            'DeliveryCount' => $this->deliveryCount,
            'LockedUntilUtc' => $this->lockedUntilUtc !== null
                ? $this->lockedUntilUtc->format('Y-m-d H:i:s')
                : null,
            'LockToken' => $this->lockToken,
            'MessageId' => $this->messageId,
            'Label' => $this->label,
            'ReplyTo' => $this->replyTo,
            'EnqueuedTimeUtc' => $this->enqueuedTimeUtc !== null
                ? $this->enqueuedTimeUtc->format('Y-m-d H:i:s')
                : null,
            'SequenceNumber' => $this->sequenceNumber,
            'TimeToLive' => $this->timeToLive,
            'To' => $this->to,
            'ScheduledEnqueueTimeUtc' => $this->scheduledEnqueueTimeUtc !== null
                ? $this->scheduledEnqueueTimeUtc->format('Y-m-d H:i:s')
                : null,
            'ReplyToSessionId' => $this->replyToSessionId,
            'PartitionKey' => $this->partitionKey
        ];

        $properties = array_filter($properties, function ($property): bool {
            return !is_null($property);
        });

        return json_encode($properties, JSON_FORCE_OBJECT | JSON_THROW_ON_ERROR);
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(?string $correlationId): self
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getDeliveryCount(): ?int
    {
        return $this->deliveryCount;
    }

    public function getLockedUntilUtc(): ?\DateTimeInterface
    {
        return $this->lockedUntilUtc;
    }

    public function getLockToken(): ?string
    {
        return $this->lockToken;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): self
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function getEnqueuedTimeUtc(): ?\DateTimeInterface
    {
        return $this->enqueuedTimeUtc;
    }

    public function getSequenceNumber(): ?int
    {
        return $this->sequenceNumber;
    }

    public function getTimeToLive(): ?float
    {
        return $this->timeToLive;
    }

    public function setTimeToLive(?float $timeToLive): self
    {
        $this->timeToLive = $timeToLive;
        return $this;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function getScheduledEnqueueTimeUtc(): ?\DateTimeInterface
    {
        return $this->scheduledEnqueueTimeUtc;
    }

    public function setScheduledEnqueueTimeUtc(?\DateTimeInterface $scheduledEnqueueTimeUtc): self
    {
        $this->scheduledEnqueueTimeUtc = $scheduledEnqueueTimeUtc;
        return $this;
    }

    public function getReplyToSessionId(): ?string
    {
        return $this->replyToSessionId;
    }

    public function setReplyToSessionId(?string $replyToSessionId): self
    {
        $this->replyToSessionId = $replyToSessionId;
        return $this;
    }

    public function getPartitionKey(): ?string
    {
        return $this->partitionKey;
    }

    public function setPartitionKey(?string $partitionKey): self
    {
        $this->partitionKey = $partitionKey;
        return $this;
    }
}
