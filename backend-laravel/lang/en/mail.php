<?php

return [
    'greeting' => 'Hi :name,',
    'verify' => [
        'subject' => 'Verify your Picture Shop e-mail address',
        'line' => 'Please confirm your e-mail address to start buying and selling pictures.',
        'action' => 'Verify e-mail',
        'ignore' => 'If you did not create an account, no further action is required.',
    ],
    'reset' => [
        'subject' => 'Reset your Picture Shop password',
        'line' => 'You are receiving this e-mail because we received a password reset request for your account.',
        'action' => 'Reset password',
        'expire' => 'This link will expire in :count minutes.',
        'ignore' => 'If you did not request a password reset, no further action is required.',
    ],
    'order' => [
        'subject' => 'Your Picture Shop order',
        'line' => 'Thanks for your purchase! Your pictures are ready to download:',
        'total' => 'Total: $:total',
        'action' => 'Go to my downloads',
    ],
    'sold' => [
        'subject' => 'One of your pictures was sold',
        'line' => 'Good news! ":title" was just purchased by :buyer.',
        'amount' => 'Amount: $:amount',
    ],
];
