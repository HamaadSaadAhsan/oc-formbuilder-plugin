<?php

namespace SaadAhsan\FormBuilder\Models;

use Model;

/**
 * FormField Model - Defines a field within a form
 */
class FormField extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'saadahsan_callbackform_form_fields';

    /**
     * @var string The column name for the sort order
     */
    const SORT_ORDER = 'sort_order';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'form_id',
        'field_type',
        'name',
        'label',
        'placeholder',
        'default_value',
        'options',
        'validation_rules',
        'error_message',
        'field_class',
        'field_style',
        'wrapper_class',
        'wrapper_style',
        'custom_attributes',
        'html_content',
        'sort_order',
        'is_required',
        'is_active',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'field_type' => 'required|string',
        'name'       => 'required|string|max:255',
    ];

    /**
     * @var array Attribute casts
     */
    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'options'     => 'array',
        'custom_attributes' => 'array',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'form' => [
            Form::class,
            'key' => 'form_id',
        ],
    ];

    /**
     * Available field types
     */
    public static function getFieldTypes(): array
    {
        return [
            'text'     => 'Text Input',
            'email'    => 'Email',
            'phone'    => 'Phone Number',
            'number'   => 'Number',
            'url'      => 'URL',
            'textarea' => 'Textarea',
            'select'   => 'Dropdown Select',
            'checkbox' => 'Checkbox',
            'radio'    => 'Radio Buttons',
            'date'     => 'Date Picker',
            'file'     => 'File Upload',
            'hidden'   => 'Hidden Field',
            'html'     => 'Custom HTML',
        ];
    }

    /**
     * Get field type options for dropdown
     */
    public function getFieldTypeOptions(): array
    {
        return static::getFieldTypes();
    }

    /**
     * Parse options for select/radio/checkbox fields
     */
    public function getParsedOptions(): array
    {
        if (!$this->options) {
            return [];
        }

        if (is_array($this->options)) {
            return $this->options;
        }

        return [];
    }

    /**
     * Get custom attributes as HTML string
     */
    public function getAttributesHtml(): string
    {
        if (!$this->custom_attributes || !is_array($this->custom_attributes)) {
            return '';
        }

        $html = [];
        foreach ($this->custom_attributes as $attr) {
            if (!empty($attr['attribute']) && isset($attr['value'])) {
                $html[] = e($attr['attribute']) . '="' . e($attr['value']) . '"';
            }
        }

        return implode(' ', $html);
    }

    /**
     * Check if this field type needs options
     */
    public function needsOptions(): bool
    {
        return in_array($this->field_type, ['select', 'radio', 'checkbox']);
    }

    /**
     * Generate the input name with proper formatting
     */
    public function getInputName(): string
    {
        return $this->name;
    }

    /**
     * Generate unique input ID
     */
    public function getInputId(): string
    {
        return 'field_' . $this->id . '_' . $this->name;
    }
}
