<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace Squatteur\Webtrees\BranchStatistics\Traits;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;

/**
 * Trait ModuleChartTrait.
 *
 * @author  Bestel Squatteur <bestel@squatteur.net>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/squatteur/webtrees-branch-statistics/
 */
trait ModuleChartTrait
{
    use \Fisharebest\Webtrees\Module\ModuleChartTrait;

    public function chartMenuClass(): string
    {
        return 'menu-chart-branchstatistics';
    }

    public function chartBoxMenu(Individual $individual): ?Menu
    {
        return $this->chartMenu($individual);
    }

    public function chartTitle(Individual $individual): string
    {
        return I18N::translate('Statistics of %s', $individual->fullName());
    }
}
