<?php

declare(strict_types=1);

/*
 * Layer map of ADR 8 (docs/MODELAGEM_LARAVEL_ADR.md) and of the module-boundaries skill: keep the three in sync.
 * A module may import only the layers listed for its own layer in $mayImport; a new module gets an entry in $layers.
 */
$layers = [
    'Vevey\PanelAdmin' => 'interface',
    'Vevey\Permissions' => 'shared',
];

$mayImport = [
    'interface' => ['orchestrator', 'base', 'shared'],
    'orchestrator' => ['base', 'shared'],
    'base' => ['shared'],
    'shared' => [],
];

arch('app/ imports no module')
    ->expect('App')
    ->not->toUse('Vevey');

foreach ($layers as $namespace => $layer) {
    $forbidden = array_keys(array_filter(
        $layers,
        fn (string $otherLayer, string $otherNamespace): bool => $otherNamespace !== $namespace
            && ! in_array($otherLayer, $mayImport[$layer], true),
        ARRAY_FILTER_USE_BOTH,
    ));

    if ($forbidden === []) {
        continue;
    }

    arch("{$namespace} ({$layer}) imports only down the layer map")
        ->expect($namespace)
        ->not->toUse($forbidden);
}
