<?php

namespace Craft;

class Megaphone_PushController extends BaseController
{
	public function actionPrepare()
	{
		// Create .sql file and get remote site name & url
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->preparePush($data);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$data['filename'] = $return['filename'];
			$data['siteName'] = $return['siteName'];
			$data['siteUrl'] = $return['siteUrl'];
			$this->returnJson(array('nextStatus' => Craft::t('Uploading local database...'), 'nextAction' => 'upload', 'data' => $data));
		}
	}

	public function actionUpload()
	{
		// Send local to remote
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->upload($data);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$this->returnJson(array('nextStatus' => Craft::t('Backing up remote database...'), 'nextAction' => 'backup', 'data' => $data));
		}
	}

	public function actionBackup()
	{
		// Backup remote database
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->backupRemoteDatabase($data);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered.'), 'finished' => true));
		}
		else
		{
			$data['dbBackupPath'] = $return['dbBackupPath'];
			$this->returnJson(array('nextStatus' => Craft::t('Updating remote database...'), 'nextAction' => 'update', 'data' => $data));
		}
	}

	public function actionUpdate()
	{
		// Update remote database
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->updateRemoteDatabase($data);

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
		// Replace strings
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->replaceRemoteStrings($data);

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
		// Clean up local file
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->cleanRemoteFiles($data);

		$this->returnJson(array('finished' => true, 'returnUrl' => 'megaphone'));
	}

	public function actionRollback()
	{
		// Rollback
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->rollbackRemoteDatabase($data['dbBackupPath']);

		$this->returnJson(array('finished' => true, 'rollBack' => true));
	}
}
