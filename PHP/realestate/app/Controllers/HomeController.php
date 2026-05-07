<?php

namespace App\Controllers;

/**
 * HomeController – Handles the public landing page.
 */
class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home.index', [
            'title'       => 'NextGen Real Estate – Find Your Dream Property',
            'description' => 'Search thousands of properties for sale and rent in Bangladesh.',
        ]);
    }
}
