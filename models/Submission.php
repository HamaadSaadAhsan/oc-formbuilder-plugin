<?php

namespace SaadAhsan\FormBuilder\Models;

use Model;

/**
 * Submission Model
 */
class Submission extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'saadahsan_callbackform_submissions';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'form_id',
        'name',
        'email',
        'phone',
        'preferred_time',
        'message',
        'form_data',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'contacted_at',
    ];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'contacted_at' => 'datetime',
        'form_data'    => 'array',
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
     * @var array Attachments
     */
    public $attachMany = [
        'files' => \System\Models\File::class,
    ];

    /**
     * Status options for dropdown
     */
    public function getStatusOptions(): array
    {
        return [
            'new'       => 'New',
            'contacted' => 'Contacted',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Scope for new submissions
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for pending (not completed) submissions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['new', 'contacted']);
    }

    /**
     * Scope for specific form
     */
    public function scopeForForm($query, $formId)
    {
        return $query->where('form_id', $formId);
    }

    /**
     * Mark as contacted
     */
    public function markAsContacted(): void
    {
        $this->status = 'contacted';
        $this->contacted_at = now();
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Get a specific field value from form_data
     */
    public function getFieldValue(string $fieldName, $default = null)
    {
        if (!$this->form_data || !is_array($this->form_data)) {
            return $default;
        }

        return $this->form_data[$fieldName] ?? $default;
    }

    /**
     * Get display-friendly form data for backend
     */
    public function getFormDataDisplay(): array
    {
        if (!$this->form_data || !is_array($this->form_data)) {
            return [];
        }

        $display = [];
        $form = $this->form;

        if ($form) {
            foreach ($form->fields as $field) {
                if (isset($this->form_data[$field->name])) {
                    $value = $this->form_data[$field->name];

                    // Format arrays (checkboxes, etc.)
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $display[] = [
                        'label' => $field->label ?: $field->name,
                        'value' => $value,
                        'type'  => $field->field_type,
                    ];
                }
            }
        } else {
            // Fallback if form is deleted
            foreach ($this->form_data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $display[] = [
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'value' => $value,
                    'type'  => 'text',
                ];
            }
        }

        return $display;
    }

    /**
     * Get form name for list display
     */
    public function getFormNameAttribute(): string
    {
        return $this->form ? $this->form->name : 'Unknown Form';
    }
}
