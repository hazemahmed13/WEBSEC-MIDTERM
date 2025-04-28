<?php

return [
    'driver' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'your-email@gmail.com', // Replace with your Gmail address
    'password' => 'your-app-password',    // Replace with your Gmail App Password
    'from' => [
        'address' => 'your-email@gmail.com',
        'name' => 'Your App Name',
    ],
]; 