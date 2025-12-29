<?php
/**
 * Email History Template
 * Shows sent and upcoming automation emails
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get email history
$sent_emails = get_option('sprr_sent_emails', array());
$upcoming_emails = get_option('sprr_upcoming_emails', array());

// Sort by date (most recent first)
usort($sent_emails, function($a, $b) {
    return strtotime($b['sent_date']) - strtotime($a['sent_date']);
});

usort($upcoming_emails, function($a, $b) {
    return strtotime($a['scheduled_date']) - strtotime($b['scheduled_date']);
});

// Pagination
$sent_per_page = 20;
$upcoming_per_page = 20;
$sent_page = isset($_GET['sent_page']) ? max(1, intval($_GET['sent_page'])) : 1;
$upcoming_page = isset($_GET['upcoming_page']) ? max(1, intval($_GET['upcoming_page'])) : 1;

$sent_total = count($sent_emails);
$upcoming_total = count($upcoming_emails);

$sent_emails_paginated = array_slice($sent_emails, ($sent_page - 1) * $sent_per_page, $sent_per_page);
$upcoming_emails_paginated = array_slice($upcoming_emails, ($upcoming_page - 1) * $upcoming_per_page, $upcoming_per_page);

$sent_total_pages = ceil($sent_total / $sent_per_page);
$upcoming_total_pages = ceil($upcoming_total / $upcoming_per_page);
?>

<style>
.sprr-history-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.sprr-history-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e5e5;
}

.sprr-history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.sprr-history-table th {
    background: #f9f9f9;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #e5e5e5;
}

.sprr-history-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e5e5;
}

.sprr-history-table tr:hover {
    background: #f9f9f9;
}

.sprr-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.sprr-status-sent {
    background: #d4edda;
    color: #155724;
}

.sprr-status-scheduled {
    background: #fff3cd;
    color: #856404;
}

.sprr-status-failed {
    background: #f8d7da;
    color: #721c24;
}

.sprr-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.sprr-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #ccc;
}

.sprr-pagination {
    margin-top: 20px;
    text-align: center;
}

.sprr-pagination a,
.sprr-pagination span {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #2271b1;
}

.sprr-pagination span.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.sprr-pagination a:hover {
    background: #f0f0f0;
}

.sprr-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.sprr-stat-card {
    background: #f9f9f9;
    padding: 15px;
    border-left: 4px solid #2271b1;
    border-radius: 4px;
}

.sprr-stat-card .stat-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
}

.sprr-stat-card .stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.sprr-cancel-email {
    min-width: 100px;
}
</style>

<div class="sprr-history-section">
    <h2>📊 Statistics</h2>
    <div class="sprr-stats-grid">
        <div class="sprr-stat-card">
            <div class="stat-label">Total Sent</div>
            <div class="stat-value"><?php echo number_format($sent_total); ?></div>
        </div>
        <div class="sprr-stat-card">
            <div class="stat-label">Upcoming</div>
            <div class="stat-value"><?php echo number_format($upcoming_total); ?></div>
        </div>
        <div class="sprr-stat-card">
            <div class="stat-label">Success Rate</div>
            <div class="stat-value">
                <?php 
                $successful = count(array_filter($sent_emails, function($e) { return $e['status'] === 'sent'; }));
                $rate = $sent_total > 0 ? ($successful / $sent_total) * 100 : 0;
                echo number_format($rate, 1) . '%';
                ?>
            </div>
        </div>
    </div>
</div>

<div class="sprr-history-section">
    <h2>📤 Upcoming Emails (<?php echo number_format($upcoming_total); ?>)</h2>
    
    <?php if (empty($upcoming_emails_paginated)): ?>
        <div class="sprr-empty-state">
            <span class="dashicons dashicons-email"></span>
            <p>No upcoming emails scheduled</p>
        </div>
    <?php else: ?>
        <table class="sprr-history-table">
            <thead>
                <tr>
                    <th>Scheduled Date</th>
                    <th>Recipient</th>
                    <th>Email</th>
                    <th>Automation Rule</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_emails_paginated as $index => $email): 
                    $subscription = wcs_get_subscription($email['subscription_id']);
                    $user = $subscription ? $subscription->get_user() : null;
                ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($email['scheduled_date'])); ?></td>
                    <td>
                        <?php if ($user): ?>
                            <strong><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></strong><br>
                            <small><?php echo esc_html($user->user_email); ?></small>
                        <?php else: ?>
                            <em>N/A</em>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($email['email_subject']); ?></td>
                    <td><?php echo esc_html($email['rule_name']); ?></td>
                    <td>
                        <span class="sprr-status-badge sprr-status-scheduled">Scheduled</span>
                    </td>
                    <td>
                        <button type="button" class="button button-small sprr-cancel-email" data-index="<?php echo $index; ?>">
                            Cancel
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($upcoming_total_pages > 1): ?>
        <div class="sprr-pagination">
            <?php for ($i = 1; $i <= $upcoming_total_pages; $i++): ?>
                <?php if ($i === $upcoming_page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=subscriptions_renewal_reminders&tab=marketing&marketing_tab=history&upcoming_page=<?php echo $i; ?>#upcoming">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="sprr-history-section">
    <h2>✅ Sent Emails (<?php echo number_format($sent_total); ?>)</h2>
    
    <?php if (empty($sent_emails_paginated)): ?>
        <div class="sprr-empty-state">
            <span class="dashicons dashicons-email-alt"></span>
            <p>No emails sent yet</p>
        </div>
    <?php else: ?>
        <table class="sprr-history-table">
            <thead>
                <tr>
                    <th>Sent Date</th>
                    <th>Recipient</th>
                    <th>Email</th>
                    <th>Automation Rule</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sent_emails_paginated as $email): 
                    $subscription = wcs_get_subscription($email['subscription_id']);
                    $user = $subscription ? $subscription->get_user() : null;
                ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($email['sent_date'])); ?></td>
                    <td>
                        <?php if ($user): ?>
                            <strong><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></strong><br>
                            <small><?php echo esc_html($user->user_email); ?></small>
                        <?php else: ?>
                            <em>N/A</em>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($email['email_subject']); ?></td>
                    <td><?php echo esc_html($email['rule_name']); ?></td>
                    <td>
                        <?php if ($email['status'] === 'sent'): ?>
                            <span class="sprr-status-badge sprr-status-sent">Sent</span>
                        <?php else: ?>
                            <span class="sprr-status-badge sprr-status-failed">Failed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($sent_total_pages > 1): ?>
        <div class="sprr-pagination">
            <?php for ($i = 1; $i <= $sent_total_pages; $i++): ?>
                <?php if ($i === $sent_page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=subscriptions_renewal_reminders&tab=marketing&marketing_tab=history&sent_page=<?php echo $i; ?>#sent">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Cancel upcoming email
    $('.sprr-cancel-email').on('click', function() {
        if (!confirm('Are you sure you want to cancel this scheduled email?')) {
            return;
        }
        
        var button = $(this);
        var index = button.data('index');
        
        button.prop('disabled', true).text('Canceling...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'sprr_cancel_upcoming_email',
                nonce: '<?php echo wp_create_nonce('sprr_cancel_email'); ?>',
                index: index
            },
            success: function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(400, function() {
                        $(this).remove();
                    });
                    
                    // Show success message
                    var notice = $('<div class="notice notice-success is-dismissible"><p>Email cancelled successfully.</p></div>');
                    $('.sprr-history-section').first().before(notice);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + response.data);
                    button.prop('disabled', false).text('Cancel');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text('Cancel');
            }
        });
    });
});
</script>
