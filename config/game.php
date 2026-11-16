<?php

/*
 * All personal wording lives here so it is easy to change.
 * DRAFT TEXT: replace the intro, closing and sign-off with your own words.
 */
return [
    // Name shown on the welcome page, dashboard and finale.
    'recipient' => env('GAME_RECIPIENT', 'Tamara'),

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
