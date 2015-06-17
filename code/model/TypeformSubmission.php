<?php

/**
 * @package typeform
 */
class TypeformSubmission extends DataObject {
	
	private static $db = array(
		'TypeformID' => 'Int',
		'DateStarted' => 'SS_Datetime',
		'DateSubmitted' => 'SS_Datetime'
	);

	private static $has_one = array(
		'Parent' => 'Page'
	);	

	private static $has_many = array(
		'Answers' => 'TypeformSubmission_Answer'
	);

	private static $default_sort = "ID DESC";
	
	private static $searchable_fields = array(
		'ParentID'
	);

	private static $summary_fields = array(
		'CMSTitle' => 'Title'
	);

	private static $casting = array(
		'Title' => 'Varchar'
	);
	

	public function onAfterDelete() {
		parent::onAfterDelete();

		foreach($this->Answers() as $answer) {
			$answer->delete();
		}
	}

	public function getCMSTitle() {
		return sprintf("%s - %s (%s)", $this->ID, $this->DateSubmitted, $this->Parent()->Title);
	}
	
	public function Title() {
		return $this->TypeformID;
	}

	public function canView($member = null) {
		return true;
	}

	public function onBeforeDelete() {
		parent::onBeforeDelete();

		$deleted = new TypeformSubmission_Deleted();
		$deleted->TypeformID = $this->TypeformID;
		$deleted->ParentID = $this->ParentID;
		$deleted->write();
	}
}

/**
 * @package typeform
 */
class TypeformSubmission_Deleted extends DataObject {

	private static $db = array(
		'TypeformID' => 'Varchar(255)'
	);

	private static $has_one = array(
		'Parent' => 'Page'
	);

	private static $summary_fields = array(
		'TypeformID',
		'Parent.Title'
	);
}

/**
 * @package typeform
 */
class TypeformSubmission_Answer extends DataObject {

	private static $db = array(
		'Value' => 'Text',
		'Label' => 'Varchar(255)'
	);

	private static $has_one = array(
		'Submission' => 'TypeformSubmission',
		'Question' => 'TypeformQuestion'
	);
}