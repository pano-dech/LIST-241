<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

/**
 * Menu Controller
 */
class MenuController extends Controller
{
    /**
     * Display menu with all products
     */
    public function index()
    {
        $productModel = new Product();
        
        // Get all categories
        $categories = $productModel->getAllCategories();
        
        // Get products by category
        $productsByCategory = [];
        foreach ($categories as $category) {
            $products = $productModel->getProductsByCategory($category['id']);
            if (!empty($products)) {
                $productsByCategory[$category['id']] = [
                    'category' => $category,
                    'products' => $products
                ];
            }
        }
        
        $this->render('menu/index', [
            'title' => 'Menu - Tummy Pillow Bakery',
            'categories' => $categories,
            'productsByCategory' => $productsByCategory
        ]);
    }
    
    /**
     * Display products by category
     * 
     * @param int $categoryId
     */
    public function category($categoryId)
    {
        $productModel = new Product();
        
        // Get category
        $categories = $productModel->getAllCategories();
        $category = null;
        
        foreach ($categories as $cat) {
            if ($cat['id'] == $categoryId) {
                $category = $cat;
                break;
            }
        }
        
        if (!$category) {
            $this->setFlash('error', 'Category not found');
            $this->redirect('/menu');
            return;
        }
        
        // Get products
        $products = $productModel->getProductsByCategory($categoryId);
        
        $this->render('menu/category', [
            'title' => $category['name'] . ' - Tummy Pillow Bakery',
            'category' => $category,
            'products' => $products,
            'categories' => $categories
        ]);
    }
    
    /**
     * Display product details
     * 
     * @param int $productId
     */
    public function product($productId)
    {
        $productModel = new Product();
        
        // Get product
        $product = $productModel->getProductWithCategory($productId);
        
        if (!$product) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('/menu');
            return;
        }
        
        // Get related products
        $relatedProducts = $productModel->getProductsByCategory($product['category_id']);
        
        // Remove current product from related products
        foreach ($relatedProducts as $key => $relatedProduct) {
            if ($relatedProduct['id'] == $productId) {
                unset($relatedProducts[$key]);
                break;
            }
        }
        
        // Get only 4 related products
        $relatedProducts = array_slice($relatedProducts, 0, 4);
        
        $this->render('menu/product', [
            'title' => $product['name'] . ' - Tummy Pillow Bakery',
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }
    
    /**
     * Search products
     */
    public function search()
    {
        $keyword = $this->get('keyword', '');
        
        if (empty($keyword)) {
            $this->redirect('/menu');
            return;
        }
        
        $productModel = new Product();
        $products = $productModel->searchProducts($keyword);
        
        $this->render('menu/search', [
            'title' => 'Search Results - Tummy Pillow Bakery',
            'keyword' => $keyword,
            'products' => $products
        ]);
    }
}