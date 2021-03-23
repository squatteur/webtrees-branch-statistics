<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace Squatteur\Webtrees\BranchStatistics;

use Aura\Router\RouterContainer;
use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Services\ChartService;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\View;
use Squatteur\Webtrees\BranchStatistics\Traits\ModuleChartTrait;
use Squatteur\Webtrees\BranchStatistics\Traits\ModuleCustomTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function app;
use function is_string;
use function max;
use function min;
use function route;

/**
 * Fan chart module class.
 *
 * @author  Bestel Squatteur <bestel@squatteur.net>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/squatteur/webtrees-branch-statistics/
 */
class Module extends AbstractModule implements ModuleCustomInterface, ModuleChartInterface, RequestHandlerInterface
{
    use ModuleCustomTrait;
    use ModuleChartTrait;

    protected const ROUTE_URL  = '/tree/{tree}/webtrees-branch-statistics/{xref}-{generations}';

    /** @var ChartService */
    private $chart_service;

    /**
     * @var string
     */
    public const CUSTOM_AUTHOR = 'Bestel Squatteur';

    /**
     * @var string
     */
    public const CUSTOM_VERSION = '2.0';

    /**
     * @var string
     */
    public const CUSTOM_WEBSITE = 'https://github.com/squatteur/webtrees-branch-statistics';


    public const DEFAULT_GENERATIONS = 6;

    protected const DEFAULT_PARAMETERS  = [
        'generations' => self::DEFAULT_GENERATIONS,
    ];

    // Limits
    protected const MINIMUM_GENERATIONS = 2;
    protected const MAXIMUM_GENERATIONS = 20;


    public function __construct(ChartService $chart_service)
    {
        $this->chart_service = $chart_service;
    }

    public function boot(): void
    {
        $router_container = app(RouterContainer::class);

        $router_container->getMap()
            ->get(static::class, static::ROUTE_URL, $this)
            ->allows(RequestMethodInterface::METHOD_POST)
            ->tokens([
                'generations' => '\d+',
            ]);
            View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    /**
     * How should this module be identified in the control panel, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        return I18N::translate('Branch statistics');
    }

    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        return I18N::translate('Statistics of an individual’s ancestors.');
    }

    /**
     * The URL for a page showing chart options.
     *
     * @param Individual $individual
     * @param mixed[]    $parameters
     *
     * @return string
     */
    public function chartUrl(Individual $individual, array $parameters = []): string
    {
        return route(static::class, [
                'tree' => $individual->tree()->name(),
                'xref' => $individual->xref(),
            ] + $parameters + self::DEFAULT_PARAMETERS);
    }

    /**
     * Where does this module store its resources
     *
     * @return string
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/../resources/';
    }

    /**
     * Handles a request and produces a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree       = $request->getAttribute('tree');
        
        $xref       = $request->getAttribute('xref');

        $individual = Registry::individualFactory()->make($xref, $tree);
        Auth::checkIndividualAccess($individual, false, true);

        $user       = $request->getAttribute('user');
        $generations = (int) $request->getAttribute('generations');

        $ajax        = $request->getQueryParams()['ajax'] ?? '';

        // Convert POST requests into GET requests for pretty URLs.
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            $params = (array) $request->getParsedBody();

            return redirect(route(static::class, [
                'tree'        => $tree->name(),
                'xref'        => $params['xref'],
                'generations' => $params['generations'],
            ]));
        }

    //    Auth::checkComponentAccess($this, 'chart', $tree, $user);

        $generations = min($generations, self::MAXIMUM_GENERATIONS);
        $generations = max($generations, self::MINIMUM_GENERATIONS);


        $ajax_url = $this->chartUrl($individual, [
            //'generations' => $generations,
            'ajax'        => true,
        ]);

        return $this->viewResponse($this->name() . '::chart', [
            'ajax_url'              => $ajax_url,
            'default_generations'   => self::DEFAULT_GENERATIONS,
            'moduleName'            => $this->name(),
            'generations'           => $generations,
            'individual'            => $individual,
            'maximum_generations'   => self::MAXIMUM_GENERATIONS,
            'minimum_generations'   => self::MINIMUM_GENERATIONS,
            'module'                => $this->name(),
            'title'                 => $this->getPageTitle($individual),
            'tree'                  => $tree,
        ]);


    }

    /**
     * Returns the page title.
     *
     * @param Individual $individual The individual used in the current chart
     *
     * @return string
     */
    private function getPageTitle(Individual $individual): string
    {
        $title = I18N::translate('Branch statistics');

        if ($individual && $individual->canShowName()) {
            $title = I18N::translate('Branch statistics of %s', $individual->fullName());
        }

        return $title;
    }

}
