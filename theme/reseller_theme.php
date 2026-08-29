<?php
// Reseller portal theme — self-contained palette, never inherits the admin theme's
// variables (an admin light theme with dark text used to leak into the reseller panel
// via getThemeCss('admin'), making table rows unreadable). Light text on dark bg always.
return [
    'name' => 'Reseller',
    'colors' => [
        'primary' => '#008cff',
        'secondary' => '#3bb8ff',
        'accent' => '#00bfff',
        'bg' => '#02050e',
        'sidebar_bg' => '#0b1728',
        'card_bg' => 'rgba(8,16,28,.6)',
        'text' => '#e8edf5',
        'text_muted' => '#94a3b8',
        'border' => 'rgba(0,191,255,.1)',
        'success' => '#4ade80',
        'warning' => '#facc15',
        'danger' => '#f87171',
    ],
    'fonts' => [
        'body' => "'Inter', sans-serif",
        'heading' => "'Inter', sans-serif",
    ],
];