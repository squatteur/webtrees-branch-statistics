<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace Squatteur\Webtrees;

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Services\ChartService;
use Squatteur\Webtrees\BranchStatistics\Module;

// Register our namespace
$loader = new ClassLoader();
$loader->addPsr4(
    'Squatteur\\Webtrees\\BranchStatistics\\',
    __DIR__ . '/src'
);
$loader->register();

// Create and return instance of the module
return new Module(new ChartService());
