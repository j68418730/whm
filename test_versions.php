<?php
exec('ls /usr/bin/php* 2>/dev/null', $out);
foreach ($out as $p) {
    if (preg_match('/php(\d+\.\d+)$/', $p, $m)) echo $m[1] . "\n";
}
