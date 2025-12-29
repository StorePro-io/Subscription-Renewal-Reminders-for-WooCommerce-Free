<?php
/**
 * Email Templates Page
 * Manage pre-built templates and drag-and-drop email builder
 * 
 * @package RenewalReminders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get saved templates
$custom_templates = get_option('sprr_custom_templates', array());

// Handle template actions
if (isset($_POST['sprr_save_template']) && check_admin_referer('sprr_template_nonce', 'sprr_template_nonce_field')) {
    // Check if user can create more templates (free users limited to 1)
    if (!sprr_is_premium_active() && count($custom_templates) >= 1 && !isset($_POST['template_id'])) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . 
             sprintf(
                 esc_html__('Free version allows only 1 custom template. %sUpgrade to Pro%s for unlimited templates!', 'subscriptions-renewal-reminders'),
                 '<a href="https://storepro.io/subscription-renewal-premium/" target="_blank" style="color: #ff6b35; font-weight: bold;">',
                 '</a>'
             ) . 
             '</p></div>';
    } else {
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : uniqid('template_');
        
        // Process content (Allow HTML for all users)
        $template_content = wp_kses_post($_POST['template_content']);
        
        $new_template = array(
            'id' => $template_id,
            'name' => sanitize_text_field($_POST['template_name']),
            'subject' => sanitize_text_field($_POST['template_subject']),
            'content' => $template_content,
            'created' => current_time('mysql'),
            'modified' => current_time('mysql'),
        );
        
        $custom_templates[$template_id] = $new_template;
        update_option('sprr_custom_templates', $custom_templates);
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Template saved successfully!', 'subscriptions-renewal-reminders') . '</p></div>';
    }
}

// Handle template deletion
if (isset($_GET['delete_template']) && check_admin_referer('delete_template_' . $_GET['delete_template'], 'nonce')) {
    $template_id = sanitize_text_field($_GET['delete_template']);
    if (isset($custom_templates[$template_id])) {
        unset($custom_templates[$template_id]);
        update_option('sprr_custom_templates', $custom_templates);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Template deleted successfully!', 'subscriptions-renewal-reminders') . '</p></div>';
    }
}

// Pre-built templates
$predefined_templates = array(
    'renewal_standard' => array(
        'name' => 'Renewal Reminder (Standard)',
        'subject' => 'Your Subscription Renews Soon',
        'preview' => 'Friendly reminder about upcoming renewal',
        'premium' => false,
        'content' => '
            <div style="background: #f5f5f5; padding: 40px 20px;">
                <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 40px;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <span style="font-size: 64px;">⏰</span>
                        <h2 style="color: #333; margin: 10px 0;">Renewal Reminder</h2>
                    </div>
                    <p style="font-size: 16px; color: #666; line-height: 1.6;">
                        Hi {first_name},
                    </p>
                    <p style="font-size: 16px; color: #666; line-height: 1.6;">
                        This is a friendly reminder that your subscription will renew soon. We wanted to make sure everything is set up correctly.
                    </p>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                        <p style="margin: 0; color: #333;"><strong>Renewal Date:</strong> [Date]</p>
                        <p style="margin: 10px 0 0; color: #333;"><strong>Amount:</strong> [Amount]</p>
                    </div>
                    <p style="font-size: 16px; color: #666; line-height: 1.6;">
                        If you need to update your payment method or have any questions, you can manage your subscription below:
                    </p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{subscription_link}" style="background: #2196F3; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Manage Subscription</a>
                    </div>
                </div>
            </div>
        '
    ),
    'renewal_early' => array(
        'name' => 'Renewal Reminder (Early Notice)',
        'subject' => 'Advance Notice: Your Subscription Renewal',
        'preview' => 'Early friendly notice to keep subscribers informed',
        'premium' => false,
        'content' => '
            <div style="background: #fff; padding: 40px 20px; border: 1px solid #eee;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #4CAF50;">Upcoming Renewal</h2>
                    <p style="font-size: 16px; color: #666;">Hi {first_name},</p>
                    <p style="font-size: 16px; color: #666; line-height: 1.6;">
                        Just a quick heads-up that your subscription is scheduled for renewal in a few days. We value your membership and wanted to give you plenty of notice.
                    </p>
                    <p style="font-size: 16px; color: #666;">
                        No action is needed on your part if you wish to continue enjoying your benefits!
                    </p>
                    <div style="margin: 30px 0; border-left: 4px solid #4CAF50; padding-left: 20px;">
                        <p style="margin: 0; font-size: 14px; color: #888;">Expected Renewal Date:</p>
                        <p style="margin: 5px 0 0; font-size: 18px; color: #333; font-weight: bold;">[Date]</p>
                    </div>
                    <p style="font-size: 14px; color: #999;">
                        Need to make changes? <a href="{subscription_link}" style="color: #4CAF50;">Visit your account dashboard.</a>
                    </p>
                </div>
            </div>
        '
    ),
    'renewal_urgent' => array(
        'name' => 'Renewal Reminder (Last Chance)',
        'subject' => 'Action Required: Your Subscription Renews Tomorrow',
        'preview' => 'Urgent reminder for immediate attention',
        'premium' => false,
        'content' => '
            <div style="background: #fff5f5; padding: 40px 20px; border: 2px solid #feb2b2;">
                <div style="max-width: 600px; margin: 0 auto; text-align: center;">
                    <div style="background: #f56565; color: #fff; display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 20px;">LAST CHANCE</div>
                    <h2 style="color: #c53030; margin: 0 0 10px;">Renewal is Happening Soon</h2>
                    <p style="font-size: 18px; color: #333; margin-bottom: 25px;">Hi {first_name}, your subscription will renew in less than 24 hours.</p>
                    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px;">
                        <p style="color: #666; line-height: 1.6; margin: 0;">
                            Please ensure your payment method is up to date to avoid any interruption in your service. We have your renewal scheduled for tomorrow.
                        </p>
                    </div>
                    <a href="{subscription_link}" style="background: #c53030; color: #fff; padding: 18px 45px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; font-size: 18px;">Update Payment Details</a>
                    <p style="margin-top: 25px; font-size: 14px; color: #718096;">
                        Questions? Reply to this email and our team will help you out.
                    </p>
                </div>
            </div>
        '
    ),
    'winback_standard' => array(
        'name' => 'Win-back: We Miss You',
        'subject' => 'We Miss You, {first_name}!',
        'preview' => 'A friendly re-engagement email for inactive subscribers',
        'premium' => true,
        'content' => '
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center;">
                <h1 style="color: #fff; margin: 0; font-size: 32px;">We Miss You!</h1>
            </div>
            <div style="padding: 40px 20px; background: #fff;">
                <p style="font-size: 18px; color: #333;">Hi {first_name},</p>
                <p style="font-size: 16px; color: #666; line-height: 1.6;">
                    We noticed you haven\'t been active lately, and we wanted to reach out. Your subscription means a lot to us!
                </p>
                <p style="font-size: 16px; color: #666; line-height: 1.6;">
                    We\'ve made some exciting updates and would love to have you back. Check out what\'s new:
                </p>
                <ul style="font-size: 16px; color: #666; line-height: 1.8;">
                    <li>New features and improvements</li>
                    <li>Better user experience</li>
                    <li>Exclusive member benefits</li>
                </ul>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{subscription_link}" style="background: #667eea; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Reactivate Your Subscription</a>
                </div>
                <p style="font-size: 14px; color: #999; text-align: center;">
                    Questions? We\'re here to help! Just reply to this email.
                </p>
            </div>
        '
    ),
    'winback_offer' => array(
        'name' => 'Win-back: Special Offer',
        'subject' => 'Exclusive 20% Off - Just For You!',
        'preview' => 'Limited-time discount to win back subscribers',
        'premium' => true,
        'content' => '
            <div style="background: #f8f9fa; padding: 40px 20px;">
                <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 40px; text-align: center;">
                        <h1 style="color: #fff; margin: 0; font-size: 36px;">Special Offer!</h1>
                        <p style="color: #fff; font-size: 18px; margin: 10px 0 0;">Just for you, {first_name}</p>
                    </div>
                    <div style="padding: 40px;">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="background: #f5576c; color: #fff; display: inline-block; padding: 20px 40px; border-radius: 50%; font-size: 48px; font-weight: bold;">20%</div>
                            <p style="font-size: 24px; color: #333; margin: 10px 0 0;">OFF</p>
                        </div>
                        <p style="font-size: 16px; color: #666; line-height: 1.6; text-align: center;">
                            We value your membership and want to make it easy for you to come back. Use this exclusive discount to reactivate your subscription today!
                        </p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{subscription_link}" style="background: #f5576c; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 18px;">Claim Your Discount</a>
                        </div>
                        <p style="font-size: 14px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px;">
                            This offer expires in 7 days. Don\'t miss out!
                        </p>
                    </div>
                </div>
            </div>
        '
    ),
);

$active_tab = isset($_GET['template_tab']) ? sanitize_text_field($_GET['template_tab']) : 'library';
?>

<div class="wrap">
    <!-- Print the page title with enhanced styling -->
    <div style="text-align: center; margin: 30px 0;">
        <h1 class="renew-rem-makin-title"> 
            <?php echo esc_html__('📧 Email Templates', 'subscriptions-renewal-reminders'); ?>
        </h1>
        <p class="renew-rem-subtitle">
            <?php echo esc_html__('Choose from pre-built templates or create your own custom email templates with our drag-and-drop builder', 'subscriptions-renewal-reminders'); ?>
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

    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper" style="margin-top: 20px;">
        <a href="?page=sp-renewal-reminders-templates&template_tab=library" 
           class="nav-tab <?php echo $active_tab === 'library' ? 'nav-tab-active' : ''; ?>">
            📚 Template Library
        </a>
        <a href="?page=sp-renewal-reminders-templates&template_tab=builder" 
           class="nav-tab <?php echo $active_tab === 'builder' ? 'nav-tab-active' : ''; ?>">
            🎨 Email Builder
        </a>
        <a href="?page=sp-renewal-reminders-templates&template_tab=custom" 
           class="nav-tab <?php echo $active_tab === 'custom' ? 'nav-tab-active' : ''; ?>">
            💾 My Templates
        </a>
    </nav>

    <?php if ($active_tab === 'library'): ?>
        <!-- Template Library -->
        <div style="background: #fff; margin-top: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); display: grid; grid-template-columns: 400px 1fr;">
            <!-- Left: Template List -->
            <div style="padding: 20px; border-right: 1px solid #ddd; overflow-y: auto; max-height: 800px;">
                <h2 style="margin-top: 0;">Pre-Built Templates</h2>
                <p class="description">Click a template to preview</p>
                
                <div class="sprr-template-list">
                    <?php foreach ($predefined_templates as $key => $template): 
                        $is_premium_template = isset($template['premium']) && $template['premium'];
                        $has_access = !$is_premium_template || sprr_is_premium_active();
                    ?>
                    <div class="sprr-template-item <?php echo !$has_access ? 'sprr-template-premium' : ''; ?>" data-template-id="<?php echo esc_attr($key); ?>">
                        <div style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #ddd; border-radius: 4px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; position: relative;" class="template-card" data-content="<?php echo esc_attr($template['content']); ?>">
                            <?php if ($is_premium_template): ?>
                                <span class="sprr-pro-badge" style="position: absolute; top: -10px; right: -10px; background: #ffb900; color: #000; font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">PRO</span>
                            <?php endif; ?>
                            <div style="font-size: 36px; flex-shrink: 0;">📧</div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="margin: 0 0 5px; font-size: 15px;"><?php echo esc_html($template['name']); ?></h4>
                                <p class="description" style="margin: 0; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo esc_html($template['preview']); ?></p>
                            </div>
                        </div>
                        <div class="template-actions" style="margin-top: 10px; display: none;">
                            <?php if ($has_access): ?>
                                <button type="button" class="button button-primary button-small sprr-use-template" style="width: 100%; margin-bottom: 5px;"
                                        data-template-id="<?php echo esc_attr($key); ?>"
                                        data-template-name="<?php echo esc_attr($template['name']); ?>"
                                        data-template-subject="<?php echo esc_attr($template['subject']); ?>"
                                        data-template-content="<?php echo esc_attr($template['content']); ?>">
                                    Use This Template
                                </button>
                            <?php else: ?>
                                <div style="text-align: center; padding: 10px; background: #fff8e1; border: 1px solid #ffe082; border-radius: 4px;">
                                    <p style="margin: 0 0 8px; font-size: 12px; color: #856404; font-weight: 500;">Win-back templates are available in Pro version</p>
                                    <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary button-small" style="width: 100%;">
                                        Upgrade to Pro
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right: Preview -->
            <div style="padding: 20px; background: #f9f9f9; overflow-y: auto; max-height: 800px;">
                <div id="sprr-template-preview-area">
                    <div style="text-align: center; padding: 100px 20px; color: #999;">
                        <span class="dashicons dashicons-email-alt" style="font-size: 64px; opacity: 0.3;"></span>
                        <p style="font-size: 16px; margin-top: 20px;">Select a template to preview</p>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($active_tab === 'builder'): ?>
        <!-- Email Builder -->
        <?php include plugin_dir_path(__FILE__) . 'renewal-reminders-email-builder.php'; ?>

    <?php elseif ($active_tab === 'custom'): ?>
        <!-- Custom Templates -->
        <div style="background: #fff; padding: 20px; margin-top: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">My Custom Templates</h2>
                <?php if (!sprr_is_premium_active() && count($custom_templates) >= 1): ?>
                    <div style="text-align: right;">
                        <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                            <?php esc_html_e('Free version allows only 1 custom template.', 'subscriptions-renewal-reminders'); ?>
                        </p>
                        <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary">
                            <span class="dashicons dashicons-star-filled" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Upgrade to Pro for Unlimited Templates', 'subscriptions-renewal-reminders'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="?page=sp-renewal-reminders-templates&template_tab=builder" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                        Create New Template
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($custom_templates)): ?>
                <div style="text-align: center; padding: 60px 20px; color: #666;">
                    <span class="dashicons dashicons-email-alt" style="font-size: 64px; opacity: 0.3;"></span>
                    <p style="font-size: 16px; margin-top: 20px;">You haven't created any custom templates yet.</p>
                    <a href="?page=sp-renewal-reminders-templates&template_tab=builder" class="button button-primary" style="margin-top: 10px;">Create Your First Template</a>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Subject</th>
                            <th>Created</th>
                            <th>Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($custom_templates as $template): ?>
                        <tr>
                            <td><strong><?php echo esc_html($template['name']); ?></strong></td>
                            <td><?php echo esc_html($template['subject']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($template['created'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($template['modified'])); ?></td>
                            <td>
                                <button type="button" class="button button-small sprr-edit-custom-template"
                                        data-id="<?php echo esc_attr($template['id']); ?>"
                                        data-name="<?php echo esc_attr($template['name']); ?>"
                                        data-subject="<?php echo esc_attr($template['subject']); ?>"
                                        data-content="<?php echo esc_attr($template['content']); ?>">
                                    Edit
                                </button>
                                <a href="?page=sp-renewal-reminders-templates&template_tab=custom&delete_template=<?php echo esc_attr($template['id']); ?>&nonce=<?php echo wp_create_nonce('delete_template_' . $template['id']); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('Are you sure you want to delete this template?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Preview Modal -->
<div id="sprr-preview-modal" class="sprr-modal" style="display: none;">
    <div class="sprr-modal-content" style="max-width: 800px;">
        <div class="sprr-modal-header">
            <h2>Email Preview</h2>
            <button type="button" class="sprr-modal-close">&times;</button>
        </div>
        <div class="sprr-modal-body" id="sprr-preview-content" style="max-height: 70vh; overflow-y: auto;">
            <!-- Preview content will be inserted here -->
        </div>
        <div class="sprr-modal-footer">
            <button type="button" class="button sprr-modal-close">Close</button>
        </div>
    </div>
</div>

<style>
.sprr-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.sprr-template-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    transition: box-shadow 0.3s;
}

.sprr-template-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.template-preview {
    background: #f9f9f9;
}

.template-info {
    padding: 20px;
}

.template-info h3 {
    margin: 0 0 10px;
    font-size: 18px;
}

.template-info .description {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.template-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.template-actions .button {
    flex: 1;
}

.sprr-template-list .template-card:hover {
    border-color: #2271b1;
    background: #f9f9f9;
}

.sprr-template-list .template-card.active {
    border-color: #2271b1;
    background: #e8f4f8;
}

.sprr-template-item.active .template-actions {
    display: block !important;
}

.sprr-template-premium .template-card {
    opacity: 0.8;
}

.sprr-template-premium.active .template-card {
    border-color: #ffb900 !important;
    background: #fffdf5 !important;
}

.sprr-pro-badge {
    z-index: 10;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Template preview on click
    $('.template-card').on('click', function() {
        $('.template-card').removeClass('active');
        $('.sprr-template-item').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.sprr-template-item').addClass('active');
        
        var content = $(this).data('content');
        $('#sprr-template-preview-area').html('<div style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' + content + '</div>');
    });
    
    // Auto-select first template
    if ($('.template-card').length > 0) {
        $('.template-card').first().trigger('click');
    }
    
    // Use template
    $('.sprr-use-template').on('click', function(e) {
        e.stopPropagation();
        var templateName = $(this).data('template-name');
        var templateSubject = $(this).data('template-subject');
        var templateContent = $(this).data('template-content');
        
        // Redirect to builder with template data
        var url = '?page=sp-renewal-reminders-templates&template_tab=builder' +
                  '&use_template=1' +
                  '&template_name=' + encodeURIComponent(templateName) +
                  '&template_subject=' + encodeURIComponent(templateSubject) +
                  '&template_content=' + encodeURIComponent(templateContent);
        
        window.location.href = url;
    });
    
    // Edit custom template
    $('.sprr-edit-custom-template').on('click', function() {
        var templateId = $(this).data('id');
        var templateName = $(this).data('name');
        var templateSubject = $(this).data('subject');
        var templateContent = $(this).data('content');
        
        var url = '?page=sp-renewal-reminders-templates&template_tab=builder' +
                  '&edit_template=' + templateId +
                  '&template_name=' + encodeURIComponent(templateName) +
                  '&template_subject=' + encodeURIComponent(templateSubject) +
                  '&template_content=' + encodeURIComponent(templateContent);
        
        window.location.href = url;
    });
    
    // Close modal
    $('.sprr-modal-close').on('click', function() {
        $('.sprr-modal').fadeOut(200);
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('sprr-modal')) {
            $('.sprr-modal').fadeOut(200);
        }
    });
});
</script>
