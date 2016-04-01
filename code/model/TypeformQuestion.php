<?php

/**
 * @package typeform
 */
class TypeformQuestion extends DataObject
{
    
    private static $db = array(
        'Reference' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'CustomTitle' => 'Varchar(255)',
        'FieldID' => 'Varchar',
        'ParentID' => 'Int'
    );

    private static $has_one = array(
    	'GroupField' => 'TypeformQuestion'
    );

    private static $has_many = array(
        'GroupedChildren' => 'TypeformQuestion',
        'Answers' => 'TypeformSubmission_Answer'
    );

    private static $summary_fields = array(
    	'ID',
    	'Title',
    	'FieldID',
        'CustomTitle',
    	'GroupField.Title'
    );
}
