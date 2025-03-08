<?php
/**
 * $JA#COPYRIGHT$
 */


/**
 *
 * JAFileConfig helper module class
 * @author JoomlArt
 *
 */
class JAFileConfig
{
	/**
	 *
	 * save Profile
	 */
	public static function response($result = array()){
		die(json_encode($result));
	}

	public static function error($msg = ''){
		return self::response(array(
			'error' => $msg
			));
	}

	public static function save()
	{
		// Initialize some variables
		
		$profile = JRequest::getCmd('profile');
		if (!$profile) {
			return self::error(JText::_('INVALID_DATA_TO_SAVE_PROFILE'));
		}

		$params = new JRegistry;
		$post = $_POST;
		if (isset($post)) {
			foreach ($post as $k => $v) {
				$params->set($k, $v);
			}
		}

		$file = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($file)) {
			@chmod($file, 0777);
		}

		$data = $params->toString();
		if (!@JFile::write($file, $data)) {
			return self::error(JText::_('OPERATION_FAILED'));
		}

		return self::response(array(
			'successful' => sprintf(JText::_('SAVE_PROFILE_SUCCESSFULLY'), $profile),
			'profile' => $profile,
			'type' => 'new'
			));
	}

	/**
	 *
	 * Clone Profile
	 */
	public static function duplicate()
	{
		$profile = JRequest::getCmd('profile');
		$from = JRequest::getCmd('from');
		
		if (!$profile || !$from) {
			return self::error(JText::_('INVALID_DATA_TO_SAVE_PROFILE'));
		}

		$path = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'profiles';
		$source = $path . DIRECTORY_SEPARATOR . $from . '.ini';
		$dest = $path . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($dest)) {
			return self::error(sprintf(JText::_('PROFILE_EXIST'), $profile));
		}

		$result = array();
		if (JFile::exists($source)) {
			if ($error = @JFile::copy($source, $dest) == true) {
				return self::response(array(
					'successful' => JText::_('CLONE_PROFILE_SUCCESSFULLY'),
					'profile' => $profile,
					'type' => 'duplicate'
					));
			} else {
				return self::error($error);
			}
		} else {
			return self::error(JText::_(sprintf('PROFILE_NOT_FOUND', $from)));
		}
	}

	/**
	 *
	 * Delete a profile
	 */
	public static function delete()
	{
		// Initialize some variables
		$profile = JRequest::getCmd('profile');
		if (!$profile) {
			return self::error(JText::_('NO_PROFILE_SPECIFIED'));
		}

		$file = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . $profile . '.ini';
		if (JFile::exists($file) && !@JFile::delete($file)) {
			return self::error(sprintf(JText::_('DELETE_FAIL'), $file));
		}

		return self::response(array(
			'successful' => sprintf(JText::_('DELETE_PROFILE_SUCCESSFULLY'), $profile),
			'profile' => $profile,
			'type' => 'delete'
			));
	}
}