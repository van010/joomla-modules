/* Put your custom javascript for this layout here */
(function($){
  $(document).ready(function(){
    var $container = $('.jacl-isotope');
    
    if (!$container.length) return ;
    
    $container.isotope({
      itemSelector: '.jacl-col',
      horizontalOrder: true
    });
  
    // re-order when images loaded
    $container.imagesLoaded(function(){
      $container.isotope();
      
      /* fix for IE-8 */
      setTimeout (function() {
        $('.jacl-isotope').isotope();
      }, 8000);  
    });
  });

})(jQuery);
