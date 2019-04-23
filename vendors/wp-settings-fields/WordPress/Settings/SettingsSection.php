<?php

namespace WordPress\Settings;

class SettingsSection
{
    protected $name            = '';
    protected $title           = '';
    protected $topHtml         = '';
    /** @var callable */
    protected $topRenderer     = null;
    /** @var callable */
    protected $defaultRenderer = null;
    /** @var SettingsField[] */
    protected $fields          = [];

    /**
     * @param string $name
     * @param string $title
     */
    public function __construct($name, $title)
    {
        $this->name = $name;
        $this->title = $title;

        $this->defaultRenderer = function () { echo $this->topHtml; };
        $this->setTopRenderer($this->defaultRenderer);
    }

    /**
     * @param \WordPress\Settings\SettingsField $field
     * @return \WordPress\Settings\SettingsSection $this
     */
    public function addField(SettingsField $field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * @param string $page
     */
    public function register($page)
    {
        add_action('admin_init', function () use ($page) { $this->registerSection($page); });

        foreach ($this->fields as $field) {
            $field->register($page, $this->name);
        }
    }

    /**
     * @param string $page
     */
    protected function registerSection($page)
    {
        add_settings_section($this->name, $this->title, $this->topRenderer, $page);
    }

    public function renderOnTop($htmlContent)
    {
        $this->topHtml = $htmlContent;
        $this->setTopRenderer($this->defaultRenderer);
    }

    /**
     * @param callable $renderTopCallback
     */
    public function setTopRenderer(callable $renderTopCallback)
    {
        $this->topRenderer = $renderTopCallback;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return \WordPress\Settings\SelectField[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
