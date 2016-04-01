<?php

/**
 * @package typeform
 */
class TypeformQuestion extends DataObject
{
    
    private static $db = array(
        'Reference' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'FieldID' => 'Varchar',
        'ParentID' => 'Int'
    );

    private static $has_one = array(
    	'GroupField' => 'TypeformQuestion'
    );

    private static $has_many = array(
        'GroupedChildren' => 'TypeformQuestion'
    );

    private static $summary_fields = array(
    	'ID',
    	'Title',
    	'FieldID',
    	'GroupField.Title'
    );
}
