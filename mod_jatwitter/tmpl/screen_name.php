<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?>
<div class="ja-twitter-user">
	<div class="ja-twitter-wrapper">
	
 		<div class="ja-twitter-thumb clearfix">
		 <a href="https://twitter.com/<?php echo $accountInfo->screen_name; ?>" title="<?php echo $accountInfo->name; ?>" target="_blank">
			<img width="<?php echo $sizeIconaccount; ?>" src="<?php echo $accountInfo->profile_image_url;?>" alt="<?php echo $accountInfo->name; ?>" class="ja-twitter-avatar" />
		</a>
	   
	  <h3><a href="https://twitter.com/<?php echo $accountInfo->screen_name; ?>" target="_blank"><?php echo $accountInfo->name; ?></a></h3>
	  </div>
	    
    <ul>
		<?php if(!empty($accountInfo->location)) : ?>
    	<li><strong><?php echo Text::_( 'LOCATION' ); ?></strong> <?php echo $accountInfo->location; ?></li>
    <?php endif; ?>
	    
    <?php if( !empty($accountInfo->url) ) : ?>
    	<li><strong><?php echo Text::_( 'WEB' ); ?></strong> <a href="<?php echo $accountInfo->url; ?>"><?php echo $accountInfo->url; ?></a></li>
    <?php endif; ?>
        
		<?php if( !empty($accountInfo->description) ) : ?>
	    <li><strong><?php echo Text::_( 'BIO' ); ?></strong> <?php echo $accountInfo->description; ?></li>
    <?php endif; ?>
    </ul>
        
    <ul>
    	<li>
			<span class="count"><?php echo $accountInfo->friends_count; ?></span>
			<span><?php echo Text::_( 'FOLLOWING' ); ?></span>
		</li>
		<li>
			<span class="count"><?php echo $accountInfo->followers_count; ?></span>
			<span><?php echo Text::_( 'FOLLOWERS' ); ?></span>
		</li>
		<li>
			<span class="count"><?php echo $accountInfo->statuses_count; ?></span>
			<span><?php echo Text::_( 'TWEETS' ); ?></span>
		</li>
	</ul>
		
	</div>
</div>
