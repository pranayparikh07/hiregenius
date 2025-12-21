<?php
/**
 * HireGenius - Configuration Example
 * 
 * Copy this file to config.php and update the values
 * DO NOT commit config.php to version control
 */

return [
    // ===========================================
    // Database Configuration
    // ===========================================
    'database' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'hiregenius',
        'charset'  => 'utf8mb4',
    ],

    // ===========================================
    // Application Configuration
    // ===========================================
    'app' => [
        'name'        => 'HireGenius',
        'tagline'     => 'Smart Video Interview Platform',
        'url'         => 'http://localhost/HireGenius',
        'timezone'    => 'UTC',
        'debug'       => false,
    ],

    // ===========================================
    // Security Configuration
    // ===========================================
    'security' => [
        'session_lifetime' => 3600, // 1 hour
        'csrf_enabled'     => true,
        'password_min_length' => 8,
    ],

    // ===========================================
    // Interview Settings
    // ===========================================
    'interview' => [
        'default_time_per_question' => 180, // seconds
        'max_questions'             => 20,
        'code_length'               => 6,
    ],

    // ===========================================
    // Default Interview Questions
    // ===========================================
    'default_questions' => [
        "Tell me about yourself.",
        "Why do you want this job?",
        "What are your greatest strengths?",
        "What are your weaknesses and how do you manage them?",
        "Where do you see yourself in 5 years?",
        "What do you know about our company?",
        "Describe a challenging situation and how you handled it.",
        "Why should we hire you?",
        "Do you have any questions for us?"
    ],
];
