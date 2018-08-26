<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    //~ If game has loop or not: true for infinite loop, false for no loop.
    'gameLoop'  => false,

    //~ Copyright name for header files
    'copyright' => 'Romain Cottard',

    //~ Distribution file to sync on codingame.com
    'dist'      => '/dist/codingame.php',

    //~ List of source directories to scan & compile
    'src'       => [
        '/src',
        '/vendor/velkuns/codingame-core/src'
    ],

    //~ List of source directories to exclude from compilation
    'exclude'   => [
        '/vendor/velkuns/codingame-core/src/Compiler'
    ],
];
