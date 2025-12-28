<?php

namespace SaadAhsan\CallbackForm\Components;

use Cms\Classes\ComponentBase;
use SaadAhsan\CallbackForm\Models\Form;
use SaadAhsan\CallbackForm\Models\Submission;
use October\Rain\Exception\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * CallbackForm Component - Renders dynamic forms
 */
class CallbackForm extends ComponentBase
{
    /**
     * @var Form The form model
     */
    public $form;

    /**
     * Component details
     */
    public function componentDetails(): array
    {
        return [
            'name'        => 'Callback Form',
            'description' => 'Displays a dynamic form based on backend configuration'
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties(): array
    {
        return [
            'formCode' => [
                'title'       => 'Form',
                'description' => 'Select the form to display',
                'type'        => 'dropdown',
                'required'    => true,
            ],
            'renderMode' => [
                'title'       => 'Render Mode',
                'description' => 'How to render the form',
                'type'        => 'dropdown',
                'options'     => [
                    'inline' => 'Inline (embedded in page)',
                    'modal'  => 'Modal (popup)',
                ],
                'default'     => 'modal',
            ],
            'modalId' => [
                'title'       => 'Modal ID',
                'description' => 'HTML ID for the modal (when using modal mode)',
                'type'        => 'string',
                'default'     => 'callbackModal',
            ],
        ];
    }

    /**
     * Get form options for dropdown
     */
    public function getFormCodeOptions(): array
    {
        $forms = Form::active()->orderBy('name')->get();
        $options = [];

        foreach ($forms as $form) {
            $options[$form->code] = $form->name;
        }

        return $options;
    }

    /**
     * Component initialization
     */
    public function onRun(): void
    {
        $this->form = $this->loadForm();

        if ($this->form) {
            $this->page['form'] = $this->form;
            $this->page['formFields'] = $this->form->getActiveFields();
            $this->page['renderMode'] = $this->property('renderMode');
            $this->page['modalId'] = $this->property('modalId');

            // Inject custom CSS if defined
            if ($this->form->custom_css) {
                $this->addCss(null, ['data-form-css' => $this->form->code]);
            }
        }
    }

    /**
     * Load the form model
     */
    protected function loadForm(): ?Form
    {
        $code = $this->property('formCode');

        if (!$code) {
            return null;
        }

        return Form::findByCode($code);
    }

    /**
     * Handle form submission via AJAX
     */
    public function onFormSubmit(): array
    {
        $formCode = post('_form_code');
        $form = Form::findByCode($formCode);

        if (!$form) {
            throw new ValidationException(['_form' => 'Form not found.']);
        }

        $data = post();

        // Build validation rules from form fields
        $rules = $form->getFieldValidationRules();
        $messages = $form->getFieldValidationMessages();

        // Validate
        $validation = \Validator::make($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        // Prepare form data for storage
        $formData = [];
        foreach ($form->getActiveFields() as $field) {
            if ($field->field_type !== 'html') {
                $formData[$field->name] = $data[$field->name] ?? null;
            }
        }

        // Create the submission
        $submission = new Submission();
        $submission->form_id = $form->id;
        $submission->form_data = $formData;

        // Also store in legacy fields if they exist
        $submission->name = $formData['name'] ?? null;
        $submission->email = $formData['email'] ?? null;
        $submission->phone = $formData['phone'] ?? null;
        $submission->message = $formData['message'] ?? null;

        $submission->ip_address = request()->ip();
        $submission->user_agent = request()->userAgent();
        $submission->save();

        // Handle file uploads
        if (request()->hasFile('files')) {
            foreach (request()->file('files') as $file) {
                $submission->files()->create(['data' => $file]);
            }
        }

        // Send notification email if configured
        if ($form->notify_email) {
            $this->sendNotificationEmail($form, $submission);
        }

        return [
            'success' => true,
            'message' => $form->success_message,
        ];
    }

    /**
     * Send notification email to admin
     */
    protected function sendNotificationEmail(Form $form, Submission $submission): void
    {
        try {
            $content = $this->buildEmailContent($form, $submission);

            Mail::raw($content, function ($message) use ($form, $submission) {
                $message->to($form->notify_email);
                $message->subject('New Form Submission: ' . $form->name);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send form notification email: ' . $e->getMessage());
        }
    }

    /**
     * Build email content from submission
     */
    protected function buildEmailContent(Form $form, Submission $submission): string
    {
        $content = "New Form Submission\n";
        $content .= "Form: {$form->name}\n";
        $content .= "====================\n\n";

        foreach ($submission->getFormDataDisplay() as $field) {
            $content .= "{$field['label']}: {$field['value']}\n";
        }

        $content .= "\n--\n";
        $content .= "Submitted at: " . $submission->created_at->format('Y-m-d H:i:s') . "\n";
        $content .= "IP Address: {$submission->ip_address}\n";

        return $content;
    }

    /**
     * Get field input HTML based on field type
     */
    public function renderField($field): string
    {
        $partial = 'field-' . $field->field_type;

        // Check if a custom partial exists, otherwise use default
        $partialPath = $this->getPartialPath($partial);

        if (!file_exists($partialPath)) {
            $partial = 'field-default';
        }

        return $this->renderPartial('@' . $partial, ['field' => $field]);
    }

    /**
     * Get the path to a partial
     */
    protected function getPartialPath(string $partial): string
    {
        return plugins_path('saadahsan/callbackform/components/callbackform/' . $partial . '.htm');
    }
}
