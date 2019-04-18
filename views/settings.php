<?php /** @var \Codeable\ExpertStats\Core\View $view */ ?>
<?php /** @var array $settings_fields */ ?>
<?php /** @var array $import_mode */ ?>
<?php $slug = $view->plugin->safe_slug; ?>
<div class="wrap <?php echo $slug ?>_wrap">
	<form method="post" action="options.php">
		<h2><?php _e( 'Codeable Stats Admin', $slug ); ?></h2>

		<table class="form-table">
			<tbody>


				<?php settings_errors( $slug ); ?>
				<?php settings_fields( $slug ); ?>
				<?php do_settings_sections( $slug ); ?>

				<tr>
					<th scope="row">
						<label class="<?php echo $slug ?>_label"
							   for="import_mode"><?php _e( 'Scan method', $slug ) ?></label>
					</th>
					<td>
						<select id="import_mode" name="import_mode">
							<option value="stop_first" <?php echo( 'stop_first' === $import_mode ? 'selected="selected"' : '' ) ?>><?php _e( 'Stop if the transaction id is found (use this if you want to update your data of first time fetch)', $slug ) ?></option>
							<option value="all" <?php echo( 'all' === $import_mode ? 'selected="selected"' : '' ) ?>><?php _e( 'Check everything (use this if you got a time out while fetching)', $slug ) ?></option>
						</select>
					</td>
				</tr>
				<?php
				foreach ( $settings_fields as $key => $label ) {
					$view->render( '_partial/setting', [ 'key' => $key, 'label' => $label ] );
				}
				?>
			</tbody>
		</table>
		<div class="action-buttons">
			<?php submit_button( __( 'Save Changes', $slug ) ); ?>
		</div>
	</form>
</div>


<div class="wrap wpcable_wrap">
	<button name="submit"
			class="button button-large button-action"><?php echo __( 'Fetch remote data', $slug ); ?></button>
	<a href="admin.php?page=codeable_settings&flushdata=true"
	   class="button button-large button-danger"><?php echo __( 'Delete cached data', $slug ); ?></a>

</div>
