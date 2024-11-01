<?php
/**
 * @var array $products
 */
?>

<div class="ubiliz-products-wrapper">
	<?php foreach ( $products as $product ): ?>
        <div class="ubiliz-product-col">
            <div class="ubiliz-product-item">
                <div class="ubiliz-product-box">
                    <img
                            src="<?php echo esc_url($product['mediumPicture']) ?>"
                            alt="Image <?php echo esc_html($product['name']) ?>"
                            class="ubiliz-product-image"
                    >

                    <div class="ubiliz-product-overlay"></div>

                    <p class="ubiliz-product-values-wrapper">
	                    <?php $price = $product['price']; ?>
	                    <?php if ($product['priceType'] !== 'fixed'): ?>
		                    <?php $price = $product['minPrice'] ?>
                            <span class="ubiliz-product-min-text">
                                à partir de
                            </span>
                            <br>
	                    <?php endif; ?>

                        <span class="ubiliz-product-price">
			                <?php echo UbilizGiftStore::format_price(esc_html($price)) ?><sup>€</sup>
                        </span>

	                    <?php if ($product['type'] === 'voucher' && $product['minParticipantCount'] !== $product['maxParticipantCount']): ?>
                            <br>
                            <span class="ubiliz-product-count-text">
                            <?php echo sprintf('de %d à %d participants', esc_html($product['minParticipantCount']), esc_html($product['maxParticipantCount'])) ?>
                            </span>
	                    <?php endif; ?>
                    </p>

                    <div class="ubiliz-product-push">
                        <span>Offrir</span>
                        <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.883 12l-7.527 6.235.644.765 9-7.521-9-7.479-.645.764 7.529 6.236h-21.884v1h21.883z"/>
                        </svg>
                    </div>
                </div>

                <h6 class="ubiliz-product-name">
                    <a href="<?php echo esc_url($product['url']) ?>" class="ubiliz-product-link" target="_blank">
						<?php echo esc_html($product['name']) ?>
                    </a>
                </h6>

                <div class="ubiliz-product-infos">
					<?php if ( $text = $product['commercialIntro'] ?: $product['description'] ): ?>
                        <p class="ubiliz-product-text">
							<?php echo wp_trim_words( esc_html($text), 15 ) ?>
                        </p>
					<?php endif; ?>
                </div>
            </div>
        </div>
	<?php endforeach; ?>
</div>
