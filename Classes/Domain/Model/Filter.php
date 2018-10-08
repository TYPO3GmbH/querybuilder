<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder;

/**
 * Class QueryParser.
 */
class Filter
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;


    /**
     * @var string
     */
    protected $input;


    /**
     * @var array
     */
    protected $values;


    /**
     * @var string
     */
    protected $labels;


    /**
     * @var string
     */
    protected $description;


    /**
     * @var string
     */
    protected $plugin;

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Set input
     *
     * @param string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Get input
     *
     * @return string
     */
    public function getInput() : string
    {
        return $this->input;
    }

    /**
     * Set values
     *
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues() : array
    {
        return $this->values;
    }

    /**
     * Set labels
     *
     * @param string $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * Get labels
     *
     * @return string
     */
    public function getLabels() : string
    {
        return $this->labels;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Set plugin
     *
     * @param string $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get plugin
     *
     * @return string
     */
    public function getPlugin() : string
    {
        return $this->plugin;
    }


//    validation ??
//    plugin_config ?? + icons ??
//
//
//
//        $filter->id = $filterField;
//        $filter->type = $this->determineFilterType($fieldConfig);
//        $filter->input = $this->determineFilterInput($fieldConfig);
//        $filter->values = $this->determineFilterValues($fieldConfig);
//        $filter->label = $fieldConfig['label'];
//        $filter->description = !empty($fieldConfig['description']) ? $fieldConfig['description'] : '';
//
//
//
//        if ($filter->type === 'date'
//            || $filter->type === 'datetime'
//            || $filter->type === 'time') {
//            $filter->validation = new stdClass();
//            $filter->plugin = 'datetimepicker';
//            $filter->plugin_config = new stdClass();
//            $filter->plugin_config->sideBySide = true;
//            $filter->plugin_config->icons = new stdClass();
//            $filter->plugin_config->icons->time = 'fa fa-clock-o';
//            $filter->plugin_config->icons->date = 'fa fa-calendar';
//            $filter->plugin_config->icons->up = 'fa fa-chevron-up';
//            $filter->plugin_config->icons->down = 'fa fa-chevron-down';
//            $filter->plugin_config->icons->previous = 'fa fa-chevron-left';
//            $filter->plugin_config->icons->next = 'fa fa-chevron-right';
//            $filter->plugin_config->icons->today = 'fa fa-calendar-o';
//            $filter->plugin_config->icons->clear = 'fa fa-trash';
//            switch ($filter->type) {
//                case 'datetime':
//                    $filter->plugin_config->format = self::FORMAT_DATETIME;
//                    break;
//                case 'date':
//                    $filter->plugin_config->format = self::FORMAT_DATE;
//                    break;
//                case 'time':
//                    $filter->plugin_config->format = self::FORMAT_TIME;
//                    break;
//                default:
//            }
//            $filter->validation->format = $filter->plugin_config->format;
//        }
//    }

}
