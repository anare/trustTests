<?php

require __DIR__ . '/MathProblem.php';

echo ('Test: ') . (assert(collatzSequence(10) === 6) ? 'pass' : 'failure');
