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

		$return = craft()->megaphone->preparePull($data['remote'], $data['key']);

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'finished' => true));
		}
		else
		{
			$data['filename'] = $return['filename'];
			$data['siteName'] = craft()->getSiteName();
			$data['siteUrl'] = craft()->getSiteUrl();
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

		$return = craft()->megaphone->backupLocalDatabase();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message'], 'nextStatus' => Craft::t('An error was encountered.'), 'finished' => true));
		}
		else
		{
			$data['dbBackupPath'] = $return['dbBackupPath'] . '.sql';
			$this->returnJson(array('nextStatus' => Craft::t('Updating local database...'), 'nextAction' => 'update', 'data' => $data));
		}
	}

	public function actionUpdate()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		sleep(1);

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->updateLocalDatabase($data['filename']);

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

		$return = craft()->megaphone->replaceLocalStrings($data['siteName'], $data['siteUrl']);

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

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->cleanLocalFiles($data['filename'], $data['dbBackupPath']);

		$this->returnJson(array('finished' => true, 'returnUrl' => 'megaphone'));
	}

	public function actionRollback()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$data = craft()->request->getRequiredPost('data');

		sleep(1);

		craft()->megaphone->rollbackLocalDatabase($data['dbBackupPath']);

		$this->returnJson(array('finished' => true, 'rollBack' => true));
	}
}
