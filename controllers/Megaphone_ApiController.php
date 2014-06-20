<?php

namespace Craft;

class Megaphone_ApiController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionPrepare()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$return = craft()->megaphone->backupDatabase();

		if (!$return['success'])
		{
			$this->returnJson(array('errorDetails' => $return['message']));
		}
		else
		{
			$data['filename'] = $return['dbBackupPath'] . '.sql';
			$this->returnJson(array('data' => $data));
		}
	}

	public function actionBackup()
	{
		$this->requireKey();
	}

	public function actionDownload()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$file = craft()->request->getRequiredPost('filename');

		if (($filePath = IOHelper::fileExists(craft()->path->getDbBackupPath() . $file)) == true)
		{
			craft()->request->sendFile(IOHelper::getFileName($filePath), IOHelper::getFileContents($filePath), array('forceDownload' => false));
		}
	}

	public function actionUpload()
	{
		$this->requireKey();
	}

	public function actionReplace()
	{
		$this->requireKey();
	}

	public function actionClean()
	{
		$this->requireKey();
	}

	public function requireKey()
	{
		$key = craft()->request->getRequiredPost('key');
		$settings = craft()->megaphone->getSettings();
		if ($key !== $settings->key)
		{
			$this->returnJson(array('errorDetails' => Craft::t('Invalid key.'), 'finished' => true));
		}
	}
}
