<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        // In a real application, you would fetch this data from your models
        // For now, we'll use static data for demonstration
        
        return view('dashboard');
    }
}