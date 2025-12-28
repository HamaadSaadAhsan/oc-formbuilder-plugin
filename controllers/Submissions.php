<?php

namespace SaadAhsan\FormBuilder\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use SaadAhsan\FormBuilder\Models\Form;
use SaadAhsan\FormBuilder\Models\Submission;

/**
 * Submissions Backend Controller
 */
class Submissions extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    /**
     * @var string Configuration file for the form behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string Configuration file for the list behavior.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array Required permissions
     */
    public $requiredPermissions = ['saadahsan.formbuilder.manage_submissions'];

    /**
     * @var Form|null Current form filter
     */
    public $filterForm = null;

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('SaadAhsan.FormBuilder', 'callbackform', 'submissions');

        // Check for form filter
        $formId = get('form');
        if ($formId) {
            $this->filterForm = Form::find($formId);
        }

        $this->vars['filterForm'] = $this->filterForm;
        $this->vars['forms'] = Form::orderBy('name')->get();
    }

    /**
     * Index page - list submissions
     */
    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    /**
     * Extend list query to filter by form
     */
    public function listExtendQuery($query)
    {
        if ($this->filterForm) {
            $query->where('form_id', $this->filterForm->id);
        }
    }

    /**
     * Mark submission as contacted
     */
    public function onMarkContacted(): array
    {
        $id = post('id');
        $submission = Submission::findOrFail($id);
        $submission->markAsContacted();

        \Flash::success('Submission marked as contacted.');

        return $this->listRefresh();
    }

    /**
     * Mark submission as completed
     */
    public function onMarkCompleted(): array
    {
        $id = post('id');
        $submission = Submission::findOrFail($id);
        $submission->markAsCompleted();

        \Flash::success('Submission marked as completed.');

        return $this->listRefresh();
    }

    /**
     * Bulk delete submissions
     */
    public function onDelete(): array
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            Submission::whereIn('id', $checkedIds)->delete();
            \Flash::success('Submissions deleted successfully.');
        }

        return $this->listRefresh();
    }

    /**
     * Filter by form
     */
    public function onFilterForm(): array
    {
        $formId = post('form_id');

        if ($formId) {
            return \Redirect::to(Backend::url('saadahsan/formbuilder/submissions') . '?form=' . $formId);
        }

        return \Redirect::to(Backend::url('saadahsan/formbuilder/submissions'));
    }
}
