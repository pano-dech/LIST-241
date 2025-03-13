<?php
namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';
    
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'password_hash',
        'role',
        'is_active'
    ];
    
    /**
     * Register a new user
     * 
     * @param array $data
     * @return array|bool
     */
    public function register($data)
    {
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email address'
            ];
        }
        
        // Check if email already exists
        $existingUser = $this->findOneBy('email', $data['email']);
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }
        
        // Validate password
        if (strlen($data['password']) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long'
            ];
        }
        
        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Set default role
        $data['role'] = 'customer';
        
        // Create user
        $userId = $this->create($data);
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
        }
        
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'User registered successfully'
        ];
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $email
     * @param string $password
     * @return array|bool
     */
    public function authenticate($email, $password)
    {
        // Get user by email
        $user = $this->findOneBy('email', $email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            return [
                'success' => false,
                'message' => 'Your account has been deactivated'
            ];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
        
        // Remove password hash from user data
        unset($user['password_hash']);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Logged in successfully'
        ];
    }
    
    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data)
    {
        // Get user
        $user = $this->find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Validate email if changed
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email address'
                ];
            }
            
            // Check if email already exists
            $existingUser = $this->findOneBy('email', $data['email']);
            
            if ($existingUser && $existingUser['id'] !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Email already registered'
                ];
            }
        }
        
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 8 characters long'
                ];
            }
            
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        // Update user
        $updated = $this->update($userId, $data);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Failed to update profile'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    }
}