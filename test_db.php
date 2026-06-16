<?php
$f1 = file('create-gatepass.php');
$f2 = file('create-gatepass copy.php');

$out = "";
$max = max(count($f1), count($f2));
for ($i = 0; $i < $max; $i++) {
    $l1 = isset($f1[$i]) ? rtrim($f1[$i]) : null;
    $l2 = isset($f2[$i]) ? rtrim($f2[$i]) : null;
    if ($l1 !== $l2) {
        $out .= sprintf("Line %d:\n  create-gatepass.php:      %s\n  create-gatepass copy.php: %s\n\n", 
            $i + 1, 
            $l1 !== null ? json_encode($l1) : '[EOF]', 
            $l2 !== null ? json_encode($l2) : '[EOF]'
        );
    }
}
file_put_contents('diff.txt', $out);
echo "Written to diff.txt\n";
?>



