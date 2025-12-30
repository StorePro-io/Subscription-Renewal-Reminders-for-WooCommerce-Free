<?php

/**
 * @package  RenewalReminders
 */



class SPRRAdminCallbacks
{
	public function sprr_adminDashboard()
	{
		return require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-admin.php';
	}

	public function sprr_storeproOptionsGroup($input)
	{
		return $input;
	}

	public function sprr_storeproAdminSection() {}


	public function sprr_storeproEnDisable()
	{
		$value = stripslashes_deep(esc_attr(get_option('en_disable')));

?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Enable/Disable Renewal reminder Notifications!', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>

					<?php

					$sp_enable_button = stripslashes_deep(esc_attr(get_option('en_disable')));

					if ($sp_enable_button == 'on') {

					?>

						<input class="renew-admin_notify_on" type="checkbox" name="en_disable" id="checkbox-switch" checked="checked">

					<?php

					} else {

					?>
						<input class="renew-admin_notify_off" type="checkbox" name="en_disable" id="checkbox-switch">
					<?php

					}

					?>
				</td>
			</tr>
		</table>
	<?php

	}

	public function sprr_storeproNotify()
	{

	?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('These are the days before the reminder is sent out', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>

					<input class="renew-admin_notify_day" type="number" id="quantity" value="<?php echo stripslashes_deep(esc_attr(get_option('notify_renewal'))); ?>" name="notify_renewal" min="1" max="31">

				</td>
			</tr>
		</table>
	<?php

	}

	public function sprr_storeproTime()
	{
		$value = stripslashes_deep(esc_attr(get_option('email_time')));
		$start = strtotime('12:00 AM');
		$end   = strtotime('11:59 PM');

	?>

		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Time in UTC to send out the reminder notification', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>

					<select style="width:85px;" name="email_time" id="select1">
						<?php

						for ($hours = 0; $hours < 24; $hours++) {
							for ($mins = 0; $mins < 60; $mins += 30) {
								$hours_minutes = str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);

								$selected = $hours_minutes == $value ? 'selected' : '';

						?>

								<option value="<?php echo esc_attr($hours_minutes); ?>" <?php echo esc_attr($selected); ?>><?php esc_html_e($hours_minutes); ?></option>
						<?php
							}
						}

						?>
					</select>
				</td>
			</tr>
		</table>
	<?php

	}


	public function sprr_storeproPluginSection()
	{

	?>
		<p class="renew-admin_captionsp"><?php esc_html_e('Add E-mail subject, content from here', 'subscriptions-renewal-reminders'); ?></p>

	<?php
	}

	public function sprr_storeproSubject()
	{

	?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Please add your Email subject', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>
					<input class="renew-admin_email_subj" type="text" class="regular-text" name="email_subject" value="<?php echo esc_attr(stripslashes_deep(get_option('email_subject', get_email_subject_default_value()))); ?>" placeholder="<?php echo esc_attr(get_email_subject_placeholder()); ?>">
				</td>
			</tr>
		</table>

	<?php

	}

	public function sprr_storeproEmaiContent()
	{

	?>

		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Available placeholders:{first_name},{last_name}, {next_payment_date}', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>

					<?php
					//new update to change the content editor to featured wp_editor 16/11/21 prnv_mtn 1.0.2
					$default_content_rem =  stripslashes_deep(get_option('email_content'));
					$editor_id_rem = 'froalaeditor';
					$arg = array(
						'textarea_name' => 'email_content',
						'media_buttons' => true,
						'textarea_rows' => 8,
						'quicktags' => true,
						'wpautop' => false,
						'teeny' => true
					);


					$blank_content_rem = get_blank_content_reminder_text();

					if (strlen(($default_content_rem)) === 0) {
						$default_content_rem .= $blank_content_rem;
					}
					//$stripped_value_sp = stripslashes_deep(esc_attr($default_content_rem));

					wp_editor($default_content_rem, $editor_id_rem, $arg);

					?>

					<p style="margin-top:3px;"><strong><?php esc_html_e('Note:', 'subscriptions-renewal-reminders'); ?></strong></p>
					<ul style="margin-top:4px;font-size:12px;">
						<li>&#9830;<?php esc_html_e('Save the settings to receive contents in the email.', 'subscriptions-renewal-reminders'); ?></li>
						<li>&#9830;<?php esc_html_e('If you made any changes to existing subscriptions or plugin settings, remember to Sync.', 'subscriptions-renewal-reminders'); ?></li>
					</ul>

				</td>
				<td class="d-none">
					<div class="renew-rem-shortcodes">
						<div class="short-code-h">
						<h4><?php echo esc_html__('Available Shortcodes:', 'subscriptions-renewal-reminders'); ?></h4>

							<!-- <button class="button button-primary" onclick="withJquery();">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-back" viewBox="0 0 16 16">
                    <path d="M0 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2z"/>
                </svg> 
            </button> -->
						</div>
						<ul>
							<li><span onclick="copy(this)" title="Copy"><?php echo esc_html__('{first_name}', 'subscriptions-renewal-reminders'); ?></span> : <?php echo esc_html__('User’s First Name.', 'subscriptions-renewal-reminders'); ?></li>
							<li><span onclick="copy(this)" title="Copy"><?php echo esc_html__('{last_name}', 'subscriptions-renewal-reminders'); ?></span> : <?php echo esc_html__('User’s Last Name.', 'subscriptions-renewal-reminders'); ?></li>
							<li><span onclick="copy(this)" title="Copy"><?php echo esc_html__('{next_payment_date}', 'subscriptions-renewal-reminders'); ?></span> : <?php echo esc_html__('Next Payment Date.', 'subscriptions-renewal-reminders'); ?></li>
							<li><span onclick="copy(this)" title="Copy"><?php echo esc_html__('{cancel_subscription}', 'subscriptions-renewal-reminders'); ?></span> : <?php echo esc_html__('Cancel Subscription Button.', 'subscriptions-renewal-reminders'); ?></li>
						</ul>

					</div>
				</td>

			</tr>
		</table>

	<?php

	}
	public function sprr_cancelButtonEnabled()
	{
		$enabled = get_option('sprr_cancel_button_enabled');
		$checked = ($enabled === 'on') ? 'checked' : '';
	?>
		<input type="checkbox" name="sprr_cancel_button_enabled" id="sprr_cancel_button_enabled" <?php echo esc_attr($checked); ?> />
		<label for="sprr_cancel_button_enabled"><?php esc_html_e('Enable Subscription Cancel Button in Renewal Reminder Emails', 'subscriptions-renewal-reminders'); ?></label>

		<!-- Add explanatory text below the checkbox -->
		<p style="margin-top: 5px; font-size: 12px; color: #555;">
			<?php esc_html_e('You can use the shortcode', 'subscriptions-renewal-reminders'); ?> <code><?php esc_html_e('[subscription_cancel_button]', 'subscriptions-renewal-reminders'); ?></code> <?php esc_html_e('to add the cancel button when editing the template.', 'subscriptions-renewal-reminders'); ?>
		</p>
	<?php
	}

	public function sprr_cancelButtonText()
	{
		// Get the option value for the cancel button text
		$button_text = get_option('sprr_cancel_button_text');

		// If the button text is not set, default to 'Cancel Subscription'
		$button_text = !empty($button_text) ? $button_text : esc_html__('Cancel Subscription', 'subscriptions-renewal-reminders'); // Use translation function for the default text

	?>
		<input type="text" name="sprr_cancel_button_text" id="sprr_cancel_button_text" value="<?php echo esc_attr($button_text); ?>" placeholder="<?php echo esc_attr__('Cancel Subscription', 'subscriptions-renewal-reminders'); ?>" /> <!-- Ensure the placeholder is translatable -->

		<label for="sprr_cancel_button_text"><?php echo esc_html__('Change Subscription Cancel Button Text Here', 'subscriptions-renewal-reminders'); ?></label> <!-- Add text domain for translation -->

<?php
	}

	public function sprr_marketingSection()
	{
?>
		<p class="renew-admin_captionsp"><?php esc_html_e('Configure the email template for win-back campaigns sent to cancelled subscription customers.', 'subscriptions-renewal-reminders'); ?></p>
<?php
	}

	public function sprr_winbackSubject()
	{
?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Subject line for win-back emails', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>
					<input class="renew-admin_email_subj" type="text" class="regular-text" name="sprr_winback_email_subject" 
						value="<?php echo esc_attr(stripslashes_deep(get_option('sprr_winback_email_subject', __('We Miss You! Special Offer Inside', 'subscriptions-renewal-reminders')))); ?>" 
						placeholder="<?php echo esc_attr__('We Miss You! Special Offer Inside', 'subscriptions-renewal-reminders'); ?>">
				</td>
			</tr>
		</table>
<?php
	}

	public function sprr_winbackContent()
	{
?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Available placeholders: {first_name}, {last_name}, {subscription_link}', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>
					<?php
					$default_content = stripslashes_deep(get_option('sprr_winback_email_content', ''));
					$editor_id = 'sprr_winback_content_editor';
					$arg = array(
						'textarea_name' => 'sprr_winback_email_content',
						'media_buttons' => true,
						'textarea_rows' => 10,
						'quicktags' => true,
						'wpautop' => false,
						'teeny' => true
					);

					$blank_content = __("Hi {first_name} {last_name},\n\nWe noticed your subscription has ended and we'd love to have you back!\n\nAs a valued customer, we're offering you an exclusive discount to reactivate your subscription.\n\nClick below to view your subscription and restart anytime:\n{subscription_link}\n\nWe hope to see you again soon!\n\nBest regards,\nThe Team", 'subscriptions-renewal-reminders');

					if (empty($default_content)) {
						$default_content = $blank_content;
					}

					wp_editor($default_content, $editor_id, $arg);
					?>

					<p style="margin-top:3px;"><strong><?php esc_html_e('Note:', 'subscriptions-renewal-reminders'); ?></strong></p>
					<ul style="margin-top:4px; font-size:12px;">
						<li>&#9830; <?php esc_html_e('Use {first_name} and {last_name} for customer name', 'subscriptions-renewal-reminders'); ?></li>
						<li>&#9830; <?php esc_html_e('Use {subscription_link} to add a link to their subscription', 'subscriptions-renewal-reminders'); ?></li>
						<li>&#9830; <?php esc_html_e('This email will be sent to customers with cancelled or expired subscriptions', 'subscriptions-renewal-reminders'); ?></li>
					</ul>
				</td>
			</tr>
		</table>
<?php
	}

	public function sprr_templateSelector()
	{
		// Migrate existing email_subject and email_content to default template if not already done
		$this->sprr_migrateToTemplateSystem();

		$selected_template = get_option('sprr_selected_template', 'default_template');
		$custom_templates = get_option('sprr_custom_templates', array());

		?>
		<table>
			<tr>
				<td>
					<div class="adm-tooltip-renew-rem" data-tooltip="<?php echo esc_attr__('Select the email template for renewal reminders', 'subscriptions-renewal-reminders'); ?>"> ? </div>
				</td>
				<td>
					<select name="sprr_selected_template" id="sprr_selected_template" style="min-width: 300px; padding: 8px;">
						<option value="default_template" <?php selected($selected_template, 'default_template'); ?>>
							<?php esc_html_e('Default Template', 'subscriptions-renewal-reminders'); ?>
						</option>
						<?php foreach ($custom_templates as $template_id => $template): ?>
							<option value="<?php echo esc_attr($template_id); ?>" <?php selected($selected_template, $template_id); ?>>
								<?php echo esc_html($template['name']); ?>
							</option>
						<?php endforeach; ?>
					</select>
					
					<a href="<?php echo admin_url('admin.php?page=sp-renewal-reminders-templates'); ?>" class="button" style="margin-left: 5px;">
						<?php esc_html_e('Manage Templates', 'subscriptions-renewal-reminders'); ?>
					</a>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="sprr-template-preview" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; display: none;">
						<h4 style="margin-top: 0;"><?php esc_html_e('Template Preview:', 'subscriptions-renewal-reminders'); ?></h4>
						<div id="sprr-template-preview-content"></div>
					</div>
					<p style="margin-top: 15px; font-size: 12px; color: #666; font-style: italic;">
						<span class="dashicons dashicons-info" style="font-size: 14px; vertical-align: middle;"></span>
						<?php esc_html_e('Template customization has been moved to the new Templates menu. Use the buttons above to create or manage your email templates.', 'subscriptions-renewal-reminders'); ?>
					</p>
				</td>
			</tr>
		</table>

		<script>
		jQuery(document).ready(function($) {
			var templates = <?php echo wp_json_encode($custom_templates); ?>;
			// Fallback to current subject/content for Default Template preview
			var defaultTemplate = <?php 
				$default_subject = stripslashes_deep(get_option('email_subject', get_email_subject_default_value()));
				$default_content = stripslashes_deep(get_option('email_content', ''));
				if (empty($default_content)) { $default_content = get_blank_content_reminder_text(); }
				$processed_content = $default_content;
				// Only apply wpautop if content has no HTML tags (plain text)
				if ($processed_content === wp_strip_all_tags($processed_content)) {
					$processed_content = wpautop($processed_content);
				}
				echo wp_json_encode(array('subject' => $default_subject, 'content' => $processed_content));
			?>;

			function showTemplatePreview() {
				var selectedId = $('#sprr_selected_template').val();
				var template = null;
				if (selectedId === 'default_template') {
					template = defaultTemplate;
				} else if (templates[selectedId]) {
					template = templates[selectedId];
				}

				if (template) {
					var previewHtml = '<p><strong><?php esc_html_e('Subject:', 'subscriptions-renewal-reminders'); ?></strong> ' + (template.subject || '') + '</p>';
					var contentHtml = (template.content || '');
					// If content appears to be plain text (no HTML tags), convert newlines to <br>
					if (!/<[a-z][\s\S]*>/i.test(contentHtml)) {
						contentHtml = contentHtml.replace(/\n/g, '<br>');
					}
					previewHtml += '<div style="background: #fff; padding: 15px; margin-top: 10px; max-height: 300px; overflow-y: auto;">' + contentHtml + '</div>';
					$('#sprr-template-preview-content').html(previewHtml);
					$('#sprr-template-preview').slideDown();
				} else {
					$('#sprr-template-preview').slideUp();
				}
			}

			$('#sprr_selected_template').on('change', showTemplatePreview);
			
			// Show preview on page load if template is selected
			if ($('#sprr_selected_template').val()) {
				showTemplatePreview();
			}
		});
		</script>
		<?php
	}

	private function sprr_migrateToTemplateSystem()
	{
		// Check if migration has already been done
		if (get_option('sprr_template_migration_done', false)) {
			return;
		}

		$existing_subject = get_option('email_subject', '');
		$existing_content = get_option('email_content', '');

		// Only create default template if there's existing data
		if (!empty($existing_subject) || !empty($existing_content)) {
			$custom_templates = get_option('sprr_custom_templates', array());
			
			// Create default template from existing settings
			$default_template_id = 'default_template';
			
			if (!isset($custom_templates[$default_template_id])) {
				$custom_templates[$default_template_id] = array(
					'id' => $default_template_id,
					'name' => __('Default Template', 'subscriptions-renewal-reminders'),
					'subject' => $existing_subject,
					'content' => $existing_content,
					'created' => current_time('mysql'),
					'modified' => current_time('mysql'),
				);
				
				update_option('sprr_custom_templates', $custom_templates);
			}
			
			// Set the default template as selected
			if (!get_option('sprr_selected_template')) {
				update_option('sprr_selected_template', $default_template_id);
			}
		}

		// Mark migration as done
		update_option('sprr_template_migration_done', true);
	}
}

