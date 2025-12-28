<?php

namespace SaadAhsan\CallbackForm;

use Backend;
use System\Classes\PluginBase;

/**
 * CallbackForm Plugin - Dynamic Form Builder
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Callback Form Builder',
            'description' => 'Dynamic form builder with customizable fields and submission management',
            'author'      => 'Saad Ahsan',
            'icon'        => 'icon-wpforms'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \SaadAhsan\CallbackForm\Components\CallbackForm::class => 'callBackForm',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'saadahsan.callbackform.manage_forms' => [
                'tab'   => 'Callback Forms',
                'label' => 'Manage forms'
            ],
            'saadahsan.callbackform.manage_submissions' => [
                'tab'   => 'Callback Forms',
                'label' => 'Manage submissions'
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'callbackform' => [
                'label'       => 'Forms',
                'url'         => Backend::url('saadahsan/callbackform/forms'),
                'icon'        => 'icon-file-text-o',
                'permissions' => ['saadahsan.callbackform.*'],
                'order'       => 500,

                'sideMenu' => [
                    'forms' => [
                        'label'       => 'Forms',
                        'icon'        => 'icon-list-alt',
                        'url'         => Backend::url('saadahsan/callbackform/forms'),
                        'permissions' => ['saadahsan.callbackform.manage_forms'],
                    ],
                    'submissions' => [
                        'label'       => 'Submissions',
                        'icon'        => 'icon-inbox',
                        'url'         => Backend::url('saadahsan/callbackform/submissions'),
                        'permissions' => ['saadahsan.callbackform.manage_submissions'],
                    ],
                ],
            ],
        ];
    }
}
