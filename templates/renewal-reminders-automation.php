<?php
/**
 * Marketing Automation Rules Template
 * 
 * @package RenewalReminders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get existing automation rules
$automation_rules = get_option('sprr_automation_rules', array());

// Migrate old rules to use template system
foreach ($automation_rules as $rule_id => $rule) {
    if (isset($rule['email_subject']) || isset($rule['email_content'])) {
        // Migrate old rule to use default template
        $automation_rules[$rule_id]['selected_template'] = 'default_template';
        unset($automation_rules[$rule_id]['email_subject']);
        unset($automation_rules[$rule_id]['email_content']);
        update_option('sprr_automation_rules', $automation_rules);
    }
}

// Handle form submissions
if (isset($_POST['sprr_save_automation_rule']) && check_admin_referer('sprr_automation_rule_nonce', 'sprr_automation_nonce')) {
    // Check if user has premium access for automation rules
    if (!sprr_is_premium_active()) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . 
             sprintf(
                 esc_html__('Automation rules are a premium feature. %sUpgrade to Pro%s to create automated email sequences!', 'subscriptions-renewal-reminders'),
                 '<a href="https://storepro.io/subscription-renewal-premium/" target="_blank" style="color: #ff6b35; font-weight: bold;">',
                 '</a>'
             ) . 
             '</p></div>';
    } else {
        // Debug: Check if form data is received
        error_log('Automation rule form submitted');
        $received_rule_id = isset($_POST['rule_id']) ? sanitize_text_field($_POST['rule_id']) : 'EMPTY';
        error_log('Received rule_id: ' . $received_rule_id);
        
        $rule_id = isset($_POST['rule_id']) && !empty($_POST['rule_id']) ? sanitize_text_field($_POST['rule_id']) : uniqid('rule_');
        error_log('Final rule_id: ' . $rule_id);
        
        $new_rule = array(
            'id' => $rule_id,
            'name' => sanitize_text_field($_POST['rule_name']),
            'status' => sanitize_text_field($_POST['subscription_status']),
            'delay_days' => intval($_POST['delay_days']),
            'selected_template' => sanitize_text_field($_POST['selected_template']),
            'enabled' => isset($_POST['rule_enabled']) ? 1 : 0,
            'created' => current_time('mysql'),
        );
        
        error_log('New rule data: ' . print_r($new_rule, true));
        
        $automation_rules[$rule_id] = $new_rule;
        update_option('sprr_automation_rules', $automation_rules);
        
        error_log('Automation rules saved: ' . print_r($automation_rules, true));
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Automation rule saved successfully!', 'subscriptions-renewal-reminders') . '</p></div>';
    }
}

// Handle rule deletion
if (isset($_GET['delete_rule']) && check_admin_referer('delete_rule_' . $_GET['delete_rule'], 'nonce')) {
    $rule_id = sanitize_text_field($_GET['delete_rule']);
    if (isset($automation_rules[$rule_id])) {
        unset($automation_rules[$rule_id]);
        update_option('sprr_automation_rules', $automation_rules);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Automation rule deleted successfully!', 'subscriptions-renewal-reminders') . '</p></div>';
    }
}

// Get edit rule if exists
$edit_rule = null;
if (isset($_GET['edit_rule']) && isset($automation_rules[$_GET['edit_rule']])) {
    $edit_rule = $automation_rules[$_GET['edit_rule']];
}

$available_statuses = array(
    'on-hold' => __('On Hold', 'subscriptions-renewal-reminders'),
    'expired' => __('Expired', 'subscriptions-renewal-reminders'),
    'cancelled' => __('Cancelled', 'subscriptions-renewal-reminders'),
    'pending-cancel' => __('Pending Cancellation', 'subscriptions-renewal-reminders'),
);
?>

<div class="automation-rules-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2><?php echo esc_html__('🤖 Automation Rules', 'subscriptions-renewal-reminders'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Create automated email campaigns based on subscription status changes. Send targeted emails to customers after specific conditions are met.', 'subscriptions-renewal-reminders'); ?>
            </p>
        </div>
        <?php if (!sprr_is_premium_active()): ?>
            <div style="text-align: right;">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                    <?php esc_html_e('Automation rules are a premium feature.', 'subscriptions-renewal-reminders'); ?>
                </p>
                <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary sprr-upgrade-btn">
                    <span class="dashicons dashicons-star-filled" style="margin-top: 3px;"></span>
                    <?php esc_html_e('Upgrade to Pro for Automation', 'subscriptions-renewal-reminders'); ?>
                </a>
            </div>
        <?php else: ?>
            <button type="button" id="add-new-rule" class="button button-primary">
                <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                <?php echo esc_html__('Add New Rule', 'subscriptions-renewal-reminders'); ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Existing Rules List -->
    <?php if (!empty($automation_rules)): ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 40px;"><?php echo esc_html__('Status', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Rule Name', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Trigger', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Delay', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Template', 'subscriptions-renewal-reminders'); ?></th>
                <th><?php echo esc_html__('Actions', 'subscriptions-renewal-reminders'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($automation_rules as $rule): ?>
            <tr>
                <td>
                    <label class="switch">
                        <input type="checkbox" class="toggle-rule" data-rule-id="<?php echo esc_attr($rule['id']); ?>" 
                               <?php checked($rule['enabled'], 1); ?>
                               <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </td>
                <td><strong><?php echo esc_html($rule['name']); ?></strong></td>
                <td>
                    <span class="subscription-status status-<?php echo esc_attr($rule['status']); ?>">
                        <?php echo esc_html(ucfirst(str_replace('-', ' ', $rule['status']))); ?>
                    </span>
                </td>
                <td><?php echo esc_html(sprintf(__('%d days', 'subscriptions-renewal-reminders'), $rule['delay_days'])); ?></td>
                <td>
                    <?php
                    $custom_templates = get_option('sprr_custom_templates', array());
                    $template_name = __('Default Template', 'subscriptions-renewal-reminders');
                    if ($rule['selected_template'] !== 'default_template' && isset($custom_templates[$rule['selected_template']])) {
                        $template_name = $custom_templates[$rule['selected_template']]['name'];
                    }
                    echo esc_html($template_name);
                    ?>
                </td>
                <td>
                    <?php if (!sprr_is_premium_active()): ?>
                        <button type="button" class="button button-small" disabled style="opacity: 0.5;" title="<?php esc_attr_e('Automation rules are a premium feature', 'subscriptions-renewal-reminders'); ?>">
                            <?php echo esc_html__('Edit', 'subscriptions-renewal-reminders'); ?> <span style="color: #ff6b35; font-weight: bold;">PRO</span>
                        </button>
                        <button type="button" class="button button-small button-link-delete" disabled style="opacity: 0.5;" title="<?php esc_attr_e('Automation rules are a premium feature', 'subscriptions-renewal-reminders'); ?>">
                            <?php echo esc_html__('Delete', 'subscriptions-renewal-reminders'); ?> <span style="color: #ff6b35; font-weight: bold;">PRO</span>
                        </button>
                    <?php else: ?>
                        <a href="?page=sp-renewal-reminders-marketing&marketing_tab=automation&edit_rule=<?php echo esc_attr($rule['id']); ?>" 
                           class="button button-small">
                            <?php echo esc_html__('Edit', 'subscriptions-renewal-reminders'); ?>
                        </a>
                        <a href="?page=sp-renewal-reminders-marketing&marketing_tab=automation&delete_rule=<?php echo esc_attr($rule['id']); ?>&nonce=<?php echo wp_create_nonce('delete_rule_' . $rule['id']); ?>" 
                           class="button button-small button-link-delete"
                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this rule?', 'subscriptions-renewal-reminders')); ?>')">
                            <?php echo esc_html__('Delete', 'subscriptions-renewal-reminders'); ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="automation-empty-state" style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
        <span class="dashicons dashicons-calendar-alt" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></span>
        <h3><?php echo esc_html__('No Automation Rules Yet', 'subscriptions-renewal-reminders'); ?></h3>
        <p><?php echo esc_html__('Create your first automation rule to send targeted emails based on subscription status.', 'subscriptions-renewal-reminders'); ?></p>
        <?php if (!sprr_is_premium_active()): ?>
            <button type="button" class="button button-primary button-hero sprr-show-pro-modal">
                <?php echo esc_html__('Create First Rule', 'subscriptions-renewal-reminders'); ?>
            </button>
        <?php else: ?>
            <button type="button" class="button button-primary button-hero" onclick="document.getElementById('add-new-rule').click();">
                <?php echo esc_html__('Create First Rule', 'subscriptions-renewal-reminders'); ?>
            </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Add/Edit Rule Form (Modal) -->
    <div id="rule-form-modal" class="sprr-modal" style="display: <?php echo $edit_rule ? 'block' : 'none'; ?>;">
        <div class="sprr-modal-content">
            <div class="sprr-modal-header">
                <h2><?php echo $edit_rule ? esc_html__('Edit Automation Rule', 'subscriptions-renewal-reminders') : esc_html__('Add New Automation Rule', 'subscriptions-renewal-reminders'); ?></h2>
                <button type="button" class="sprr-modal-close">&times;</button>
            </div>
            <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                <?php wp_nonce_field('sprr_automation_rule_nonce', 'sprr_automation_nonce'); ?>
                <input type="hidden" name="sprr_save_automation_rule" value="1">
                <input type="hidden" name="rule_id" value="<?php echo $edit_rule ? esc_attr($edit_rule['id']) : ''; ?>">
                
                <div class="sprr-modal-body">
                    <?php if (!sprr_is_premium_active()): ?>
                        <div class="notice notice-info" style="margin-bottom: 20px;">
                            <p><?php esc_html_e('Automation rules are a premium feature. Upgrade to Pro to create automated email campaigns.', 'subscriptions-renewal-reminders'); ?></p>
                        </div>
                    <?php endif; ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rule_name"><?php echo esc_html__('Rule Name', 'subscriptions-renewal-reminders'); ?> *</label>
                            </th>
                            <td>
                                <input type="text" id="rule_name" name="rule_name" class="regular-text" 
                                       value="<?php echo $edit_rule ? esc_attr($edit_rule['name']) : ''; ?>" 
                                       placeholder="<?php echo esc_attr__('e.g., On-Hold Follow-up Email', 'subscriptions-renewal-reminders'); ?>" 
                                       required <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                                <p class="description"><?php echo esc_html__('A descriptive name for this automation rule', 'subscriptions-renewal-reminders'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="subscription_status"><?php echo esc_html__('Subscription Status', 'subscriptions-renewal-reminders'); ?> *</label>
                            </th>
                            <td>
                                <select id="subscription_status" name="subscription_status" class="regular-text" required <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                                    <option value=""><?php echo esc_html__('-- Select Status --', 'subscriptions-renewal-reminders'); ?></option>
                                    <?php foreach ($available_statuses as $status_key => $status_label): ?>
                                    <option value="<?php echo esc_attr($status_key); ?>" 
                                            <?php selected($edit_rule && $edit_rule['status'] === $status_key); ?>>
                                        <?php echo esc_html($status_label); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php echo esc_html__('Trigger this rule when subscription changes to this status', 'subscriptions-renewal-reminders'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="delay_days"><?php echo esc_html__('Delay (Days)', 'subscriptions-renewal-reminders'); ?> *</label>
                            </th>
                            <td>
                                <input type="number" id="delay_days" name="delay_days" class="small-text" min="0" max="365"
                                       value="<?php echo $edit_rule ? esc_attr($edit_rule['delay_days']) : '1'; ?>" 
                                       required <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                                <p class="description"><?php echo esc_html__('Send email after this many days from status change (0 = immediately)', 'subscriptions-renewal-reminders'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="selected_template"><?php echo esc_html__('Email Template', 'subscriptions-renewal-reminders'); ?> *</label>
                            </th>
                            <td>
                                <?php
                                $custom_templates = get_option('sprr_custom_templates', array());
                                $selected_template = $edit_rule ? ($edit_rule['selected_template'] ?? 'default_template') : 'default_template';
                                ?>
                                <select id="selected_template" name="selected_template" class="regular-text" style="min-width: 300px;" <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                                    <option value="default_template" <?php selected($selected_template, 'default_template'); ?>>
                                        <?php esc_html_e('Default Template', 'subscriptions-renewal-reminders'); ?>
                                    </option>
                                    <?php foreach ($custom_templates as $template_id => $template): ?>
                                        <option value="<?php echo esc_attr($template_id); ?>" <?php selected($selected_template, $template_id); ?>>
                                            <?php echo esc_html($template['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Choose the email template to use for this automation rule.', 'subscriptions-renewal-reminders'); ?>
                                    <a href="<?php echo admin_url('admin.php?page=sp-renewal-reminders-templates&template_tab=builder'); ?>" target="_blank" style="margin-left: 10px;">
                                        <span class="dashicons dashicons-plus-alt" style="font-size: 14px; vertical-align: middle;"></span>
                                        <?php esc_html_e('Create New Template', 'subscriptions-renewal-reminders'); ?>
                                    </a>
                                </p>
                                <div id="automation-template-preview" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; display: none;">
                                    <h4 style="margin-top: 0;"><?php esc_html_e('Template Preview:', 'subscriptions-renewal-reminders'); ?></h4>
                                    <div id="automation-template-preview-content"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="rule_enabled"><?php echo esc_html__('Enable Rule', 'subscriptions-renewal-reminders'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="rule_enabled" name="rule_enabled" value="1" 
                                           <?php checked(!$edit_rule || $edit_rule['enabled'], 1); ?>
                                           <?php echo !sprr_is_premium_active() ? 'disabled' : ''; ?>>
                                    <?php echo esc_html__('Active (emails will be sent)', 'subscriptions-renewal-reminders'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="sprr-modal-footer">
                    <button type="button" class="button sprr-modal-close"><?php echo esc_html__('Cancel', 'subscriptions-renewal-reminders'); ?></button>
                    <?php if (!sprr_is_premium_active()): ?>
                        <button type="button" class="button button-primary" disabled style="opacity: 0.5;" title="<?php esc_attr_e('Automation rules are a premium feature', 'subscriptions-renewal-reminders'); ?>">
                            <?php echo esc_html__('Save Rule', 'subscriptions-renewal-reminders'); ?> <span style="color: #ff6b35; font-weight: bold;">PRO</span>
                        </button>
                        <div style="margin-top: 10px; text-align: center;">
                            <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" style="color: #ff6b35; font-weight: bold;">
                                <?php esc_html_e('Upgrade to Pro to save automation rules', 'subscriptions-renewal-reminders'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <button type="submit" class="button button-primary">
                            <?php echo esc_html__('Save Rule', 'subscriptions-renewal-reminders'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Pro Feature Modal -->
    <div id="sprr-pro-feature-modal" class="sprr-modal" style="display: none;">
        <div class="sprr-modal-content" style="max-width: 500px; text-align: center; padding: 40px 30px;">
            <div style="font-size: 60px; margin-bottom: 20px;">🚀</div>
            <h2 style="font-size: 24px; margin: 0 0 15px; color: #2c3e50;">Unlock Automation Rules</h2>
            <p style="font-size: 16px; line-height: 1.6; color: #666; margin-bottom: 30px;">
                Automate your marketing and win back customers! Upgrade to the Pro version to create unlimited automation rules, triggered email sequences, and advanced scheduling.
            </p>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary button-hero" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <span class="dashicons dashicons-cart" style="margin-top: 2px;"></span>
                    Upgrade to Pro Now
                </a>
                <button type="button" class="button button-link sprr-modal-close" style="color: #999; text-decoration: none;">
                    Maybe Later
                </button>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: flex; justify-content: space-around; font-size: 12px; color: #888;">
                <span>✅ Unlimited Rules</span>
                <span>✅ Triggered Sequences</span>
                <span>✅ Priority Support</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 24px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #2271b1;
}
input:checked + .slider:before {
    transform: translateX(20px);
}

/* Modal Styles */
.sprr-modal {
    position: fixed;
    z-index: 999999 !important;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
}
.sprr-modal-content {
    background-color: #fff;
    margin: 50px auto;
    width: 90%;
    max-width: 800px;
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1000000 !important;
}
.sprr-modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sprr-modal-header h2 {
    margin: 0;
}
.sprr-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    line-height: 1;
}
.sprr-modal-close:hover {
    color: #000;
}
.sprr-modal-body {
    padding: 20px 30px;
    max-height: 60vh;
    overflow-y: auto;
}
.sprr-modal-footer {
    padding: 15px 30px;
    border-top: 1px solid #ddd;
    text-align: right;
}
.sprr-modal-footer .button {
    margin-left: 10px;
    min-width: 120px;
    padding: 6px 20px;
}

/* Fix editor text visibility */
.sprr-modal-body .wp-editor-area,
.sprr-modal-body #email_content {
    color: #000 !important;
    background-color: #fff !important;
}
.sprr-modal-body .wp-editor-container {
    border: 1px solid #ddd;
}
.sprr-modal-body .mce-content-body {
    color: #000 !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    console.log('Automation script loaded');
    
    // Open modal for new rule
    $('#add-new-rule').on('click', function() {
        console.log('Add new rule button clicked');
        // Reset form for new rule - manually set all fields
        $('#rule_name').val('');
        $('#subscription_status').val('');
        $('#delay_days').val('1');
        $('#rule_enabled').prop('checked', true);
        $('#rule-form-modal h2').text('<?php echo esc_js(__('Add New Automation Rule', 'subscriptions-renewal-reminders')); ?>');
        $('input[name="rule_id"]').val('');
        console.log('Rule ID set to: ' + $('input[name="rule_id"]').val());
        // Set default template
        $('#selected_template').val('default_template');
        // Clear template preview
        $('#automation-template-preview').hide();
        $('#rule-form-modal').show();
    });
    
    // Close modal
    $('.sprr-modal-close').on('click', function() {
        console.log('Modal close button clicked');
        $('#rule-form-modal').hide();
        // Reset form if not editing
        if (!<?php echo $edit_rule ? 'true' : 'false'; ?>) {
            $('#rule-form-modal form')[0].reset();
        }
    });
    
    // Close modal on outside click
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('sprr-modal')) {
            $('#rule-form-modal').hide();
        }
    });
    
    // Toggle rule enabled/disabled
    $('.toggle-rule').on('change', function() {
        var ruleId = $(this).data('rule-id');
        var enabled = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sprr_toggle_automation_rule',
                rule_id: ruleId,
                enabled: enabled,
                nonce: '<?php echo wp_create_nonce('sprr_toggle_rule_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var message = enabled ? 'Rule activated' : 'Rule deactivated';
                    $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>')
                        .insertAfter('.wrap h1')
                        .delay(3000)
                        .fadeOut();
                }
            }
        });
    });

    // Template preview functionality
    var templates = <?php echo json_encode($custom_templates); ?>;

    function showAutomationTemplatePreview() {
        var selectedId = $('#selected_template').val();
        if (templates[selectedId]) {
            var template = templates[selectedId];
            var previewHtml = '<p><strong><?php esc_html_e('Subject:', 'subscriptions-renewal-reminders'); ?></strong> ' + (template.subject || '') + '</p>';
            previewHtml += '<div style="background: #fff; padding: 15px; margin-top: 10px; max-height: 300px; overflow-y: auto;">' + (template.content || '') + '</div>';
            $('#automation-template-preview-content').html(previewHtml);
            $('#automation-template-preview').slideDown();
        } else {
            $('#automation-template-preview').slideUp();
        }
    }

    $('#selected_template').on('change', showAutomationTemplatePreview);
    
    // Show preview on page load if template is selected
    if ($('#selected_template').val()) {
        showAutomationTemplatePreview();
    }

    // Pro Modal Logic
    $('.sprr-show-pro-modal').on('click', function(e) {
        e.preventDefault();
        $('#sprr-pro-feature-modal').fadeIn(200);
    });

    $('.sprr-modal-close').on('click', function() {
        $(this).closest('.sprr-modal').fadeOut(200);
    });

    $(window).on('click', function(event) {
        if ($(event.target).hasClass('sprr-modal')) {
            $('.sprr-modal').fadeOut(200);
        }
    });
});
</script>
