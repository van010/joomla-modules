<?php 
  $helpers = $displayData['helper'];
  $cats = $displayData['data'];
  $options = $displayData['options'];

  if(!empty($cats)) { ?>
  <div class="category-listing">
    <ul>
      <?php foreach ($cats as $cat) :?>
        <li><a href="<?php echo $cat->cat_link; ?>"><?php echo $cat->title;?></a></li>
      <?php endforeach ?>
    </ul>
  </div>
  <?php
  }
?>