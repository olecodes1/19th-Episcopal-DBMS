<?php

/**
 * Centralized configuration file
 * This file contains all configuration values that were previously hardcoded
 */

return [
    // Database configuration
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: '19edypd_db',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'socket' => getenv('DB_SOCKET') ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
    ],
    
    // Application settings
    'app' => [
        'debug' => getenv('APP_DEBUG') === '1',
        'name' => '19th Episcopal District AdminDash',
        'timezone' => 'UTC',
    ],
    
    // Security settings
    'security' => [
        'session_timeout' => 3600, // 1 hour in seconds
        'csrf_token_length' => 32,
        'password_min_length' => 8,
        'login_rate_limit' => [
            'attempts' => 5,
            'window' => 900, // 15 minutes in seconds
        ],
    ],
    
    // File upload settings
    'uploads' => [
        'max_file_size' => 52428800, // 50MB in bytes
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_videos' => ['mp4', 'webm', 'ogg', 'mov'],
        'allowed_audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'upload_dir' => __DIR__ . '/assets/uploads/media',
    ],
    
    // URL paths
    'urls' => [
        'base' => '/PhpstormProjects/19thepiscopaldistrict/AdminDash',
        'login' => '/PhpstormProjects/19thepiscopaldistrict/AdminDash/login.php',
        'home' => '/PhpstormProjects/19thepiscopaldistrict/AdminDash/index.php',
    ],
    
    // District information
    'district' => [
        'id' => 19,
        'name' => '19th Episcopal District',
    ],
];