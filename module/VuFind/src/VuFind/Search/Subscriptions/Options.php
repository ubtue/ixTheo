<?php
namespace VuFind\Search\Subscriptions;

class Options extends \VuFind\Search\Base\Options
{
    /**
     * Constructor
     *
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct(\VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($configLoader);

        $this->defaultSort = 'title';
        $this->sortOptions = [
            'title' => 'sort_title', 'author' => 'sort_author',
            'year DESC' => 'sort_year', 'year' => 'sort_year asc'
        ];
    }

    /**
     * Return the route name for the search results action.
     *
     * @return string
     */
    public function getSearchAction()
    {
        return 'myresearch-subscription';
    }

    /**
     * Load all recommendation settings from the relevant ini file.  Returns an
     * associative array where the key is the location of the recommendations (top
     * or side) and the value is the settings found in the file (which may be either
     * a single string or an array of strings).
     *
     * @param string $handler Name of handler for which to load specific settings.
     *
     * @return array associative: location (top/side/etc.) => search settings
     */
    public function getRecommendationSettings($handler = null)
    {
        return [];
    }
}
