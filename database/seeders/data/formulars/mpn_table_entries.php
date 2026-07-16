<?php

/**
 * MPN Table — big_wells + small_wells → MPN value.
 *
 * Seeded with the clean combinations currently used in this project.
 * Extend this list (or use Bulk Import in the UI) for a full MPN grid.
 *
 * @return list<array{big_wells: string, small_wells: string, value: string}>
 */
return [
    ['big_wells' => '0', 'small_wells' => '2', 'value' => '1'],
    ['big_wells' => '0', 'small_wells' => '3', 'value' => '<2'],
    ['big_wells' => '1', 'small_wells' => '0', 'value' => '<1'],
    ['big_wells' => '1', 'small_wells' => '1', 'value' => '1'],
    ['big_wells' => '1', 'small_wells' => '2', 'value' => '<2'],
    ['big_wells' => '40', 'small_wells' => '40', 'value' => '<7'],
];
