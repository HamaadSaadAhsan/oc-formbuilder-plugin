<?php

namespace SaadAhsan\CallbackForm\Models;

use Model;

/**
 * Form Model - Defines a dynamic form
 */
class Form extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'saadahsan_callbackform_forms';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'success_message',
        'error_message',
        'submit_button_text',
        'notify_email',
        'custom_css',
        'custom_js',
        'wrapper_class',
        'form_class',
        'is_active',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|string|max:255',
    ];

    /**
     * @var array Attribute casts
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @var array Jsonable attributes - used by repeater
     */
    protected $jsonable = ['fields_config'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'fields' => [
            FormField::class,
            'key' => 'form_id',
            'order' => 'sort_order asc',
        ],
        'submissions' => [
            Submission::class,
            'key' => 'form_id',
            'order' => 'created_at desc',
        ],
    ];

    /**
     * Generate unique code from name
     */
    public function beforeValidate(): void
    {
        if (empty($this->code)) {
            $this->code = str_slug($this->name);
        }
    }

    /**
     * Sync fields_config to FormField table after save
     */
    public function afterSave(): void
    {
        $this->syncFieldsFromConfig();
    }

    /**
     * Sync the jsonable fields_config to the FormField relation table
     */
    protected function syncFieldsFromConfig(): void
    {
        // Delete existing fields
        $this->fields()->delete();

        // Get fields from jsonable config
        $fieldsConfig = $this->fields_config;

        if (!is_array($fieldsConfig)) {
            return;
        }

        // Create new fields
        foreach ($fieldsConfig as $index => $fieldData) {
            $field = new FormField();
            $field->form_id = $this->id;
            $field->field_type = $fieldData['field_type'] ?? 'text';
            $field->name = $fieldData['name'] ?? 'field_' . $index;
            $field->label = $fieldData['label'] ?? null;
            $field->placeholder = $fieldData['placeholder'] ?? null;
            $field->default_value = $fieldData['default_value'] ?? null;
            $field->options = $fieldData['options'] ?? null;
            $field->validation_rules = $fieldData['validation_rules'] ?? null;
            $field->error_message = $fieldData['error_message'] ?? null;
            $field->field_class = $fieldData['field_class'] ?? null;
            $field->field_style = $fieldData['field_style'] ?? null;
            $field->wrapper_class = $fieldData['wrapper_class'] ?? null;
            $field->wrapper_style = $fieldData['wrapper_style'] ?? null;
            $field->custom_attributes = $fieldData['custom_attributes'] ?? null;
            $field->html_content = $fieldData['html_content'] ?? null;
            $field->sort_order = $index;
            $field->is_required = !empty($fieldData['is_required']);
            $field->is_active = $fieldData['is_active'] ?? true;
            $field->save();
        }
    }

    /**
     * Get active fields ordered by sort_order
     */
    public function getActiveFields()
    {
        return $this->fields()->where('is_active', true)->orderBy('sort_order')->get();
    }

    /**
     * Get validation rules for all fields
     */
    public function getFieldValidationRules(): array
    {
        $rules = [];
        foreach ($this->getActiveFields() as $field) {
            if ($field->validation_rules) {
                $rules[$field->name] = $field->validation_rules;
            } elseif ($field->is_required) {
                $rules[$field->name] = 'required';
            }
        }
        return $rules;
    }

    /**
     * Get custom validation messages for all fields
     */
    public function getFieldValidationMessages(): array
    {
        $messages = [];
        foreach ($this->getActiveFields() as $field) {
            if ($field->error_message) {
                $messages[$field->name . '.required'] = $field->error_message;
            }
        }
        return $messages;
    }

    /**
     * Find form by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Get fields count for list display
     */
    public function getFieldsCountAttribute(): int
    {
        return $this->fields()->count();
    }

    /**
     * Get submissions count for list display
     */
    public function getSubmissionsCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Scope for active forms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
