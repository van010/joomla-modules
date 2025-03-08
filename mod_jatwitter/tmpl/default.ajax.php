<?php 
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="ja_twitter_div" class="content">
	<?php if($showtextheading == "1"){ ?>
		<h4 class="twitter-title"><?php echo $headingtext;?></h4>
	<?php } ?>	
	<?php if ( $showfollowlink == "1" ){ ?>
		<center>
		<script src='https://platform.twitter.com/anywhere.js?id=<?php echo $apikey?>&amp;v=1' type='text/javascript'></script>
<div id="anywhere-block-follow-button"></div>
  <script type="text/javascript">
  	twttr.anywhere(function(twitter) {
    	twitter('#anywhere-block-follow-button').followButton("<?php echo $screenName?>");
  		})
  </script>
  </center>
	<?php }  ?>
</div>