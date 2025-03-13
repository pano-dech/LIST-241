<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

/**
 * Home Controller
 */
class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index()
    {
        $productModel = new Product();
        
        // Get hot deals
        $hotDeals = $productModel->getHotDeals(3);
        
        $this->render('home/index', [
            'title' => 'Welcome to Tummy Pillow Bakery',
            'hot_deals' => $hotDeals
        ]);
    }
    
    /**
     * Display the about page
     */
    public function about()
    {
        $this->render('home/about', [
            'title' => 'Who We Are - Tummy Pillow Bakery'
        ]);
    }
}