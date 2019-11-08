<?php
function collatzSequence($an)
{
    $stoppingTime = 0;
    if ($an < 1) {
        return $stoppingTime;
    }
    while ($an > 1) {
        $an = ($an % 2 === 0) ? ($an / 2) : (3 * $an + 1);
        $stoppingTime++;
    }

    return $stoppingTime;
}

$an = 10;
$stoppingTime = collatzSequence($an);
echo "An = $an : stoppingTime = $stoppingTime\n";
$an = 23061912;
$stoppingTime = collatzSequence($an);
echo "An = $an : stoppingTime = $stoppingTime\n";
