<?php
/**
 * Help Page Template for Subscriptions Renewal Reminders
 */
?>
<div class="wrap renewal-reminder-plugin">
  <div style="text-align: center; margin: 30px 0;">
    <h1 class="renew-rem-makin-title">Subscriptions Renewal Reminders — Help</h1>
    <p class="renew-rem-subtitle">Overview, how it works, and quick links for review and support</p>
  </div>

  <div style="display:grid; grid-template-columns: 1fr; gap: 16px;">
    <div style="background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:16px;">
      <h2 style="margin-top:0;">What this plugin does</h2>
      <p>
        This plugin automatically sends renewal reminder emails to your WooCommerce Subscriptions customers a configurable number of days before their next payment date. It helps reduce surprise renewals and churn by keeping customers informed.
      </p>

      <h3>How it works</h3>
      <ul style="margin:0; padding-left:18px;">
        <li><strong>Sync:</strong> The plugin periodically synchronizes active subscriptions into its own table for quick processing.</li>
        <li><strong>Schedule:</strong> A daily cron runs at your configured time (UTC) to send reminders to customers approaching renewal.</li>
        <li><strong>Templates:</strong> Choose a default template or build custom ones in the Templates section. Free version includes two standard templates.</li>
        <li><strong>Shortcodes:</strong> Use placeholders such as {first_name}, {last_name}, {next_payment_date}, {cancel_subscription} in your email content.</li>
        <li><strong>Sending:</strong> Emails use WordPress/WooCommerce mail settings; subject and content come from the selected template or saved content options.</li>
      </ul>

      <h3>Key areas</h3>
      <ul style="margin:0; padding-left:18px;">
        <li><strong>Settings:</strong> Configure enable/disable, days before reminder, and send time.</li>
        <li><strong>Templates:</strong> Browse library, create/edit custom templates with the Email Builder, and manage your saved templates.</li>
        <li><strong>Sync:</strong> Run manual sync when you change subscription statuses or plugin settings to update the database.</li>
        <li><strong>Email Testing (Pro / override available):</strong> Run an immediate send to verify. See Testing tab for enable instructions.</li>
        <li><strong>Email History (Pro):</strong> View logs of sent/failed counts and retained estimates.</li>
      </ul>

      <h3>Shortcodes reference</h3>
      <ul style="margin:0; padding-left:18px;">
        <li>{first_name} — Customer’s first name</li>
        <li>{last_name} — Customer’s last name</li>
        <li>{next_payment_date} — Upcoming renewal date</li>
        <li>{cancel_subscription} — Adds a subscription cancel button (if enabled)</li>
      </ul>

      <h3>Requirements</h3>
      <ul style="margin:0; padding-left:18px;">
        <li>WooCommerce and WooCommerce Subscriptions must be active</li>
        <li>Ensure WP Cron is working; set send time (UTC) in Settings</li>
      </ul>
    </div>

    <div style="background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:16px;">
      <h2 style="margin-top:0;">Review & Support</h2>
      <p>We’d love your feedback and are here to help.</p>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="https://wordpress.org/plugins/subscriptions-renewal-reminders/#reviews" target="_blank" class="button button-primary">Leave a Review</a>
        <a href="https://storepro.io/contact/" target="_blank" class="button">Get Support</a>
      </div>
      <p style="margin-top:10px; color:#555;">For premium features and priority support, see the Upgrade link in the menu.</p>
    </div>

    <div style="background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:16px;">
      <h2 style="margin-top:0;">Troubleshooting</h2>
      <ul style="margin:0; padding-left:18px;">
        <li><strong>No emails:</strong> Check Settings → enable is ON, cron time set, and WP Cron working.</li>
        <li><strong>Empty subject/content:</strong> Ensure a template is selected in Settings or content is saved; we also apply safe fallbacks.</li>
        <li><strong>Preview issues:</strong> Use Templates → Library/Builder. In Settings, the preview shows selected/default template; plain text is formatted automatically.</li>
        <li><strong>History disabled:</strong> Email History is Pro-only. The tab shows a preview and upgrade CTA for free users.</li>
      </ul>
    </div>
  </div>

  <div style="text-align:center; margin-top:24px;">
    <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary sprr-upgrade-btn">Upgrade to Pro</a>
  </div>
</div>
