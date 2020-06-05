<?php

return [
    'channels' => [
        'none' => 'None',
        'on-site' => 'On-site Only',
        'email' => 'Email Only',
        'email-site' => 'On-site & Email',
    ],
    'notifications' => [
        'review' => [
            'title' => 'Spot Review',
            'description' => 'someone reviews one of your spots.'
        ],
        'comment' => [
            'title' => 'Spot Comment',
            'description' => 'someone comments on one of your spots.'
        ],
        'challenge' => [
            'title' => 'Spot Challenge',
            'description' => 'someone create a new challenge at one of your spots.'
        ],
        'entry' => [
            'title' => 'Challenge Entry',
            'description' => 'someone enters one of your challenges.'
        ],
        'challenge_won' => [
            'title' => 'Challenge Won',
            'description' => 'you win a challenge you entered.'
        ],
        'follower' => [
            'title' => 'New Follower',
            'description' => 'someone starts following you.'
        ],
        'new_spot' => [
            'title' => 'New Spot',
            'description' => 'someone you follow creates a new spot.'
        ],
        'new_challenge' => [
            'title' => 'New Challenge',
            'description' => 'someone you follow creates a new challenge.'
        ]
    ],
];
