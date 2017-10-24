<div class="sixtenpress-default-ui <?php echo esc_attr( $this->shortcode_args['modal'] ); ?>"
	 id="<?php echo esc_attr( $this->shortcode_args['modal'] ); ?>" style="display: none;">
	<div class="media-modal wp-core-ui">
		<div class="media-modal-content">
			<button class="media-modal-close">
				<span class="media-modal-icon">
					<span class="screen-reader-text"><?php echo esc_html( $this->shortcode_args['labels']['close'] ); ?></span>
				</span>
			</button>
			<div class="media-frame wp-core-ui hide-menu hide-router sixtenpress-meta-wrap">
				<div class="media-frame-title">
					<h1><?php echo esc_attr( $this->shortcode_args['labels']['title'] ); ?></h1>
				</div>
				<div class="media-frame-content">
					<?php
					do_action( 'sixtenpress_shortcode_modal', $this->shortcode );
					do_action( "sixtenpress_shortcode_modal_{$this->shortcode}", $this->shortcode );
					?>
				</div>
				<div class="media-frame-toolbar">
					<div class="media-toolbar">
						<div class="media-toolbar-secondary">
							<button class="sixtenpress-cancel-insertion button media-button button-large button-secondary media-button-insert"
									title="<?php echo esc_attr( $this->shortcode_args['labels']['cancel'] ) ?>">
								<?php echo esc_attr( $this->shortcode_args['labels']['cancel'] ); ?>
							</button>
						</div>
						<div class="media-toolbar-primary">
							<button class="sixtenpress-insert button media-button button-large button-primary media-button-insert"
									title="<?php echo esc_attr( $this->shortcode_args['labels']['insert'] ); ?>">
								<?php echo esc_attr( $this->shortcode_args['labels']['insert'] ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="media-modal-backdrop"></div>
</div>
