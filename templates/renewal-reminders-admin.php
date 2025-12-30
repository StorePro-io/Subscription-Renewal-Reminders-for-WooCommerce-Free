<?php



//Get the active tab from the $_GET param
$default_tab = 'settings';

//Get sanitization
global $pagenow;

$sp_tab = "";
if (isset($_GET['tab'])) {
  $sp_tab = filter_input(INPUT_POST | INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS);
}
$tab = isset($sp_tab) && !empty($sp_tab) ? $sp_tab : $default_tab;



global $wpdb;
$table_name = $wpdb->prefix . "renewal_reminders";
$charset_collate = $wpdb->get_charset_collate();


?>
<!-- Our admin page content should all be inside .wrap -->
<div class="wrapper renewal-reminder-plugin">
  <!-- Add a review notice  -->

  <?php
  $dismissed_key = 'disable-sp-renewal-notice-forever';
  $is_dismissed = get_user_meta(get_current_user_id(), $dismissed_key, true);

  // Check if the cookie is present
  $banner_closed = isset($_COOKIE['bannerClosed']) && $_COOKIE['bannerClosed'] === 'true';

  if (!$is_dismissed && !$banner_closed) {
  ?>

    <div class="sp-review">
      <div id="sp-notice-settings" class="sp-notice notice notice-success is-dismissible" data-dismissible="disable-done-notice-forever">
        <div style="padding: 5px 0;">
          <strong style="color: #2c3e50; font-size: 15px;">
            <?php esc_html_e('🎉 Hey There! If you like our plugin Subscription Renewal Reminders don\'t forget to rate and leave a Review', 'renewal-reminders-sp'); ?>
          </strong><br>
          <p style="margin: 10px 0; line-height: 1.6;">
            <?php esc_html_e('We\'d love to hear your feedback!', 'renewal-reminders-sp'); ?>
            <a href="https://wordpress.org/plugins/subscriptions-renewal-reminders/#reviews" target="_blank" class="dismiss-this" style="color: #667eea; font-weight: 600; text-decoration: none;">
              <?php esc_html_e('Please leave us a review.', 'renewal-reminders-sp'); ?>
            </a>
            <?php _e(' Enjoying our plugin? Please rate it!', 'my-plugin-textdomain');
            $plugin_slug = 'subscriptions-renewal-reminders';

            // Make a request to the WordPress.org Plugin API
            $response = wp_remote_get("https://api.wordpress.org/plugins/info/1.0/{$plugin_slug}.json");

            if (is_wp_error($response)) {
              echo "Error fetching plugin information.";
            } else {
              $body = wp_remote_retrieve_body($response);
              $data = json_decode($body);

              if ($data && isset($data->rating)) {
                $rating = $data->rating;

                // Ensure $rating is within the valid range of 0 to 5
                $rating = max(0, min(5, $rating));

                // Convert the numeric rating to star representation using HTML entities
                $star_rating = str_repeat('&#9733;', $rating) . str_repeat('&#9734;', 5 - $rating);

                echo " We have a <span class=\"sp-star-rating\"> {$star_rating} </span> by users across the globe.";
              } else {
                echo "Plugin not found or rating information not available.";
              }
            }
            ?>
          </p>
        </div>
        
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            var spNotice = document.getElementById('sp-notice-settings');
            if (spNotice) {
              spNotice.addEventListener('click', function(event) {
                if (event.target.classList.contains('notice-dismiss')) {
                  console.log('Dismiss button clicked');
                  document.cookie = 'bannerClosed=true; expires=Thu, 01 Jan 2030 00:00:00 UTC; path=/;';
                  console.log('Cookie set: ' + document.cookie);
                  spNotice.remove(); // Remove the notice from the DOM
                }
              });
            } else {
              console.error('#sp-notice element not found');
            }
          });
        </script>
      </div>
    </div>

    <?php
  } ?>

  <!-- Print the page title with enhanced styling -->
  <div style="text-align: center; margin: 30px 0;">
    <h1 class="renew-rem-makin-title"> 
      <?php echo esc_html__('Subscriptions Renewal Reminders', 'subscriptions-renewal-reminders'); ?>
    </h1>
    <p class="renew-rem-subtitle">
      <?php echo esc_html__('Automate your subscription renewal notifications with ease', 'subscriptions-renewal-reminders'); ?>
    </p>
  </div>
  
  <?php settings_errors(); ?>

  <!-- Enhanced advertisement for storepro -->
  <div class="sp-ad">
      <div class="sp-header">
          <div class="logo-container">
              <a href="http://storepro.io/" target="_blank">
                  <img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/storepro.jpg" alt="StorePro">
              </a>
          </div>
          <button type="button" class="notice-dismiss sp-ad-dismiss" title="<?php echo esc_attr__('Dismiss this notice', 'subscriptions-renewal-reminders'); ?>">
              <span class="screen-reader-text">
                  <?php echo esc_html__('Dismiss this notice.', 'subscriptions-renewal-reminders'); ?>
              </span>
          </button>
      </div>

      <div class="sp-col-12 sp-ad-content">
        <div>
          <h2  class="sp-ad-title-typer">
              <?php echo esc_html__('🚀 Discover the superpower of having your own development team on call for your website','subscriptions-renewal-reminders'); ?>
          </h2>
          <p class="sp-ad-text-typer">
              <?php echo esc_html__('We help online businesses like yours grow faster, experiment easier and solve technical challenges without the stress and wasted time. Get in touch today for a quick chat to see how we can help you.','subscriptions-renewal-reminders'); ?>
          </p>
        </div>
          
          <div class="sp-pricing-footer">
              <a href="https://calendly.com/storepro" target="_blank" class="pricing-button">
                  <?php echo esc_html__('💬 Talk to Us', 'subscriptions-renewal-reminders'); ?>
              </a>
          </div>
      </div>
  </div>


  <!-- Enhanced navigation tabs -->
  <nav class="nav-tab-wrapper">
      <a href="?page=sp-renewal-reminders&tab=settings" class="nav-tab <?php if ($tab === 'settings'): ?>nav-tab-active<?php endif; ?>">
          ⚙️ <?php echo esc_html__('Settings', 'subscriptions-renewal-reminders'); ?>
      </a>
      <a href="?page=sp-renewal-reminders&tab=sync" class="nav-tab <?php if ($tab === 'sync'): ?>nav-tab-active<?php endif; ?>">
          🔄 <?php echo esc_html__('Sync', 'subscriptions-renewal-reminders'); ?>
      </a>
      <a href="?page=sp-renewal-reminders&tab=testing" class="nav-tab <?php if ($tab === 'testing'): ?>nav-tab-active<?php endif; ?>">
        🧪 <?php echo esc_html__('Email Testing', 'subscriptions-renewal-reminders'); ?>
      </a>
      <a href="?page=sp-renewal-reminders&tab=history" class="nav-tab <?php if ($tab === 'history'): ?>nav-tab-active<?php endif; ?>">
        🧾 <?php echo esc_html__('Email History', 'subscriptions-renewal-reminders'); ?>
      </a>
  </nav>

  <div class="renew-rem-tab-content">
    <?php
    switch ($tab):
      case 'settings':
    ?>
        <div class="tab-content-inner">
          <form method="post" action="options.php">
            <?php
            settings_fields('storepro_options_group');
            do_settings_sections('storepro_plugin');
            submit_button();
            ?>
          </form>
        </div>
      <?php
        break;
      case 'sync':
      ?>
        <div class="tab-content-inner">
          <div class="re-compare-bar-tabs-sync">
            <h3 style="color: #2c3e50; margin-bottom: 10px;">🔄 Manual Synchronization</h3>
            <p><?php echo esc_html__('Synchronize Subscription data to Renewal Reminders Plugin manually here! This will update your database with the latest subscription information.', 'subscriptions-renewal-reminders'); ?></p>
          </div>
          
          <div class="renew-rem-progress"></div>
          
          <div class="renew-rem-button-sect-default">
            <button class="button-primary" id="renew-defload" style="font-size: 14px;">
              🔄 <?php echo esc_html__('Start Manual Sync', 'subscriptions-renewal-reminders'); ?>
            </button>
          </div>
        </div>
        <?php
        break;

      case 'testing':
      ?>
        <div class="tab-content-inner" style="padding-bottom: 48px;">
          <div class="re-compare-bar-tabs-sync">
            <h3 style="color: #2c3e50; margin-bottom: 10px;">🧪 Email Testing</h3>
            <p><?php echo esc_html__('Trigger today\'s renewal reminder emails immediately to verify sending works as expected.', 'subscriptions-renewal-reminders'); ?></p>
          </div>
          <?php 
            $testing_enabled = (function_exists('sprr_is_premium_active') && sprr_is_premium_active());
            if (!$testing_enabled) {
              // Allow temporary override via constant, filter, or option
              $const_enabled = defined('SPRR_TESTING_ENABLED') ? (bool) constant('SPRR_TESTING_ENABLED') : false;
              $testing_enabled = $const_enabled
                                || (bool) apply_filters('sprr_testing_enabled', false)
                                || (bool) get_option('sprr_testing_enabled', false);
            }
          ?>
          <?php if ($testing_enabled): ?>
            <div>
              <button class="button" id="sprr-send-renewals-now" style="font-size: 14px;">
                ✉️ <?php echo esc_html__('Send Renewal Emails Now', 'subscriptions-renewal-reminders'); ?>
              </button>
              <span id="sprr-send-result" style="margin-left: 10px; color: #555;"></span>
            </div>
            <script>
              jQuery(document).ready(function($){
                $('#sprr-send-renewals-now').on('click', function(){
                  var $btn = $(this);
                  $btn.prop('disabled', true).text('Sending...');
                  $('#sprr-send-result').text('');
                  $.post(ajaxurl, {
                    action: 'sprr_run_renewal_sends_now',
                    nonce: '<?php echo wp_create_nonce('sprr_run_now'); ?>'
                  }).done(function(resp){
                    if (resp && resp.success) {
                      var sent = resp.data.sent || 0;
                      var failed = resp.data.failed || 0;
                      $('#sprr-send-result').text('Sent: ' + sent + ' • Failed: ' + failed);
                    } else {
                      $('#sprr-send-result').text('Error: ' + (resp && resp.data ? resp.data : 'Unknown error'));
                    }
                  }).fail(function(){
                    $('#sprr-send-result').text('Network error while sending.');
                  }).always(function(){
                    $btn.prop('disabled', false).text('✉️ <?php echo esc_js(__('Send Renewal Emails Now', 'subscriptions-renewal-reminders')); ?>');
                  });
                });
              });
            </script>
          <?php else: ?>
            <div style="position: relative;">
              <div style="filter: blur(3px); pointer-events: none;">
                <div>
                  <button class="button" style="font-size: 14px;" disabled>
                    ✉️ <?php echo esc_html__('Send Renewal Emails Now', 'subscriptions-renewal-reminders'); ?>
                  </button>
                  <span style="margin-left: 10px; color: #555;">Sent: 0 • Failed: 0</span>
                </div>
              </div>
              <div style="position: absolute; inset: 0; display:flex; align-items:center; justify-content:center;">
                <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.08); text-align:center; max-width: 520px;">
                  <h4 style="margin:0 0 8px;">Email Testing is a Pro feature</h4>
                  <p style="margin:0 0 12px; color:#555;">Upgrade to run tests instantly and verify deliverability.</p>
                  <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary sprr-upgrade-btn">Upgrade to Pro</a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php
        break;

      case 'history':
      ?>
        <div class="tab-content-inner">
          <h3 style="color:#2c3e50; margin-bottom: 15px;"><?php echo esc_html__('Email History', 'subscriptions-renewal-reminders'); ?></h3>
          <?php if (function_exists('sprr_is_premium_active') && sprr_is_premium_active()): ?>
            <?php $history = get_option('sprr_email_history', array()); ?>
            <?php if (empty($history)): ?>
              <div style="text-align:center; padding:40px; color:#666; background:#fff; border:1px solid #ddd; border-radius:6px;">
                <span class="dashicons dashicons-email" style="font-size:48px; opacity:0.3;"></span>
                <p style="font-size:16px; margin-top:10px;"><?php echo esc_html__('No email history found yet.', 'subscriptions-renewal-reminders'); ?></p>
              </div>
            <?php else: ?>
              <table class="wp-list-table widefat fixed striped">
                <thead>
                  <tr>
                    <th><?php echo esc_html__('Date', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Type', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Subject', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Template', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Sent', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Failed', 'subscriptions-renewal-reminders'); ?></th>
                    <th><?php echo esc_html__('Retained', 'subscriptions-renewal-reminders'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (array_reverse($history) as $item): ?>
                    <tr>
                      <td><?php echo esc_html(date('M j, Y H:i', strtotime($item['timestamp']))); ?></td>
                      <td><?php echo esc_html(ucfirst($item['type'])); ?></td>
                      <td><?php echo esc_html($item['subject']); ?></td>
                      <td><?php echo esc_html($item['template']); ?></td>
                      <td><?php echo isset($item['sent']) ? intval($item['sent']) : 0; ?></td>
                      <td><?php echo isset($item['failed']) ? intval($item['failed']) : 0; ?></td>
                      <td><?php echo isset($item['retained']) ? intval($item['retained']) : 0; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          <?php else: ?>
            <div style="position: relative;">
              <div style="filter: blur(3px); pointer-events: none;">
                <table class="wp-list-table widefat fixed striped">
                  <thead>
                    <tr>
                      <th><?php echo esc_html__('Date', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Type', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Subject', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Template', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Sent', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Failed', 'subscriptions-renewal-reminders'); ?></th>
                      <th><?php echo esc_html__('Retained', 'subscriptions-renewal-reminders'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($i=0; $i<5; $i++): ?>
                      <tr>
                        <td><?php echo esc_html(date('M j, Y H:i', current_time('timestamp') - ($i*3600))); ?></td>
                        <td>Renewal</td>
                        <td>Heads up: Your renewal is near</td>
                        <td>Modern Hero</td>
                        <td><?php echo 10 - $i; ?></td>
                        <td><?php echo $i % 2; ?></td>
                        <td><?php echo $i; ?></td>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>
              </div>
              <div style="position: absolute; inset: 0; display:flex; align-items:center; justify-content:center;">
                <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.08); text-align:center; max-width: 480px;">
                  <h4 style="margin:0 0 8px;">Email History is a Pro feature</h4>
                  <p style="margin:0 0 12px; color:#555;">Upgrade to view detailed email send logs, retention metrics, and performance insights.</p>
                  <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary sprr-upgrade-btn">Upgrade to Pro</a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php
        break;

      default:
        //check if there is any data in the table
        global $wpdb;
        $renew_table_name = $wpdb->prefix . "renewal_reminders";
        $renew_count_query = "select count(*) from $renew_table_name";
        $renew_num = $wpdb->get_var($renew_count_query);

        if ((int)$renew_num == 0) {
        ?>
          <div class="renew-main-sync-box">
            <div style="text-align: center; margin-bottom: 20px;">
              <div style="font-size: 48px; margin-bottom: 15px;">🚀</div>
              <h3 style="color: #2c3e50; margin-bottom: 15px;">Welcome to Subscription Renewal Reminders!</h3>
            </div>
            
            <div class="re-compare-bar-tabs">
              <?php echo esc_html__('Let\'s get started by synchronizing your subscription data for the first time. This will set up everything you need to send automated renewal reminders to your customers.', 'subscriptions-renewal-reminders'); ?>
            </div>
            
            <div class="renew-rem-button-sect">
              <button class="renew-firstload" id="ren-spin-ajax">
                🔄 <?php echo esc_html__('Start Initial Synchronization', 'subscriptions-renewal-reminders'); ?>
              </button>
            </div>

            <div class="renew-text">
              <div style="background: rgba(102, 126, 234, 0.1); padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
                <strong>💡 <?php echo esc_html__('Quick Start:', 'subscriptions-renewal-reminders'); ?></strong><br>
                <?php echo esc_html__('Click the button above to import your existing subscriptions. You can also configure your email templates and settings at any time using the tabs above.', 'subscriptions-renewal-reminders'); ?>
              </div>
            </div>
          </div>
        <?php
        } else {
          // Redirect browser
          global $wp;
          $sp_page = "";
          if (isset($_GET['page'])) {
            $sp_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);
          }
          $ren_current_url = admin_url("admin.php?page=" . $sp_page) . "&tab=settings";
          echo '<script type="text/javascript">window.location.href = "' . esc_url_raw($ren_current_url) . '";</script>';
          exit;
        }
        ?>
    <?php
        break;
    endswitch; ?>
  </div>

  <!-- Enhanced premium section -->
  <div class="sp-renewal-pro">
    <div class="premium-links">
      <div style="text-align: center; margin-bottom: 25px;">
        <h3 style="color: #2c3e50; font-size: 24px; margin-bottom: 10px;">
          ✨ <?php echo esc_html__('Upgrade to Premium', 'subscriptions-renewal-reminders'); ?>
        </h3>
        <p style="color: #7f8c8d; font-size: 16px;">
          <?php echo esc_html__('Unlock advanced features and take your renewal reminders to the next level', 'subscriptions-renewal-reminders'); ?>
        </p>
      </div>
      
      <div class="screenshots">
    <div class="column">
      <div class="img-card">
        <a href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/settings.webp"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/settings.webp" /></a>
      </div>
      <p style="text-align: center;font-size: 16px;font-weight: 600;color: #666565db;margin-top: 0 !important;"><?php echo esc_html__('Settings', 'subscriptions-renewal-reminders'); ?></p>
    </div>
    <div class="column">
      <div class="img-card">
        <a href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/email-settings.webp"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/email-settings.webp" /></a>
      </div>
      <p style="text-align: center;font-size: 16px;font-weight: 600;color: #666565db;margin-top: 0 !important;"><?php echo esc_html__('Email Settings', 'subscriptions-renewal-reminders'); ?> </p>
    </div>
    <div class="column">
      <div class="img-card">
        <a href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/admin-email-settings.webp"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/admin-email-settings.webp" /></a>
      </div>
      <p style="text-align: center;font-size: 16px;font-weight: 600;color: #666565db;margin-top: 0 !important;"><?php echo esc_html__('Admin Email Settings ', 'subscriptions-renewal-reminders'); ?> </p>
    </div>
    <div class="column">
      <div class="img-card">
        <a href="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/test-email.webp"><img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/test-email.webp" /></a>
      </div>
      <p style="text-align: center;font-size: 16px;font-weight: 600;color: #666565db;margin-top: 0 !important;"><?php echo esc_html__('Test Mail', 'subscriptions-renewal-reminders'); ?> </p>
    </div>
  </div>
  <div class="premium-features">
    <p><?php echo esc_html__('PRO Features:', 'subscriptions-renewal-reminders'); ?></p>
    <ul>
      <li><?php echo esc_html__('Multi-Interval Reminders: Send multiple renewal reminders (e.g., 14 days and 3 days before renewal) to boost engagement and reduce unexpected cancellations.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('Compatibility with synchronized subscriptions.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('The ability to choose the type of subscription period renewal reminder emails are sent to. This is useful for websites with mixed subscription periods, as you can avoid sending renewal reminders for subscriptions that don’t actually need them.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('Renewal period can be chosen from the available options which is daily, weekly, monthly or yearly.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('The ability to change the from email address and the sender’s name for renewal reminder emails.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('Additional shortcodes are included for email templates, such as the total amount, subscription link, and my account link.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('An email test feature has been included.', 'subscriptions-renewal-reminders'); ?> </li>
      <li><?php echo esc_html__('Additional filters that allow you to expand the plugin’s functionality and customize email templates. You can also use these filters to modify the subscription period.', 'subscriptions-renewal-reminders'); ?></li>
      <li><?php echo esc_html__('The ability to include a "Send Email to Admin" button in emails and modify the "From" email address and sender’s name for renewal reminder emails.', 'subscriptions-renewal-reminders'); ?></li>
      <li><?php echo esc_html__('The option to add a "Cancel Subscription" button in emails, enabling subscribers to conveniently manage their subscriptions by canceling them directly from the email without visiting their account page.', 'subscriptions-renewal-reminders'); ?></li>
    </ul>
    <div class="button-upgrade"><a href="https://storepro.io/product/?add-to-cart=14883" target="_blank" class="button sprr-upgrade-btn" style="text-decoration: none;"><?php echo esc_html__('Upgrade to Pro Version Now', 'subscriptions-renewal-reminders'); ?></a>
    </div>
  </div>

    </div>
    
    <span class="renew-rem-by-text">
      <a href="http://storepro.io/" target="_blank"> 
        <img src="<?php echo esc_url(plugin_dir_url(__FILE__)); ?>img/storepro-logo.png" alt="StorePro" style="border-radius: 5px;">
      </a>
    </span>
  </div>
</div>