<?php

/*
 * This file is part of blomstra/flarum-gdpr
 *
 * Copyright (c) 2021 Blomstra Ltd
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blomstra\Gdpr\Http\Controller;

use Blomstra\Gdpr\Exporter;
use Blomstra\Gdpr\Models\Export;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Validation\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExportController implements RequestHandlerInterface
{
    protected $zip;

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('actor');

        $file = Arr::get($request->getQueryParams(), 'file');

        if (!$actor || !$file) {
            throw new UnauthorizedException();
        }

        $export = Export::byFile($file);

        if ($export) {
            return resolve(Exporter::class)->getZip($export);
        }

        throw new FileNotFoundException();
    }
}
