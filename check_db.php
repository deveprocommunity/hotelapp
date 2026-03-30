<?php
$columns = \Illuminate\Support\Facades\DB::select('DESCRIBE pos_carts');
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
