<?php

/**
 * Connects with the provided Typeform API key and fetches the responses to
 * cache locally within SilverStripe.
 *
 * Operation is via a cron job on the server which is scheduled to run once an
 * hour. Comments for a single form are synced through the
 * {@link SyncTypeformSubmissions_Single} class.
 *
 * @package typeform
 */
class SyncTypeformSubmissions extends BuildTask {

	private static $typeform_classes = array(
		'Page',
	);

	public function run($request) {
		increase_time_limit_to();
		increase_memory_limit_to();

		$formId = $request->getVar('form');
		$force = $request->getVar('force') || false;

		if ($request->getVar('delete') && Director::isDev()) {
			$submissions = TypeformSubmission::get();

			if ($formId) {
				$submissions = $submissions->filter('ParentID', $formId);
			}

			foreach ($submissions as $submission) {
				$submission->delete();
			}
		}

		foreach ($this->config()->typeform_classes as $class) {
			$forms = DataObject::get($class);

			if (Director::is_cli()) {
				echo "Syncing " . $class . " forms\n";
			} else {
				echo "<p>Syncing " . $class . " forms</p>";
			}

			if (!$formId) {
				if (Director::is_cli()) {
					echo $forms->count() . " found\n";
				} else {
					echo "<p>" . $forms->count() . " found</p>";
				}
			}

			foreach ($forms as $form) {
				$key = null;

				if ($form->hasMethod('getTypeformUid')) {
					$key = $form->getTypeformUid();
				}

				if ($key && $formId && $form->ID !== $formId) {
					if (Director::is_cli()) {
						echo sprintf("* Skipping %s\n", $form->Title);
					} else {
						echo sprintf("<li>Skipping %s</li>", $form->Title);
					}

					continue;
				}

				if ($key) {
					$fetch = new SyncTypeformSubmissions_Single($key);
					$results = $fetch->syncComments($form, $force);

					$total = $results['total'];
					$synced = $results['synced'];

					if (Director::is_cli()) {
						echo sprintf("* %d new synced submissions out of %d total for %s\n", $synced, $total, $form->Title);
					} else {
						echo sprintf("<li>%d new synced submissions out of %d for %s</li>", $synced, $total, $form->Title);
					}
				} else {
					if (Director::is_cli()) {
						echo sprintf("* No valid key for %s\n", $form->Title);
					} else {
						echo sprintf("<li>No valid key for %s</li>", $form->Title);
					}
				}
			}
		}
	}
}

/**
 * @package typeform
 */
class SyncTypeformSubmissions_Single {

	/**
	 * @param string $formKey
	 */
	public function __construct($formKey) {
		$this->formKey = $formKey;
	}

	/**
	 * @param ITypeform $target
	 * @param boolean $force
	 *
	 * @return array
	 */
	public function syncComments(ITypeform $target, $force = false, $offset = 0) {
		// either now or 10 minutes.
		$results = array(
			'total' => 0,
			'synced' => 0,
		);

		$limit = 500;

		$since = $target->getLastTypeformImportedTimestamp();

		if (!$force) {
			if ($since) {
				$since = '&since=' . $since;
			}
		} else {
			$since = '';
		}

		$rest = new RestfulService("https://api.typeform.com/v0/form/", 0);
		$url =
			sprintf("%s?key=%s&completed=true&offset=0&limit=%s%s",
			$this->formKey,
			SiteConfig::current_site_config()->TypeformApiKey,
			$offset,
			$limit,
			$since
		);

		$response = $rest->request($url);

		if ($response && !$response->isError()) {
			$body = json_decode($response->getBody(), true);

			if (isset($body['stats'])) {
				$target->extend('updateTypeformStats', $body['stats']);
			}

			if (isset($body['questions'])) {
				$this->populateQuestions($body['questions'], $target, $results);
			}

			if (isset($body['responses'])) {
				$this->populateResponses($body['responses'], $target, $results, $force);
			}

			// if the number of responses are 500, then we assume we need to
			// sync another page.
			$body = json_decode($response->getBody());

			if ($body->stats->responses->total >= ($offset + $limit)) {
				$this->syncComments($target, $force, $offset + $limit);
			}
		} else {
			SS_Log::log($response->getBody(), SS_Log::WARN);
		}

		return $results;
	}

	public function populateQuestions($questions, $target, $results) {
		foreach ($questions as $question) {
			$existing = TypeformQuestion::get()->filter(array(
				'ParentID' => $target->ID,
				'Reference' => $question['id'],
			))->first();

			if (!$existing) {
				$existing = TypeformQuestion::create();
				$existing->ParentID = $target->ID;
				$existing->Reference = $question['id'];
			}

			$existing->FieldID = $question['field_id'];

			if (isset($question['group']) && $question['group']) {
				$group = TypeformQuestion::get()->filter('Reference', $question['group'])->first();

				if ($group) {
					$existing->GroupFieldID = $group->ID;
				}
			}

			$existing->Title = $question['question'];
			$existing->write();
		}
	}

	public function populateResponses($responses, $target, &$results, $force) {
		// assumes comments don't update.
		foreach ($responses as $response) {
			$results['total']++;

			$deleted = TypeformSubmission_Deleted::get()->filter(array(
				'TypeformID' => $response['id'],
				'ParentID' => $target->ID,
			));

			if ($deleted->count() > 0 && !$force) {
				continue;
			}

			$existing = TypeformSubmission::get()->filter(array(
				'TypeformID' => $response['id'],
				'ParentID' => $target->ID,
			));

			if ($existing->count() > 0) {
				continue;
			} else {
				$results['synced']++;

				// check to make sure it hasn't been deleted
				$submission = TypeformSubmission::create();

				$submission->TypeformID = $response['id'];
				$submission->DateStarted = date("Y-m-d H:i:s", strtotime($response['metadata']['date_land'] . ' UTC'));
				$submission->DateSubmitted = date("Y-m-d H:i:s", strtotime($response['metadata']['date_submit'] . ' UTC'));

				$submission->ParentID = $target->ID;
				$submission->write();

				if (isset($response['answers'])) {
					foreach ($response['answers'] as $field => $value) {
						$question = TypeformQuestion::get()->filter(array(
							'Reference' => $field,
						))->first();

						if (!$question) {
							$question = TypeformQuestion::create();
							$question->ParentID = $target->ID;
							$question->Reference = $reference;
							$question->write();
						}

						$answer = TypeformSubmission_Answer::create();
						$answer->Label = $question->Title;
						$answer->QuestionID = $question->ID;
						$answer->SubmissionID = $submission->ID;
						$answer->Value = $value;
						$answer->write();
					}
				}

				$submission->extend('onAfterAnswersSynced');
			}
		}
	}
}
