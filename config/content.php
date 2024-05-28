<?php

return [

    'notifications' => [
        'friend_request' => 'sent you a friend request',
        'channel_invitation' => 'invited you to join their private channel',
    ],

    'push_notifications' => [
        'friend_request' => [
            'title' => '🤗 Friendship alert! 🚨',
            'content' => "You've received a friend request! 👀",
        ],
        'friend_request_accepted' => [
            'title' => '🥳 Woohoo! 🎉',
            'content' => 'Your friend request was accepted! 🎈',
        ],
        'channel_invitation' => [
            'title' => '🔔 Walkie Talkie',
            'content' => "You're invited to a new channel! 🚀 Time to join the fun! 🎈",
        ],
        'join_channel' => [
            'title' => '🎉 Invitation Accepted! 🤝',
            'content' => "💞Your friend's in the private channel! Let's talk! 💫",
        ],
        'message' => [
            'title' => '🚨 Ding ding! 🛎️',
            'content' => "💬 You've got a new message waiting for you! 📩",
        ],
        'inactive_user' => [
            [
                'title' => '🥺 Missing you! 😔',
                'content' => 'Come back and bring your magic! 🌟',
            ],
            [
                'title' => '😏 Long time no see! 💔',
                'content' => "Open it up and let's pick up where we left off! 💤",
            ],
            [
                'title' => "😴 Where'd you go? 🥺",
                'content' => "😔 We're waiting for you! 💬",
            ],
        ],
        'free_user' => [
            [
                'title' => '🫰Want more from our app? 👀',
                'content' => '🚀 Upgrade to premium for an ad-free experience and exclusive features! Upgrade now!',
            ],
            [
                'title' => '🔥Ready for premium perks? 👑',
                'content' => '🚀 Take your experience to the next level! Upgrade to premium today!',
            ],
            [
                'title' => '🤯 Missing out our VIP Features? 😱',
                'content' => 'Get premium features! Upgrade now for VIP treatment!!',
            ],
        ],
        'birthday' => [
            [
                'title' => '🎉 Happy Birthday! 🎂',
                'content' => '🎁 Special gift: 50% off our premium package! Upgrade now and elevate your experience! ✨',
            ],
            [
                'title' => "🎂 It's your special day! 🎉",
                'content' => '💝 Enjoy 50% off our premium package as our gift to you! Upgrade now! 🎁',
            ],
            [
                'title' => '🎉 Celebrate your birthday in style! 🎂',
                'content' => '🎉 Enjoy 50% off our premium package today only! Upgrade now! 🌟',
            ],

        ],

    ],
];
