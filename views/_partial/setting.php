<?php /** @var string $key */ ?>
<?php /** @var string $label */ ?>
<?php $slug = $view->plugin->safe_slug; ?>
<tr>
	<th scope="row">
		<label class="<?php echo $slug ?>_label"
			   for="<?php echo $slug ?><?php echo $key ?>"><?php echo $label ?></label>
	</th>
	<td>
		<input id="<?php echo $slug ?>_<?php echo $key ?>"
			   type="<?php echo( 'password' === $key ? 'password' : 'text' ) ?>" name="<?php echo $key ?>"
			   value="<?php echo $view->plugin->settings->get( $key ) ?>" autocomplete="new-password"/>
	</td>
</tr>
