<?php
declare(strict_types=1);

// Micro-bench: compare output-collection strategies on a workload that mimics
// inline emission (many small concats, recursion with nested spans).
//
// Run: php -d opcache.enable_cli=1 -d opcache.jit=1255 -d opcache.jit_buffer_size=64M tests/Bench/buffer_micro.php

ini_set('memory_limit', '512M');

const ITERS = 50_000;
const RECURSION_FANOUT = 8;   // children per node
const RECURSION_DEPTH = 3;    // tree depth → ~512 leaves
const CHUNK = 'hello world ';

// --- 1. by-ref string, .= (current pattern) -------------------------------
function byref_emit(int $depth, string &$out): void {
    $out .= '<span>';
    if ($depth === 0) {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            $out .= CHUNK;
        }
    } else {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            byref_emit($depth - 1, $out);
        }
    }
    $out .= '</span>';
}
function bench_byref(): string {
    $out = '';
    byref_emit(RECURSION_DEPTH, $out);
    return $out;
}

// --- 2. return string, caller concatenates --------------------------------
function returning_emit(int $depth): string {
    $s = '<span>';
    if ($depth === 0) {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            $s .= CHUNK;
        }
    } else {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            $s .= returning_emit($depth - 1);
        }
    }
    return $s . '</span>';
}
function bench_returning(): string {
    return returning_emit(RECURSION_DEPTH);
}

// --- 3. by-ref array of chunks + implode ----------------------------------
function array_emit(int $depth, array &$out): void {
    $out[] = '<span>';
    if ($depth === 0) {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            $out[] = CHUNK;
        }
    } else {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            array_emit($depth - 1, $out);
        }
    }
    $out[] = '</span>';
}
function bench_array(): string {
    $out = [];
    array_emit(RECURSION_DEPTH, $out);
    return implode('', $out);
}

// --- 4. ob_start / ob_get_clean -------------------------------------------
function ob_emit(int $depth): void {
    echo '<span>';
    if ($depth === 0) {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            echo CHUNK;
        }
    } else {
        for ($i = 0; $i < RECURSION_FANOUT; $i++) {
            ob_emit($depth - 1);
        }
    }
    echo '</span>';
}
function bench_ob(): string {
    ob_start();
    ob_emit(RECURSION_DEPTH);
    return ob_get_clean();
}

// --- driver ----------------------------------------------------------------
function run(string $name, callable $fn, int $iters): array {
    // warmup
    for ($i = 0; $i < 200; $i++) $fn();
    $best = PHP_FLOAT_MAX;
    $worst = 0.0;
    $sum = 0.0;
    $runs = 3;
    $sample = null;
    for ($r = 0; $r < $runs; $r++) {
        $t0 = hrtime(true);
        for ($i = 0; $i < $iters; $i++) {
            $sample = $fn();
        }
        $dt = (hrtime(true) - $t0) / 1e6; // ms
        $best = min($best, $dt);
        $worst = max($worst, $dt);
        $sum += $dt;
    }
    return ['name' => $name, 'best_ms' => $best, 'mean_ms' => $sum / $runs, 'worst_ms' => $worst, 'len' => strlen($sample)];
}

// sanity: outputs equal?
$ref = bench_byref();
foreach (['bench_returning', 'bench_array', 'bench_ob'] as $fn) {
    if ($fn() !== $ref) {
        fwrite(STDERR, "MISMATCH: $fn\n");
        exit(1);
    }
}
echo "Output length: " . strlen($ref) . " bytes\n";
echo "Iterations:    " . ITERS . "\n";
echo "Tree:          fanout=" . RECURSION_FANOUT . " depth=" . RECURSION_DEPTH . "\n\n";

$results = [
    run('by-ref string (.=)', 'bench_byref', ITERS),
    run('returning string',   'bench_returning', ITERS),
    run('by-ref array + implode', 'bench_array', ITERS),
    run('ob_start/ob_get_clean',  'bench_ob', ITERS),
];

printf("%-28s %10s %10s %10s\n", 'strategy', 'best(ms)', 'mean(ms)', 'worst(ms)');
foreach ($results as $r) {
    printf("%-28s %10.2f %10.2f %10.2f\n", $r['name'], $r['best_ms'], $r['mean_ms'], $r['worst_ms']);
}
$baseline = $results[0]['best_ms'];
echo "\nrelative to by-ref string (best):\n";
foreach ($results as $r) {
    printf("  %-28s %.2fx\n", $r['name'], $r['best_ms'] / $baseline);
}
