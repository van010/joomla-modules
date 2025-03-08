<?php

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
// Auth call to Google.
if (!$client->getAccessToken()) : 
	echo LayoutHelper::render($module->module.'.authorize', array('client'=>$client,'scriptUri'=>$scriptUri),dirname(__DIR__) . '/layouts');
elseif (!isset($profileId['error'])) : ?>
	<div id="ja_ga_stats" class="<?php echo $params->get('moduleclass_sfx','') ?> row-fluid">
    <div class="ga-container">
      <div class="row">
    		<?php if ($params->get('active_users','1') == '1') : ?>
    			<div id="ja_active_users" class="span4">
    				<span class="ja-ga-title"><?php echo Text::_('JA_GA_TMPL_ACTIVE_USERS_TITLE');?></span>
    				<span class="ja-ga-number" id="active-users">0</span>
    				<span class="ja-ga-time"><?php echo Text::_('JA_GA_NOW')?></span>
    			</div>
    		<?php endif; ?>
    		<?php if ($params->get('pageview', '1') == '1') : ?>
    			<div id="ja_page_views" class="span4">
    				<span class="ja-ga-title"><?php echo Text::_('JA_GA_TMPL_PAGE_VIEWS_TITLE');?></span>
    				<span class="ja-ga-number"><?php echo number_format($pageViews)?></span>
    				<span class="ja-ga-time"><?php echo $time ?></span>
    			</div>
    		<?php endif; ?>
    		<?php if ($params->get('bounce_rate','1') == '1') : ?>
    			<div id="ja_bounce_rates" class="span4">
    				<span class="ja-ga-title"><?php echo Text::_('JA_GA_TMPL_BOUNCE_RATE_TITLE');?></span>
    				<span class="ja-ga-number"><?php echo round($bounceRate,2)?>%</span>
    				<span class="ja-ga-time"><?php echo $time ?></span>
    			</div>
    		<?php endif; ?>
      </div>
    </div>
    <div class="ja_ga_foot">
      <div class="ga-container">
        <a href="<?php echo $scriptUri.'?ja_refresh=1' ?>"><?php echo Text::_('JA_GA_REFRESH')?></a>
      </div>
    </div>
  </div>
	<script type="text/javascript">
		(function($) {
			getActiveUsers();
			function getActiveUsers() {
				var current = parseInt($('#active-users').text());
				var time_request = <?php echo $params->get('time_getuser','10'); ?>*1000;

				$.get('<?php echo $fetch_users_url; ?>', function(data) {
					var result = 0;
					if (data.totalsForAllResults) {
						result = data.totalsForAllResults['rt:activeUsers'];
					}
					if (result != current) {
						$('#active-users').html('<span style="color:#ff0000;">'+result+'</span>');
						setTimeout(function() {
							$('#active-users span').css('color','#096');
						}, 2000);
					}
				});

				setTimeout(function() {
					getActiveUsers();
				},time_request)
			}
		})(jQuery);
	</script>
<?php else : ?>
	<p><?php echo Text::_('JA_GA_ERROR_NOTICE')?>, please
		<a href="<?php echo $scriptUri.'?ja_refresh=1' ?>"><?php echo Text::_('JA_GA_REFRESH')?></a>
	</p>
<?php endif;