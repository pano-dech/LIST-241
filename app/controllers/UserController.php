<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Cart;

/**
 * User Controller
 */
class UserController extends Controller
{
    /**
     * Display login form
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->session->has('user_id')) {
            $this->redirect('/');
        }
        
        $this->render('users/login', [
            'title' => 'Login - Tummy Pillow Bakery'
        ]);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate()
    {
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/login');
            return;
        }
        
        // Get form data
        $email = $this->post('email');
        $password = $this->post('password');
        
        // Validate form data
        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Please enter email and password');
            $this->redirect('/login');
            return;
        }
        
        // Authenticate user
        $userModel = new User();
        $result = $userModel->authenticate($email, $password);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/login');
            return;
        }
        
        // Set session data
        $this->session->set('user_id', $result['user']['id']);
        $this->session->set('user_name', $result['user']['name']);
        $this->session->set('user_role', $result['user']['role']);
        
        // Initialize cart for user
        $cartModel = new Cart();
        $cartModel->getOrCreateCart($result['user']['id']);
        
        // Set success message
        $this->setFlash('success', 'Welcome back, ' . $result['user']['name'] . '!');
        
        // Redirect to appropriate page
        if ($result['user']['role'] === 'admin') {
            $this->redirect('/admin/orders');
        } else {
            $this->redirect('/');
        }
    }
    
    /**
     * Display registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->session->has('user_id')) {
            $this->redirect('/');
        }
        
        $this->render('users/register', [
            'title' => 'Register - Tummy Pillow Bakery'
        ]);
    }
    
    /**
     * Register a new user
     */
    public function store()
    {
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/register');
            return;
        }
        
        // Get form data
        $userData = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'phone_number' => $this->post('phone_number'),
            'address' => $this->post('address'),
            'password' => $this->post('password')
        ];
        
        // Validate form data
        if (empty($userData['name']) || empty($userData['email']) || empty($userData['phone_number']) || 
            empty($userData['address']) || empty($userData['password'])) {
            $this->setFlash('error', 'Please fill in all fields');
            $this->redirect('/register');
            return;
        }
        
        // Register user
        $userModel = new User();
        $result = $userModel->register($userData);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/register');
            return;
        }
        
        // Set success message
        $this->setFlash('success', 'Registration successful! Please log in.');
        $this->redirect('/login');
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        // Clear session data
        $this->session->remove('user_id');
        $this->session->remove('user_name');
        $this->session->remove('user_role');
        
        // Set success message
        $this->setFlash('success', 'You have been logged out');
        $this->redirect('/login');
    }
}