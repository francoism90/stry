<?php

declare(strict_types=1);

use Foxws\Relatable\Models\Relatable;

return [

    /*
     * Override this to use your own model. Yours must extend
     * `Foxws\Relatable\Models\Relatable`, and may set its own `$table`
     * (e.g. to keep using an existing table).
     */
    'models' => [
        'relatable' => Relatable::class,
    ],

    /*
     * When enabled, the package's migration runs automatically. Disable
     * it when your application owns the table (e.g. an existing one).
     *
     * Disabled here: the existing `related` table is renamed to
     * `relatables` by an application migration instead.
     */
    'migrations' => false,

    /*
     * The score and boost given to a new relation when none is passed.
     * Its weight (used for ordering) is score × boost.
     */
    'defaults' => [
        'score' => 1.0,
        'boost' => 1.0,
    ],

    /*
     * When enabled, deleting a model also deletes its relations, in both
     * directions. Soft-deleted models keep them until force-deleted.
     */
    'delete_on_model_delete' => true,

];
