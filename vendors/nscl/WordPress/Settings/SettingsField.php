<?php

namespace NSCL\WordPress\Settings;

abstract class SettingsField
{
    protected $name        = '';
    protected $title       = '';
    protected $args        = [];
    protected $type        = '';
    protected $description = '';
    /** @var mixed */
    protected $default     = '';

    /**
     * @param string $name
     * @param string $title
     * @param array $args Optional. All descriptions and labels in the array
     *                    must be properly escaped <b>before</b> passing them to
     *                    the instance of the settings field.
     */
    public function __construct($name, $title, $args = [])
    {
        $this->name  = $name;
        $this->title = $title;
        $this->args  = array_merge($this->getDefaultArgs(), $args);

        $this->type        = $this->args['type'];
        $this->description = $this->args['description'];
        $this->default     = $this->args['default'];
    }

    protected function getDefaultArgs()
    {
        return [
            'type'              => 'string', // boolean|number|integer|string
            'description'       => '',
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeValue'],
            'show_in_rest'      => false,
            'class'             => ''
        ];
    }

    /**
     * @param string $page
     * @param string $section
     */
    public function register($page, $section)
    {
        add_action('admin_init', function () use ($page, $section) { $this->registerField($page, $section); });
    }

    protected function registerField($page, $section)
    {
        add_settings_field($this->name, $this->title, [$this, 'display'], $page, $section);
        register_setting($page, $this->name, $this->args);
    }

    public function display()
    {
        echo '<fieldset>';

        $this->displayBefore();
        $this->displayInput();
        $this->displayAfter();

        echo '</fieldset>';
    }

    protected function displayBefore()
    {
        echo '<legend class="screen-reader-text">';
            echo '<span>', esc_html($this->title), '</span>';
        echo '</legend>';
    }

    abstract protected function displayInput();

    protected function displayAfter()
    {
        if (!empty($this->description)) {
            echo '<p class="description">';
                // The description must be properly escaped __before__ passing
                // it to the instance of the settings field
                echo $this->description;
            echo '</p>';
        }
    }

    public function sanitizeType($value)
    {
        switch ($this->type) {
            case 'boolean':
                return boolval($value); break;
            case 'number':
                return floatval($value); break;
            case 'integer':
                return intval($value); break;
            case 'string':
            default:
                return "{$value}"; break;
        }
    }

    public function sanitizeValue($value)
    {
        if ($value === '') {
            return $value;
        }

        switch ($this->type) {
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'number':
                $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($value === false) {
                    $value = ''; // MultivalueField will filter all empty strings (we must
                                 // distinguish the sanitization error from the default value)
                }
                break;

            case 'integer':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                if ($value === false) {
                    $value = '';
                }
                break;

            case 'string':
            default:
                $value = sanitize_text_field($value);
                break;
        }

        return $value;
    }

    /**
     * @return mixed bool|int|float|string, also array in MultivalueField.
     */
    public function getValue()
    {
        $value = get_option($this->name, null);

        if (is_null($value) || ($value === '' && $this->type != 'string')) {
            $value = $this->default;
        } else {
            $value = $this->sanitizeType($value);
        }

        return $value;
    }

    public function getDefaultValue()
    {
        return $this->default;
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
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
}
