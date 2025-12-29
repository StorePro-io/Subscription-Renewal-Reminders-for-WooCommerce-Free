<?php
/**
 * Marketing Dashboard Template
 * 
 * @package RenewalReminders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get filter parameters
$status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : 'all';
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Get ALL subscriptions for counting (without filter)
$all_subscriptions = wcs_get_subscriptions(array(
    'subscriptions_per_page' => -1,
    'orderby' => 'start_date',
    'order' => 'DESC',
));

// Count by status - count all subscriptions first
$status_counts = array(
    'all' => 0,
    'active' => 0,
    'cancelled' => 0,
    'expired' => 0,
    'on-hold' => 0,
    'pending-cancel' => 0,
);

foreach ($all_subscriptions as $sub) {
    $status_counts['all']++;
    $status = $sub->get_status();
    if (isset($status_counts[$status])) {
        $status_counts[$status]++;
    }
}

// Now get filtered subscriptions for display
$args = array(
    'subscriptions_per_page' => -1,
    'orderby' => 'start_date',
    'order' => 'DESC',
);

if ($status_filter !== 'all') {
    $args['subscription_status'] = $status_filter;
}

$subscriptions = wcs_get_subscriptions($args);

// Prepare data for table
$subscribers_data = array();
foreach ($subscriptions as $subscription) {
    $user_id = $subscription->get_user_id();
    $user = get_userdata($user_id);
    
    if (!$user) continue;
    
    $email = $user->user_email;
    $first_name = $subscription->get_billing_first_name();
    $last_name = $subscription->get_billing_last_name();
    $status = $subscription->get_status();
    $next_payment = $subscription->get_date('next_payment');
    $total = $subscription->get_total();
    
    // Apply search filter
    if ($search_query && 
        stripos($email, $search_query) === false && 
        stripos($first_name, $search_query) === false && 
        stripos($last_name, $search_query) === false) {
        continue;
    }
    
    $subscribers_data[] = array(
        'subscription_id' => $subscription->get_id(),
        'user_id' => $user_id,
        'email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'status' => $status,
        'next_payment' => $next_payment,
        'total' => $total,
        'currency' => $subscription->get_currency(),
    );
}
?>

<div class="wrap renewal-reminders-marketing">
    <!-- Print the page title with enhanced styling -->
    <div style="text-align: center; margin: 30px 0;">
        <h1 class="renew-rem-makin-title"> 
            <?php echo esc_html__('📊 Marketing Dashboard', 'subscriptions-renewal-reminders'); ?>
        </h1>
        <p class="renew-rem-subtitle">
            <?php echo esc_html__('View all subscribers, track subscription status, and send targeted win-back campaigns to cancelled customers', 'subscriptions-renewal-reminders'); ?>
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
                <h2 class="sp-ad-title-typer">
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

    <?php
    // Get active tab
    $default_tab = 'subscribers';
    $marketing_tab = isset($_GET['marketing_tab']) ? sanitize_text_field($_GET['marketing_tab']) : $default_tab;
    ?>

    <!-- Main Navigation Tabs -->
    <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
        <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers" 
           class="nav-tab <?php echo $marketing_tab === 'subscribers' ? 'nav-tab-active' : ''; ?>">
            👥 <?php echo esc_html__('Subscribers', 'subscriptions-renewal-reminders'); ?>
        </a>
        <a href="?page=sp-renewal-reminders-marketing&marketing_tab=automation" 
           class="nav-tab <?php echo $marketing_tab === 'automation' ? 'nav-tab-active' : ''; ?>">
            🤖 <?php echo esc_html__('Automation', 'subscriptions-renewal-reminders'); ?>
        </a>
        <a href="?page=sp-renewal-reminders-marketing&marketing_tab=history" 
           class="nav-tab <?php echo $marketing_tab === 'history' ? 'nav-tab-active' : ''; ?>">
            📧 <?php echo esc_html__('Email History', 'subscriptions-renewal-reminders'); ?>
        </a>
    </nav>

    <?php if ($marketing_tab === 'subscribers'): ?>
    <!-- Subscribers Tab Content -->

    <!-- Status Filter Tabs -->
    <div class="wp-filter" style="margin-bottom: 20px;">
        <ul class="filter-links">
            <li>
                <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers&status_filter=all" 
                   class="<?php echo $status_filter === 'all' ? 'current' : ''; ?>">
                    <?php echo esc_html__('All', 'subscriptions-renewal-reminders'); ?> 
                    <span class="count">(<?php echo esc_html($status_counts['all']); ?>)</span>
                </a>
            </li>
            <li>
                <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers&status_filter=active" 
                   class="<?php echo $status_filter === 'active' ? 'current' : ''; ?>">
                    <?php echo esc_html__('Active', 'subscriptions-renewal-reminders'); ?> 
                    <span class="count">(<?php echo esc_html($status_counts['active']); ?>)</span>
                </a>
            </li>
            <li>
                <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers&status_filter=cancelled" 
                   class="<?php echo $status_filter === 'cancelled' ? 'current' : ''; ?>">
                    <?php echo esc_html__('Cancelled', 'subscriptions-renewal-reminders'); ?> 
                    <span class="count">(<?php echo esc_html($status_counts['cancelled']); ?>)</span>
                </a>
            </li>
            <li>
                <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers&status_filter=expired" 
                   class="<?php echo $status_filter === 'expired' ? 'current' : ''; ?>">
                    <?php echo esc_html__('Expired', 'subscriptions-renewal-reminders'); ?> 
                    <span class="count">(<?php echo esc_html($status_counts['expired']); ?>)</span>
                </a>
            </li>
            <li>
                <a href="?page=sp-renewal-reminders-marketing&marketing_tab=subscribers&status_filter=on-hold" 
                   class="<?php echo $status_filter === 'on-hold' ? 'current' : ''; ?>">
                    <?php echo esc_html__('On Hold', 'subscriptions-renewal-reminders'); ?> 
                    <span class="count">(<?php echo esc_html($status_counts['on-hold']); ?>)</span>
                </a>
            </li>
        </ul>
        
        <form method="get" class="search-form">
            <input type="hidden" name="page" value="sp-renewal-reminders-marketing">
            <input type="hidden" name="marketing_tab" value="subscribers">
            <input type="hidden" name="status_filter" value="<?php echo esc_attr($status_filter); ?>">
            <input type="search" name="search" value="<?php echo esc_attr($search_query); ?>" 
                   placeholder="<?php echo esc_attr__('Search subscribers...', 'subscriptions-renewal-reminders'); ?>">
            <button type="submit" class="button"><?php echo esc_html__('Search', 'subscriptions-renewal-reminders'); ?></button>
        </form>
    </div>

    <!-- Bulk Actions for Cancelled Subscriptions -->
    <?php if ($status_filter === 'cancelled' || $status_filter === 'expired'): ?>
    <div class="tablenav top" style="margin-bottom: 15px;">
        <div class="alignleft actions">
            <button type="button" id="send-winback-email" class="button button-primary">
                <span class="dashicons dashicons-email" style="margin-top: 3px;"></span>
                <?php echo esc_html__('Send Win-Back Email to Selected', 'subscriptions-renewal-reminders'); ?>
            </button>
            <button type="button" id="select-all-subscribers" class="button">
                <?php echo esc_html__('Select All', 'subscriptions-renewal-reminders'); ?>
            </button>
            <button type="button" id="deselect-all-subscribers" class="button">
                <?php echo esc_html__('Deselect All', 'subscriptions-renewal-reminders'); ?>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Subscribers Table -->
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <?php if ($status_filter === 'cancelled' || $status_filter === 'expired'): ?>
                <td class="check-column">
                    <input type="checkbox" id="select-all-checkbox">
                </td>
                <?php endif; ?>
                <th><?php echo esc_html__('Subscription ID', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Customer Name', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Email', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Status', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Total', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Next Payment', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Actions', 'subscriptions-renewal-reminders'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($subscribers_data)): ?>
                <?php foreach ($subscribers_data as $subscriber): ?>
                <tr>
                    <?php if ($status_filter === 'cancelled' || $status_filter === 'expired'): ?>
                    <th class="check-column">
                        <input type="checkbox" class="subscriber-checkbox" 
                               data-email="<?php echo esc_attr($subscriber['email']); ?>"
                               data-first-name="<?php echo esc_attr($subscriber['first_name']); ?>"
                               data-last-name="<?php echo esc_attr($subscriber['last_name']); ?>"
                               data-subscription-id="<?php echo esc_attr($subscriber['subscription_id']); ?>">
                    </th>
                    <?php endif; ?>
                    <td><strong>#<?php echo esc_html($subscriber['subscription_id']); ?></strong></td>
                    <td><?php echo esc_html($subscriber['first_name'] . ' ' . $subscriber['last_name']); ?></td>
                    <td><?php echo esc_html($subscriber['email']); ?></td>
                    <td>
                        <span class="subscription-status status-<?php echo esc_attr($subscriber['status']); ?>">
                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $subscriber['status']))); ?>
                        </span>
                    </td>
                    <td><?php echo wc_price($subscriber['total'], array('currency' => $subscriber['currency'])); ?></td>
                    <td><?php echo $subscriber['next_payment'] ? esc_html(date('M d, Y', strtotime($subscriber['next_payment']))) : '—'; ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $subscriber['subscription_id'] . '&action=edit')); ?>" 
                           class="button button-small">
                            <?php echo esc_html__('View', 'subscriptions-renewal-reminders'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo ($status_filter === 'cancelled' || $status_filter === 'expired') ? '8' : '7'; ?>" 
                        style="text-align: center; padding: 40px;">
                        <span class="dashicons dashicons-info" style="font-size: 48px; opacity: 0.3;"></span>
                        <p><?php echo esc_html__('No subscribers found.', 'subscriptions-renewal-reminders'); ?></p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php elseif ($marketing_tab === 'automation'): ?>
    <!-- Automation Tab Content -->
    <?php require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-automation.php'; ?>

    <?php elseif ($marketing_tab === 'history'): ?>
    <!-- Email History Tab Content -->
    <?php include plugin_dir_path(__FILE__) . 'renewal-reminders-history.php'; ?>

    <?php endif; ?>

</div>

<style>
.renewal-reminders-marketing .subscription-status {
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.subscription-status.status-active {
    background: #d4edda;
    color: #155724;
}
.subscription-status.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}
.subscription-status.status-expired {
    background: #fff3cd;
    color: #856404;
}
.subscription-status.status-on-hold {
    background: #d1ecf1;
    color: #0c5460;
}
.subscription-status.status-pending-cancel {
    background: #ffeaa7;
    color: #d63031;
}
.wp-filter {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
}
.filter-links {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    flex: 1;
}
.filter-links li {
    margin: 0;
}
.filter-links a {
    display: block;
    padding: 12px 15px;
    text-decoration: none;
    border-bottom: 4px solid transparent;
    color: #646970;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.filter-links a.current {
    border-bottom-color: #2271b1;
    color: #2271b1;
    font-weight: 600;
}
.filter-links a:hover {
    color: #2271b1;
    background: rgba(34, 113, 177, 0.05);
}
.filter-links .count {
    background: #f0f0f1;
    color: #50575e;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 4px;
}
.filter-links a.current .count {
    background: #2271b1;
    color: #fff;
}
.search-form {
    padding: 8px 12px;
    flex-shrink: 0;
    display: flex;
    gap: 8px;
    align-items: center;
}
.search-form input[type="search"] {
    padding: 6px 10px;
    width: 200px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    font-size: 13px;
}
.search-form button {
    height: 30px;
    padding: 0 12px;
    line-height: 28px;
}
@media screen and (max-width: 782px) {
    .wp-filter {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-links {
        overflow-x: auto;
        flex-wrap: nowrap;
    }
    .search-form {
        border-top: 1px solid #dcdcde;
        justify-content: stretch;
    }
    .search-form input[type="search"] {
        flex: 1;
        width: auto;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select All functionality
    $('#select-all-checkbox').on('change', function() {
        $('.subscriber-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    $('#select-all-subscribers').on('click', function() {
        $('.subscriber-checkbox').prop('checked', true);
        $('#select-all-checkbox').prop('checked', true);
    });
    
    $('#deselect-all-subscribers').on('click', function() {
        $('.subscriber-checkbox').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
    });
    
    // Send Win-Back Email
    $('#send-winback-email').on('click', function() {
        var selectedSubscribers = [];
        
        $('.subscriber-checkbox:checked').each(function() {
            selectedSubscribers.push({
                email: $(this).data('email'),
                first_name: $(this).data('first-name'),
                last_name: $(this).data('last-name'),
                subscription_id: $(this).data('subscription-id')
            });
        });
        
        if (selectedSubscribers.length === 0) {
            alert('<?php echo esc_js(__('Please select at least one subscriber.', 'subscriptions-renewal-reminders')); ?>');
            return;
        }
        
        if (!confirm('<?php echo esc_js(__('Send win-back email to ' + selectedSubscribers.length + ' selected subscriber(s)?', 'subscriptions-renewal-reminders')); ?>'.replace(selectedSubscribers.length, selectedSubscribers.length))) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('<?php echo esc_js(__('Sending...', 'subscriptions-renewal-reminders')); ?>');
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sprr_send_winback_emails',
                subscribers: selectedSubscribers,
                nonce: '<?php echo wp_create_nonce('sprr_winback_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php echo esc_js(__('Win-back emails sent successfully!', 'subscriptions-renewal-reminders')); ?>');
                    $('.subscriber-checkbox').prop('checked', false);
                    $('#select-all-checkbox').prop('checked', false);
                } else {
                    alert('<?php echo esc_js(__('Error: ', 'subscriptions-renewal-reminders')); ?>' + response.data.message);
                }
                $button.prop('disabled', false).html('<span class="dashicons dashicons-email" style="margin-top: 3px;"></span><?php echo esc_js(__('Send Win-Back Email to Selected', 'subscriptions-renewal-reminders')); ?>');
            },
            error: function() {
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'subscriptions-renewal-reminders')); ?>');
                $button.prop('disabled', false).html('<span class="dashicons dashicons-email" style="margin-top: 3px;"></span><?php echo esc_js(__('Send Win-Back Email to Selected', 'subscriptions-renewal-reminders')); ?>');
            }
        });
    });
});
</script>
