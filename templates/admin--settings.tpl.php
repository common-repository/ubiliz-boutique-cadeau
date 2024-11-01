<?php
/**
 * @var string $title
 * @var bool $is_token_set
 * @var bool $is_token_valid
 * @var string $token
 * @var array $place_data
 * @var array $places
 * @var array $errors
 */

?>

<div class="ubiliz-gs-wrapper">
    <h1><?php echo esc_html($title) ?></h1>

    <div class="ubiliz-gs-content">
        <p>
            <strong>Connectez-vous à votre compte Ubiliz</strong>
        </p>
        <form action="#" method="post">
            <table class="form-table">
                <tbody>
				<?php if ( $is_token_set === false ): ?>
                    <tr>
                        <td style="width: 15%">
                            <label for="ubiliz_token">Token</label>
                        </td>
                        <td style="width: 15%">
                            <input type="text" id="ubiliz_token" name="ubiliz_token" required>
                        </td>
                        <td style="width: auto">
                            <input type="submit" name="submit" value="Valider" class="button-primary">
                        </td>
                    </tr>
				<?php else: ?>
                    <tr>
                        <td style="width: 15%">
                            <label for="ubiliz_token">Token</label>
                        </td>
                        <td style="width: 15%">
                            <input type="text" id="ubiliz_token" name="ubiliz_token" value="<?php echo esc_html($token) ?>" readonly>
                        </td>
                        <td style="width: auto">
                            <input type="submit" name="reset_token" value="Réinitialiser" class="button-secondary">
                        </td>
                    </tr>

					<?php if ( ! empty( $places ) ): ?>
                        <tr>
                            <td style="width: 15%">
                                <label for="ubiliz_place">Établissement</label>
                            </td>
                            <td style="width: 15%">
                                <select name="ubiliz_place" id="ubiliz_place">
                                    <option value="all">Tous les établissements</option>
									<?php foreach ( $places as $place ): ?>
                                        <option value="<?php echo esc_html($place['id']) ?>" <?php echo (!empty($place_data['id']) && $place_data['id'] == $place['id']) ? 'selected' : '' ?>>
											<?php echo esc_html($place['nameForSlug']) ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
                            </td>
                            <td style="width: auto">
                                <input type="submit" name="submit" value="Enregistrer" class="button-primary">
                            </td>
                        </tr>
					<?php endif; ?>
				<?php endif; ?>
                </tbody>
            </table>

            <?php wp_nonce_field('ubiliz_settings_submit', '_ubiliz_nonce') ?>
        </form>

		<?php if ( $is_token_set === false ): ?>
            <div>
                <p>
                    Si vous avez déjà un compte Ubiliz, renseignez le Token de votre compte ci-dessus.
                    Sinon commencez par créer un compte ici : <br> <br>
                    <a href="https://app.ubiliz.com/register" target="_blank" class="button-primary">
                        Créer un compte Ubiliz
                    </a>
                </p>
            </div>
		<?php endif; ?>

		<?php if ( $is_token_set ): ?>
			<?php if ( $is_token_valid ): ?>
                <div class="notice notice-success">
                    <p>
                        <span class="dashicons dashicons-yes"></span>
                        Connecté
                        <?php if (!empty($place_data['nameForSlug'])): ?>
                            à <strong><?php echo esc_html($place_data['nameForSlug']) ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
			<?php else: ?>
                <div class="notice notice-error">
                    <p>
                        <span class="dashicons dashicons-no-alt"></span>
                        Le token est invalide
                    </p>
                </div>
			<?php endif; ?>
		<?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="notice notice-error">
                <p>
                    <span class="dashicons dashicons-no-alt"></span>
                    <?php echo esc_html($error) ?>
                </p>
            </div>
        <?php endforeach; ?>

		<?php if ( $is_token_valid ): ?>
            <hr>

            <div class="ubiliz-gs-container">

                <p class="text-centered">
                    Vous pouvez maintenant intégrer votre boutique cadeau à n'importe quelle page <br>
                    de votre site en collant votre shortcode :
                </p>

                <div class="ubiliz-gs-sc-ui">
                    <div class="ubiliz-sc">
                        <span id="ubiliz-sc-value">[UBILIZ]</span>
                        <button class="button-primary ubiliz-sc-copy">Copier</button>
                    </div>
                </div>

                <div class="text-centered">
                    <br>
                    <a href="<?php echo admin_url( 'admin.php?page=ubiliz-gs-products' ) ?>" class="button-primary">Voir ma liste de produits</a>
                </div>

            </div>
		<?php endif; ?>

    </div>

</div>

