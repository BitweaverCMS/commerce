<h3><?php echo tra( 'Customers who bought this product also purchased...' ); ?></h3>
<div class="row">
<?php foreach( $relatedList as $productId => $productText ) { ?><div class="small col-xs-4 col-sm-3 col-lg-2"><?php echo $productText; ?></div><?php } ?>
</div>
