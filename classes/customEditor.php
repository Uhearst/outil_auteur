<?php

namespace format_udehauthoring;

use editor_tiny\editor;
use editor_tiny\manager;


class customEditor extends editor
{

    /** @var manager The Tiny Manager instace */
    public $manager;

    /** @var \stdClass|null The default configuration to use if none is provided */
    protected static $defaultconfiguration = null;

    /**
     * Set the default configuration for the editor.
     *
     * @param manager $manager The editor manager
     */
    public static function set_custom_default_configuration(manager $manager) {
        global $PAGE;

        if (self::is_default_configuration_set()) {
            return;
        }

        $context = $PAGE->context;

        $config = (object) [
            'css' => $PAGE->theme->editor_css_url()->out(false),
            'context' => $context->id,
            'plugins' => $manager->get_plugin_configuration($context, [], []),
        ];

        $config = json_encode($config);

        self::$defaultconfiguration = $config;

        return $config;
    }

    public function use_custom_editor($elementid, array $options = null, $fpoptions = null) {
        global $PAGE;

        $defaultConfig = self::set_custom_default_configuration($this->manager);

        if ($fpoptions === null) {
            $fpoptions = [];
        }

        $context = $PAGE->context;

        if (isset($options['context']) && ($options['context'] instanceof \context)) {
            $context = $options['context'];
        }

        $siteconfig = get_config('editor_tiny');
        $config = (object) [
            'css' => $PAGE->theme->editor_css_url()->out(false),
            'context' => $context->id,
            'filepicker' => $fpoptions,
            'currentLanguage' => current_language(),
            'branding' => property_exists($siteconfig, 'branding') ? !empty($siteconfig->branding) : true,
            'language' => [
                'currentlang' => current_language(),
                'installed' => get_string_manager()->get_list_of_translations(true),
                'available' => get_string_manager()->get_list_of_languages()
            ],
            'placeholderSelectors' => [],
            'plugins' => $this->manager->get_plugin_configuration($context, $options, $fpoptions, $this),
            'nestedmenu' => true,
        ];

        if (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING) {
            $config->placeholderSelectors = ['.behat-tinymce-placeholder'];
        }

        foreach ($fpoptions as $fp) {
            if (!empty($fp->itemid)) {
                $config->draftitemid = $fp->itemid;
                break;
            }
        }

        $configoptions = json_encode(convert_to_array($config));

        return ['elementId' => $elementid, 'defaultConfig' => $defaultConfig, 'config' => $configoptions];
    }

}