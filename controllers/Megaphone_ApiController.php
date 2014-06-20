<?php

namespace Craft;

class Megaphone_ApiController extends BaseController
{
	public function actionPrepare()
	{
		$this->requireKey();
	}

	public function actionBackup()
	{
		$this->requireKey();
	}

	public function actionDownload()
	{
		$this->requireKey();
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
		$key = craft()->request->getRequiredQuery('key');
		$settings = craft()->megaphone->getSettings();
		if ($key !== $settings->key)
		{
			$this->returnJson(array('errorDetails' => Craft::t('Invalid key.'), 'finished' => true));
		}
	}
}
