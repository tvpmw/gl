<?php

namespace App\Controllers;

class ErrorController extends BaseController
{
    public function unauthorized()
    {
        return view('errors/unauthorized');
    }

    public function dashboard_unauthorized()
    {
        return view('dashboard_unauthorized');
    }
}