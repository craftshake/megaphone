<?php

namespace Craft;

class Megaphone_ApiController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionPrepareForPull()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$settings = craft()->megaphone->getSettings();
		if (!$settings->allowPull)
		{
			$this->returnErrorJson('Pull is not enabled on remote');
		}

		$return = craft()->megaphone->backupLocalDatabase();

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$data['filename'] = $return['dbBackupPath'] . '.sql';
			$this->returnJson(array('success' => true, 'data' => $data));
		}
	}

	public function actionPrepareForPush()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$settings = craft()->megaphone->getSettings();
		if (!$settings->allowPush)
		{
			$this->returnErrorJson('Push is not enabled on remote');
		}

		$data = array(
			'siteName' => craft()->getSiteName(),
			'siteUrl' => craft()->getSiteUrl()
		);

		$this->returnJson(array('success' => true, 'data' => $data));
	}

	public function actionBackup()
	{
		$this->actionPrepareForPull();
	}

	public function actionDownload()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$file = craft()->request->getRequiredPost('filename');

		if (($filePath = IOHelper::fileExists(craft()->path->getDbBackupPath() . $file)) == true)
		{
			craft()->request->sendFile(IOHelper::getFileName($filePath), IOHelper::getFileContents($filePath), array('forceDownload' => false));
		}
	}

	public function actionUpload()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$return = craft()->megaphone->receive();

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionUpdate()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->updateLocalDatabase($data['filename']);

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionReplace()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->replaceLocalStrings($data['siteName'], $data['siteUrl']);

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionClean()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->cleanLocalFiles($data['filename'], $data['dbBackupPath']);

		$this->returnJson(array('success' => true));
	}

	public function actionRollback()
	{
		$this->requirePostRequest();
		$this->requireMegaphoneKey();

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->rollbackLocalDatabase($data['dbBackupPath']);

		$this->returnJson(array('success' => true));
	}

	public function requireMegaphoneKey()
	{
		$key = craft()->request->getRequiredPost('key');
		$plugin = craft()->plugins->getPlugin('megaphone');
		if ($plugin == null)
		{
			$this->returnErrorJson(Craft::t('Megaphone is not installed or enabled on remote.'));
		}
		$settings = craft()->megaphone->getSettings();
		if ($key !== $settings->key)
		{
			$this->returnErrorJson(Craft::t('Invalid key.'));
		}
	}
}
