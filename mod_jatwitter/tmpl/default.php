<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');	

use Joomla\CMS\Language\Text;

?>
<div class="ja-twitter">
	<?php if( $params->get('showtextheading') ) : ?>
	<h4><?php echo $params->get('headingtext'); ?></h4>
	<?php endif; ?>
	
	<!-- ACCOUNT INFOMATION -->
	<?php if( $useDisplayAccount && !empty($accountInfo)): ?>
	<div class="ja-twitter-account">
		<?php include( JModuleHelper::getLayoutPath( 'mod_jatwitter', 'screen_name') ); ?>
	</div>
	<?php endif; ?>
	<!-- // ACCOUNT INFOMATION -->
	
	<!-- LISTING TWEETS -->
	<?php if( is_array($list) && !empty($list) ) : ?>	
	<div class="ja-twitter-tweets">
	
		<?php foreach( $list as $item ) : ?>
		<div class="ja-twitter-item">
			<?php if( $showIcon ) : ?>
			<div class="ja-twitter-image">
		 		<a href="https://twitter.com/<?php echo $item->screen_name; ?>" target="_blank">
					<img src="<?php echo $item->profile_image_url; ?>" style="width:<?php echo $iconsize; ?>px;" alt="<?php echo $item->name; ?>" class="ja-twitter-avatar" />
				</a>
			</div>
			<?php endif ; ?>
			
			<?php if( $showSource ) : ?>
			<div class="ja-twitter-source">
				<?php echo Text::_( 'FROM' ); ?> <span><?php echo $item->source; ?></span>
			</div>
			<?php endif ; ?>
			
			<div class="ja-twitter-text">
				<?php if( $showUsername ) : ?>
			    <a href="https://twitter.com/<?php echo $item->screen_name; ?>" target="_blank"><?php echo $item->name; ?></a>
				<?php endif ; ?>
					
				<?php echo $jatHerlper->convert( $item->text ); ?>
			</div>
			<div class="ja-twitter-date" style="">
				<?php echo $jatHerlper->getDate( $item->created_at ); ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php else : ?>
	<div ja-twitter-tweets>
		<?php echo Text::_('ERROR_SERVER_RESPONSE'); ?>
	</div>
	<?php endif; ?>	
	<!-- //LISTING TWEETS -->
	
	<!-- LISTING FRIENDS -->
	<?php if ( $useFriends && isset($friends) && is_array($friends) ): ?>
	<div class="ja-twitter-friends clearfix">
		<h4><?php echo  Text::_( 'MY_FRIENDS' ); ?></h4>
		<?php foreach( $friends as $friend ) :	?>
		<?php if( isset( $friend ) ) :	?>

			<a href="https://twitter.com/<?php echo $friend->screen_name; ?>" title="<?php echo $friend->name; ?>" target="_blank">
				<img style="width:<?php echo $sizeIconfriend; ?>px;" alt="<?php echo $friend->name; ?>" src="<?php echo $friend->profile_image_url; ?>" />	
			</a>	
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php elseif ( !$useFriends):?>
	<?php else:?>
		<center>
			<h4><?php echo Text::_('NO_FRIEND');?></h4>
		</center>
	<?php endif; ?>
	<!-- //LISTING FRIENDS -->
	<?php if ( $showfollowlink == "1" ){ ?>
	<br />
	<center>
	<?php echo $jatHerlper->getFollowButton($params);  ?>
	</center>
	<?php }  ?>
</div>