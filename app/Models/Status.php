<?php

namespace App\Models;

enum Status: string
{
    case PUBLIC = 'public';
    case PUBLICOFF = 'publicoff';
    case PENDING = 'pending';
}
