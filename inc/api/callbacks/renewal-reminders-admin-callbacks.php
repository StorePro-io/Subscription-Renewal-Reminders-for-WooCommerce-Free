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
}
