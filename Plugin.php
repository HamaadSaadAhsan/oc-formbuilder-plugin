<?php

namespace SaadAhsan\FormBuilder;

use Backend;
use System\Classes\PluginBase;

/**
 * FormBuilder Plugin - Dynamic Form Builder
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Form Builder',
            'description' => 'Dynamic form builder with customizable fields and submission management',
            'author'      => 'Saad Ahsan',
            'icon'        => 'icon-file-text-o'
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
            \SaadAhsan\FormBuilder\Components\FormBuilder::class => 'formBuilder',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'saadahsan.formbuilder.manage_forms' => [
                'tab'   => 'Form Builder',
                'label' => 'Manage forms'
            ],
            'saadahsan.formbuilder.manage_submissions' => [
                'tab'   => 'Form Builder',
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
            'formbuilder' => [
                'label'       => 'Forms',
                'url'         => Backend::url('saadahsan/formbuilder/forms'),
                'icon'        => 'icon-file-text-o',
                'permissions' => ['saadahsan.formbuilder.*'],
                'order'       => 500,

                'sideMenu' => [
                    'forms' => [
                        'label'       => 'Forms',
                        'icon'        => 'icon-list-alt',
                        'url'         => Backend::url('saadahsan/formbuilder/forms'),
                        'permissions' => ['saadahsan.formbuilder.manage_forms'],
                    ],
                    'submissions' => [
                        'label'       => 'Submissions',
                        'icon'        => 'icon-inbox',
                        'url'         => Backend::url('saadahsan/formbuilder/submissions'),
                        'permissions' => ['saadahsan.formbuilder.manage_submissions'],
                    ],
                ],
            ],
        ];
    }
}
