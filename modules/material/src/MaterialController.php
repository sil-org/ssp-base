<?php

namespace SimpleSAML\Module\material;

use Exception;
use SimpleSAML\Configuration;
use SimpleSAML\XHTML\TemplateControllerInterface;
use Twig\Environment;

class MaterialController implements TemplateControllerInterface
{
    /**
     * Modify the twig environment after its initialization (e.g. add filters or extensions).
     *
     * @param Environment $twig The current twig environment.
     * @return void
     */
    public function setUpTwig(Environment &$twig): void
    {
    }

    /**
     * Add, delete or modify the data passed to the template.
     * This method will be called right before displaying the template.
     *
     * @param array $data The current data used by the template.
     * @return void
     * @throws Exception
     */
    public function display(array &$data): void
    {
        $globalConfig = Configuration::getInstance();
        $data['theme_color_scheme'] = $globalConfig->getOptionalString('theme.color-scheme', null);
        $data['analytics_tracking_id'] = $globalConfig->getOptionalString('analytics.trackingId', '');

        if (!isset($data['idp_name']) || $data['idp_name'] === '') {
            $data['idp_name']  = $globalConfig->getOptionalString('idp_name', '');
        }
        if (!isset($data['help_center_url']) || $data['help_center_url'] === '') {
            $data['help_center_url'] = $globalConfig->getOptionalString('helpCenterUrl', '');
        }
        if (!isset($data['profile_url']) || $data['profile_url'] === '') {
            $data['profile_url'] = $globalConfig->getOptionalString('profileUrl', '');
        }
    }
}
