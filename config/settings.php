<?php

return [
    'notification_channels' => [
        'none' => 'None',
        'on-site' => 'On-site Only',
        'email' => 'Email Only',
        'email-site' => 'On-site & Email',
    ],
    'notifications' => [
        'notifications_review' => [
            'title' => 'Spot Review',
            'description' => 'someone reviews one of your spots.'
        ],
        'notifications_comment' => [
            'title' => 'Spot Comment',
            'description' => 'someone comments on one of your spots.'
        ],
        'notifications_challenge' => [
            'title' => 'Spot Challenge',
            'description' => 'someone create a new challenge at one of your spots.'
        ],
        'notifications_entry' => [
            'title' => 'Challenge Entry',
            'description' => 'someone enters one of your challenges.'
        ],
        'notifications_challenge_won' => [
            'title' => 'Challenge Won',
            'description' => 'you win a challenge you entered.'
        ],
        'notifications_follower' => [
            'title' => 'New Follower',
            'description' => 'someone starts following you.'
        ],
        'notifications_new_spot' => [
            'title' => 'New Spot',
            'description' => 'someone you follow creates a new spot.'
        ],
        'notifications_new_challenge' => [
            'title' => 'New Challenge',
            'description' => 'someone you follow creates a new challenge.'
        ]
    ],
    'privacy' => [
        'privacy_follow' => [
            'title' => 'Followers',
            'description' => 'follow you.',
            'options' => [
                'nobody' => 'Nobody',
                'request' => 'Requesters (you accept or decline)',
                'anybody' => 'Anybody',
            ]
        ],
        'privacy_hometown' => [
            'title' => 'Hometown',
            'description' => 'view your hometown.',
            'options' => [
                'nobody' => 'Nobody',
                'follower' => 'Followers',
                'anybody' => 'Anybody',
            ]
        ],
    ],
];
