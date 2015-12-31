<?php

/**
 * @package typeform
 */
class TypeformSubmissionAdmin extends ModelAdmin
{

    private static $managed_models = array(
        'TypeformSubmission',
        'TypeformSubmission_Deleted'
    );

    private static $menu_title = 'Typeform';

    private static $url_segment = 'typeform';

    public $showImportForm = false;
}
