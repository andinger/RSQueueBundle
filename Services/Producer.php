<?php

/**
 * RSQueueBundle for Symfony2
 *
 * Marc Morera 2013
 */

namespace Mmoreram\RSQueueBundle\Services;

use Mmoreram\RSQueueBundle\Services\Abstracts\AbstractService;
use Mmoreram\RSQueueBundle\RSQueueEvents;
use Mmoreram\RSQueueBundle\Event\RSQueueProducerEvent;

/**
 * Provider class
 */
class Producer extends AbstractService
{
    /**
     * Enqueues payload inside desired queue
     *
     * @param Mixed  $payload    Data to enqueue
     * @param String $queueAlias Name of queue to enqueue payload
     *
     * @return Producer self Object
     *
     * @throws InvalidAliasException If any alias is not defined
     */
    public function produce($payload, $queueAlias)
    {
        $queue = $this->queueAliasResolver->get($queueAlias);
        $payloadSerialized = $this->serializer->apply($payload);

        $this->redis->rpush(
            $queue,
            $payloadSerialized
        );

        /**
         * Dispatching producer event...
         */
        $producerEvent = new RSQueueProducerEvent($payload, $payloadSerialized, $queueAlias, $queue, $this->redis);
        $this->eventDispatcher->dispatch(RSQueueEvents::RSQUEUE_PRODUCER, $producerEvent);

        return $this;
    }
}