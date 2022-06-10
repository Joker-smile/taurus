<?php

namespace App\Repositories;

use App\Models\Message;

class MessageRepository extends AbstractRepository
{
    public function model(): string
    {
        return Message::class;
    }
}
