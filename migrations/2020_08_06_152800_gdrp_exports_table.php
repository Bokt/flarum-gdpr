<?php

/*
 * This file is part of blomstra/flarum-gdpr
 *
 * Copyright (c) 2021 Blomstra Ltd
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable(
    'gdpr_exports',
    function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id')->unsigned()->nullable();
        $table->string('file')->nullable();
        $table->dateTime('created_at');
        $table->dateTime('destroys_at');
    }
);
