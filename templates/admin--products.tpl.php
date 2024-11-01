<?php

/**
 * @var string $title
 * @var bool $is_token_set
 * @var bool $is_token_valid
 * @var array $products
 */

?>

<div class="ubiliz-gs-wrapper">
    <h1><?php echo esc_html($title) ?></h1>

    <div class="ubiliz-gs-content">

		<?php if ( $is_token_set === false || $is_token_valid === false ): ?>
            <div class="ubiliz-gs-container">
                <p class="text-centered">
                    <a href="<?php echo admin_url( 'admin.php?page=ubiliz-gs-settings' ) ?>" class="button-primary">
                        Configurer
                    </a>
                </p>
            </div>
		<?php else: ?>

            <h2>Liste de vos produits</h2>

			<?php if ( empty( $products ) ): ?>
                <p>
                    Vous n'avez actuellement pas de produits dans votre liste.
                </p>

			<?php else: ?>
                <table class="ubiliz-gs-table">
                    <tbody>
					<?php foreach ( $products as $product ): ?>
                        <tr>
                            <td>
                                <img style="width: 100px" src="<?php echo esc_url($product['smallPicture']) ?>" alt="Image <?php echo esc_html($product['name']) ?>">
                            </td>
                            <td>
                                <h4><?php echo esc_html($product['name']) ?></h4>
                                <p>
									<?php echo esc_html($product['commercialIntro']) ?>
                                </p>
                                <p>
		                            <?php echo esc_html($product['description']) ?>
                                </p>
                            </td>
                            <td>
                                <?php if (esc_html($product['priceType']) !== 'fixed'): ?>
                                    <span>à partir de</span>
                                <?php endif; ?>
                                <?php $price =  $product['minPrice'] ?: $product['price'] ?>
	                            <?php echo UbilizGiftStore::format_price(esc_html($price) ) ?>€
                                <?php if ($product['type'] === 'voucher' && $product['minParticipantCount'] !== $product['maxParticipantCount']): ?>
                                    <br> <?php echo sprintf('de %d à %d participants.', esc_html($product['minParticipantCount']), esc_html($product['maxParticipantCount'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
								<?php if ( $product['enabled'] ): ?>
                                    <span class="ubiliz-badge">Activé</span>
								<?php else: ?>
                                    <span class="ubiliz-badge disabled">Désactivé</span>
								<?php endif ?>
                            </td>
                            <td>
                                <a href="https://app.ubiliz.com/configuration-bons/<?php echo esc_html($product['id']) ?>/edit" target="_blank" class="button-primary">
                                    Modifier
                                </a>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
			<?php endif; ?>

            <p>
                <a href="https://app.ubiliz.com/configuration-bons/type-offre" class="button-primary" target="_blank">
                    Créer une offre
                </a>
            </p>

		<?php endif; ?>

    </div>
</div>
