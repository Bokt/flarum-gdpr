<?php

/*
 * This file is part of blomstra/flarum-gdpr
 *
 * Copyright (c) 2021 Blomstra Ltd
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blomstra\Gdpr\Api\Controller;

use Blomstra\Gdpr\Api\Serializer\RequestErasureSerializer;
use Blomstra\Gdpr\Jobs\ErasureJob;
use Blomstra\Gdpr\Models\ErasureRequest;
use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ProcessErasureController extends AbstractShowController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = RequestErasureSerializer::class;

    /**
     * @var Queue
     */
    protected $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @inheritDoc
     */
    public function data(ServerRequestInterface $request, Document $document)
    {
        /** @var User $actor */
        $actor = RequestUtil::getActor($request);

        $actor->assertCan('processErasure');

        $id = Arr::get($request->getQueryParams(), 'id');

        $erasureRequest = ErasureRequest::findOrFail($id);

        $erasureRequest->status = 'processed';
        $erasureRequest->processed_mode = Arr::get($request->getParsedBody(), 'meta.mode');
        $erasureRequest->processed_at = Carbon::now();
        $erasureRequest->processed_by = $actor->id;
        $erasureRequest->processor_comment = Arr::get($request->getParsedBody(), 'data.attributes.processor_comment');

        $erasureRequest->save();

        $this->queue->push(new ErasureJob($erasureRequest));

        return $erasureRequest;
    }
}
