<?php

/*
 * This file is part of blomstra/flarum-gdpr
 *
 * Copyright (c) 2021 Blomstra Ltd
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blomstra\Gdpr\Jobs;

use Blomstra\Gdpr\Models\ErasureRequest;
use Flarum\Api\ApiKey;
use Flarum\Http\AccessToken;
use Flarum\Queue\AbstractJob;
use Flarum\User\EmailToken;
use Flarum\User\LoginProvider;
use Flarum\User\PasswordToken;
use Flarum\User\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;

class ErasureJob extends AbstractJob
{
    /**
     * @var ErasureRequest
     */
    private $erasureRequest;

    /**
     * @var Builder
     */
    protected $schema;

    public function __construct(ErasureRequest $erasureRequest)
    {
        $this->erasureRequest = $erasureRequest;
    }

    public function handle(ConnectionInterface $connection): void
    {
        $this->schema = $connection->getSchemaBuilder();

        /** @var User */
        $user = User::findOrFail($this->erasureRequest->user_id);

        // Store these props here, as they'll be erased/anonymized in a moment
        $username = $user->getDisplayNameAttribute();
        $email = $user->email;

        $mode = $this->erasureRequest->processed_mode;

        $this->{$mode}($user);

        $this->sendUserConfirmation($username, $email);
    }

    private function deletion(User $user): void
    {
        $user->delete();
    }

    private function anonymization(User $user): void
    {
        ApiKey::where('user_id', $user->id)->delete();
        AccessToken::where('user_id', $user->id)->delete();
        EmailToken::where('user_id', $user->id)->delete();
        LoginProvider::where('user_id', $user->id)->delete();
        PasswordToken::where('user_id', $user->id)->delete();

        $columns = $this->schema->getColumnListing($user->getTable());

        $remove = ['id', 'username', 'password', 'email', 'is_email_confirmed', 'preferences'];

        foreach ($columns as $column) {
            if (in_array($column, $remove)) {
                continue;
            }

            $user->{$column} = null;
        }

        $user->username = Str::random(40);
        $user->email = "$user->username@flarum-gdpr.local";
        $user->is_email_confirmed = false;
        $user->setPasswordAttribute(Str::random(40));
        $user->preferences = [];

        $user->save();
    }

    private function sendUserConfirmation(string $username, string $email): void
    {
        // TODO
    }
}
