<?php

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;

$helper = new JAGAHelper();
$client = $displayData['client'];
$scriptUri = $displayData['scriptUri'];
$authUrl = $client->createAuthUrl();
	if (!isset($_REQUEST['ja_ga_authorize'])) : ?>
		<div class="ga-authorize">
			<?php echo Text::_('JA_GA_GET_ACCESS_CODE_DESC');?>
			<a href="<?php echo $authUrl; ?>" target="_blank"><?php echo Text::_('JA_GA_GET_ACCESS_CODE'); ?></a>
			<form name="ja_ga_input" action="<?php echo $scriptUri; ?>" method="get">
				<h5><?php echo Text::_('JA_GA_ACCESS_CODE'); ?></h5>
				<input type="password" name="ja_ga_code" value="" size="61"> <br/>
				<input type="submit" class="btn btn-primary" name="ja_ga_authorize" value="<?php echo Text::_('JA_GA_SAVE_ACCESS_CODE'); ?>"/>
			</form>
		</div>
<?php 
	else:
		if ($_REQUEST['ja_ga_code']) {
			$client->authenticate($_REQUEST['ja_ga_code']);
			$token = $client->getAccessToken();
			$helper::store_token($token);
			header("Location: " . $scriptUri);
		} else {
			header("Location: " . $scriptUri);
		}
	endif;
?>