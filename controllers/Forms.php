<?php

namespace SaadAhsan\CallbackForm\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use SaadAhsan\CallbackForm\Models\Form;

/**
 * Forms Backend Controller
 */
class Forms extends Controller
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
    public $requiredPermissions = ['saadahsan.callbackform.manage_forms'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('SaadAhsan.CallbackForm', 'callbackform', 'forms');
    }

    /**
     * Delete selected forms
     */
    public function onDelete(): array
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            Form::whereIn('id', $checkedIds)->delete();
            \Flash::success('Forms deleted successfully.');
        }

        return $this->listRefresh();
    }
}
