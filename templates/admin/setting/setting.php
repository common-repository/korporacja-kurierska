<?php if ( ! empty( $settings ) ): ?>
    <div class="wrap">
        <form method="post" action="">
            <div class="postbox-container" id="kk-postbox-settings">
                <div class="postbox">
                    <h2 class="hndle">
                        <span><?= __( 'Ustawienia', 'korporacja-kurierska' ); ?></span>
                    </h2>
                    <div class="inside korporacja-tabs">
                        <ul class="">
							<?php foreach ( $settings as $key => $setting ): ?>
                                <li>
                                    <a href="#<?= $key; ?>"><span><?= $setting['name']; ?></span></a>
                                </li>
							<?php endforeach; ?>
                        </ul>

						<?php foreach ( $settings as $key => $setting ): ?>
                            <div id="<?= $key; ?>" class="">
								<?= $setting['content']; ?>
                            </div>
						<?php endforeach; ?>
                    </div>
                    <button class="button button-primary">
						<?= __( 'Zapisz', 'korporacja-kurierska' ); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>
