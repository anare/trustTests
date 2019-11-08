<?php

require __DIR__ . '/StringManipulation.php';

echo ('Test: ') . (assert('!dlroW olleH' === reverse($str)) ? 'pass' : 'failure');
