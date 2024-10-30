<div class="kk-error">
	<span class="dashicons dashicons-warning"></span>
	<?= $message; ?>
	<?php if (!empty($tip)): ?>
		<div class="woocommerce-help-tip" data-tip="<?= $tip; ?>"></div>
	<?php endif; ?>
</div>
