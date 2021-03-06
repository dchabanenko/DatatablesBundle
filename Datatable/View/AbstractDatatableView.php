<?php

/**
 * This file is part of the SgDatatablesBundle package.
 *
 * (c) stwe <https://github.com/stwe/DatatablesBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sg\DatatablesBundle\Datatable\View;

use Sg\DatatablesBundle\Datatable\Column\ColumnBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;

/**
 * Class AbstractDatatableView
 *
 * @package Sg\DatatablesBundle\Datatable\View
 */
abstract class AbstractDatatableView implements DatatableViewInterface
{
    /**
     * The service container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * A Features instance.
     *
     * @var Features
     */
    protected $features;

    /**
     * An Options instance.
     *
     * @var Options
     */
    protected $options;

    /**
     * A ColumnBuilder instance.
     *
     * @var ColumnBuilder
     */
    protected $columnBuilder;

    /**
     * An Ajax instance.
     *
     * @var Ajax
     */
    protected $ajax;

    /**
     * Data to use as the display data for the table.
     *
     * @var mixed
     */
    protected $data;

    /**
     * The Twig templates.
     *
     * @var array
     */
    protected $templates;

    //-------------------------------------------------
    // Ctor.
    //-------------------------------------------------

    /**
     * Ctor.
     *
     * @param ContainerInterface $container            The service container
     * @param array              $defaultLayoutOptions The default layout options
     */
    public function __construct(ContainerInterface $container, array $defaultLayoutOptions)
    {
        $this->container = $container;

        $this->features = new Features();
        $this->options = new Options();
        $this->columnBuilder = new ColumnBuilder();
        $this->ajax = new Ajax();

        $this->data = null;
        $this->templates = $defaultLayoutOptions["templates"];

        $this->buildDatatableView();
    }

    //-------------------------------------------------
    // DatatableViewInterface
    //-------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function render($type = "all")
    {
        $options = array();

        if (true === $this->features->getServerSide()) {
            if ("" === $this->ajax->getUrl()) {
                throw new Exception("render(): The ajax url parameter must be given.");
            }
        } else {
            if (null === $this->data) {
                throw new Exception("render(): Call setData() in your controller.");
            } else {
                $options["view_data"] = $this->data;
            }
        }

        $options["view_features"] = $this->features;
        $options["view_options"] = $this->options;
        $options["view_columns"] = $this->columnBuilder->getColumns();
        $options["view_ajax"] = $this->ajax;

        $options["view_multiselect"] = $this->columnBuilder->isMultiselect();
        $options["view_multiselect_column"] = $this->columnBuilder->getMultiselectColumn();

        $options["view_table_id"] = $this->getName();

        switch ($type) {
            case "html":
                return $this->container->get("templating")->render($this->templates["html"], $options);
                break;
            case "js":
                return $this->container->get("templating")->render($this->templates["js"], $options);
                break;
            default:
                return $this->container->get("templating")->render($this->templates["base"], $options);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        return null;
    }

    //-------------------------------------------------
    // Getters && Setters
    //-------------------------------------------------

    /**
     * Set Data.
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get Data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set templates.
     *
     * @param array $templates
     *
     * @return $this
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * Get templates.
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Truncate text
     * 
     * @param string $text
     * @param integer $chars
     * 
     * @return string
     */
    public function truncate($text, $chars = 25)
    {
        if (strlen($text) > $chars) {
            $text = substr($text . " ", 0, $chars);
            $text = substr($text, 0, strrpos($text, ' ')) . "...";
        }
        return $text;
    }

}
