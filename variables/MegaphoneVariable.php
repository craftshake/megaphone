<?php

namespace Craft;

class MegaphoneVariable
{
	public function getSettings()
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');
		return $megaphone->getSettings();
	}
}
