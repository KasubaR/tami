<?php

/*
 * All personal wording lives here so it is easy to change.
 * DRAFT TEXT: replace the intro, closing and sign-off with your own words.
 */
return [
    // Name shown on the welcome page, dashboard and finale.
    'recipient' => env('GAME_RECIPIENT', 'Tamara'),

    // LOCAL DEVELOPMENT ONLY. When true, the private-link check is skipped so the
    // site opens directly. Never set this on the live server (it defaults to off).
    'open_access' => (bool) env('GAME_OPEN_ACCESS', false),

    // The birthday (shown on the welcome page and used for its countdown).
    'birthday' => env('GAME_BIRTHDAY', '2026-11-20'),

    // When the puzzles unlock, in Zambian time (Africa/Lusaka, UTC+2).
    'opens_at' => env('GAME_OPENS_AT', '2026-11-20 14:00'),

    // Full name, shown large on the welcome page (other pages use the first name above).
    'full_name' => env('GAME_FULL_NAME', 'Tamara Luchele Muzwindi'),

    // The age she turns (shown in the birthday banner once the countdown ends).
    'age' => (int) env('GAME_AGE', 27),

    'sign_off' => env('GAME_SIGN_OFF', 'With all my love'),

    // Welcome page: romantic introduction.
    'intro' => "Today is all about you, Tamara. You bring so much joy, laughter and light into my life, "
        .'and I wanted your birthday to feel as special as you are. So I hid a little adventure for you.',

    // Welcome page: how the challenge works.
    'how_it_works' => 'Solve eleven crosswords. Each one you finish unlocks a gift, and when you have '
        .'unwrapped all eleven, a very special surprise is waiting at the end.',

    // Finale: personal closing message.
    'closing' => 'Thank you for being you, for every smile and every bit of love you share. '
        .'I hope this birthday is full of happiness, and I cannot wait to celebrate you all year long.',
];
