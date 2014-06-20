<?php

namespace Craft;

class Megaphone_PullController extends BaseController
{
	public function actionPrepare()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->prepare();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Downloading remote database...'), 'nextAction' => 'download'));
		}
	}

	public function actionDownload()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->download($data['remote'], $data['key']);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Backing up local database...'), 'nextAction' => 'backup'));
		}
	}

	public function actionBackup()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$return = craft()->megaphone->backupDatabase();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered.'), 'nextAction' => 'rollback'));
		}
		else
		{
			if (isset($return['dbBackupPath']))
			{
				$data['dbBackupPath'] = $return['dbBackupPath'];
			}
			$this->returnJson(array('nextStatus' => Craft::t('Updating local database...'), 'nextAction' => 'update', 'data' => $data));
		}
	}

	public function actionUpdate()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$return = craft()->megaphone->updateDatabase();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered. Rolling back…'), 'nextAction' => 'rollback'));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Replacing strings in database...'), 'nextAction' => 'replace'));
		}
	}

	public function actionReplace()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$return = craft()->megaphone->replaceStrings();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered. Rolling back…'), 'nextAction' => 'rollback'));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Cleaning up...'), 'nextAction' => 'clean'));
		}
	}

	public function actionClean()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		craft()->megaphone->clean();

		$this->returnJson(array('finished' => true, 'returnUrl' => 'megaphone'));
	}

	public function actionRollback()
	{

	}
}
