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

		$return = craft()->megaphone->prepare($data['remote'], $data['key']);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$data['filename'] = $return['filename'];
			$this->returnJson(array('nextStatus' => Craft::t('Downloading remote database...'), 'nextAction' => 'download', 'data' => $data));
		}
	}

	public function actionDownload()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->download($data['remote'], $data['key'], $data['filename']);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Backing up local database...'), 'nextAction' => 'backup', 'data' => $data));
		}
	}

	public function actionBackup()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$data = craft()->request->getRequiredPost('data');

		sleep(1);

		$return = craft()->megaphone->backupDatabase();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered.'), 'nextAction' => 'rollback'));
		}
		else
		{
			$data['dbBackupPath'] = $return['dbBackupPath'];
			$this->returnJson(array('nextStatus' => Craft::t('Updating local database...'), 'nextAction' => 'update', 'data' => $data));
		}
	}

	public function actionUpdate()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->updateDatabase($data['filename']);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered. Rolling back…'), 'nextAction' => 'rollback'));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Replacing strings in database...'), 'nextAction' => 'replace', 'data' => $data));
		}
	}

	public function actionReplace()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->replaceStrings();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered. Rolling back…'), 'nextAction' => 'rollback'));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Cleaning up...'), 'nextAction' => 'clean', 'data' => $data));
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
