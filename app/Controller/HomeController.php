<?php

namespace App\Controller;

use App\Cores\Request;
use App\Cores\Response;
use App\Cores\View;

final class HomeController
{
    public static function index(Request $req, array $args): Response
    {
        return Response::html(View::page('home'));
    }
}
