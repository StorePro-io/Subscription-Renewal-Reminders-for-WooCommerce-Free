<?php
/**
 * Drag-and-Drop Email Builder
 * 
 * @package RenewalReminders
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get template data if editing or using a template
$edit_id = isset($_GET['edit_template']) ? sanitize_text_field($_GET['edit_template']) : '';
$template_name = isset($_GET['template_name']) ? sanitize_text_field($_GET['template_name']) : '';
$template_subject = isset($_GET['template_subject']) ? sanitize_text_field($_GET['template_subject']) : '';
$template_content = isset($_GET['template_content']) ? wp_kses_post(urldecode($_GET['template_content'])) : '';

// Enqueue WordPress media library for image selection
if (function_exists('wp_enqueue_media')) {
    wp_enqueue_media();
}
?>

<div style="background: #fff; padding: 0; margin-top: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
    <div style="padding: 20px; border-bottom: 1px solid #ddd;">
        <h2 style="margin: 0;">🎨 Email Builder</h2>
        <p class="description">Drag and drop components to build your perfect email template.</p>
    </div>

    <div class="sprr-builder-container">
        <!-- Left Sidebar - Components & Editor -->
        <div class="sprr-builder-sidebar">
            <div class="sprr-sidebar-section">
                <h3>Template Info</h3>
                <div class="sprr-form-group">
                    <label>Template Name</label>
                    <input type="text" id="template_name" placeholder="My Email Template" 
                           value="<?php echo esc_attr($template_name); ?>" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                </div>
                <div class="sprr-form-group">
                    <label>Email Subject</label>
                    <input type="text" id="template_subject" placeholder="Subject line here..." 
                           value="<?php echo esc_attr($template_subject); ?>" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                </div>
            </div>

            <div class="sprr-sidebar-section sprr-components-section" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">
                <h3>Components</h3>
                <p class="description">Drag into preview</p>
                
                <div class="sprr-component-list">
                    <div class="sprr-component" draggable="true" data-type="header">
                        <span class="dashicons dashicons-editor-aligncenter"></span>
                        <span>Header</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="text">
                        <span class="dashicons dashicons-editor-paragraph"></span>
                        <span>Text Block</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="button">
                        <span class="dashicons dashicons-admin-links"></span>
                        <span>Button</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="image">
                        <span class="dashicons dashicons-format-image"></span>
                        <span>Image</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="divider">
                        <span class="dashicons dashicons-minus"></span>
                        <span>Divider</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="spacer">
                        <span class="dashicons dashicons-image-flip-vertical"></span>
                        <span>Spacer</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="columns">
                        <span class="dashicons dashicons-columns"></span>
                        <span>2 Columns</span>
                    </div>
                    <div class="sprr-component" draggable="true" data-type="list">
                        <span class="dashicons dashicons-editor-ul"></span>
                        <span>List</span>
                    </div>
                </div>
            </div>

            <div class="sprr-sidebar-section sprr-edit-section" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">
                <h4>Edit Selected</h4>
                <div id="sprr-properties-panel">
                    <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
                        <span class="dashicons dashicons-admin-settings" style="font-size: 32px; opacity: 0.3;"></span>
                        <p>Click a component to edit</p>
                    </div>
                </div>
            </div>


        </div>

        <!-- Right Side - Preview Canvas -->
        <div class="sprr-builder-canvas">
            <div class="sprr-canvas-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; padding: 15px 20px; background: #f9f9f9;">
                    <span class="dashicons dashicons-visibility" style="margin-right: 5px;"></span>
                    Email Preview
                </h3>
                <button type="button" id="sprr-edit-html-btn" class="button" style="margin-right: 15px;">
                    <span class="dashicons dashicons-editor-code"></span>
                    Full Editor
                </button>
            </div>
            
            <div id="sprr-canvas-area" class="sprr-canvas-area">
                <?php if (!empty($template_content)): ?>
                    <?php echo $template_content; ?>
                <?php else: ?>
                    <div class="sprr-empty-canvas">
                        <span class="dashicons dashicons-welcome-add-page" style="font-size: 64px; opacity: 0.2;"></span>
                        <p>Drag components here to start building your email</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Builder Footer -->
    <div style="padding: 20px; border-top: 1px solid #ddd; background: #f9f9f9; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <button type="button" id="sprr-preview-btn" class="button">
                <span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span>
                Preview
            </button>
            <button type="button" id="sprr-clear-canvas" class="button" onclick="return confirm('Clear all content?');">
                <span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
                Clear
            </button>
        </div>
        <div>
            <button type="button" id="sprr-save-template" class="button button-primary" style="min-width: 150px;">
                <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span>
                Save Template
            </button>
        </div>
    </div>
</div>

<!-- Limit Modal (same style and content as Template Library) -->
<div id="sprr-limit-modal" class="sprr-modal" style="display: none;">
    <div class="sprr-modal-content" style="max-width: 560px;">
        <div class="sprr-modal-header">
            <h2>Limit Reached</h2>
            <button type="button" class="sprr-modal-close">&times;</button>
        </div>
        <div class="sprr-modal-body" style="font-size: 14px; color: #333;">
            <p style="margin-top: 0;">
                Free version allows only 1 custom template. You already have a custom template. To add more, please upgrade.
            </p>
            <p style="margin: 10px 0; font-size: 13px; color: #444;">
                Please upgrade or delete an existing template from
                <a href="<?php echo admin_url('admin.php?page=sp-renewal-reminders-templates&template_tab=custom'); ?>" target="_blank">My Templates</a>.
            </p>
            <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:4px; padding:10px; margin-top:10px;">
                <p style="margin:0 0 10px 0; font-size:13px; color:#856404;">
                    Win-back templates and unlimited custom templates are available in Pro.
                </p>
                <a href="https://storepro.io/subscription-renewal-premium/" target="_blank" class="button button-primary sprr-upgrade-btn">Upgrade to Pro</a>
            </div>
        </div>
        <div class="sprr-modal-footer">
            <button type="button" class="button sprr-modal-close">Close</button>
        </div>
    </div>
    <style>
    /* Modal Styles */
    .sprr-modal { position: fixed; z-index: 1000000 !important; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: none; }
    .sprr-modal-content { background-color: #fff; margin: 50px auto; width: 90%; max-width: 800px; border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; }
    .sprr-modal-header { padding: 20px 30px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
    .sprr-modal-header h2 { margin: 0; }
    .sprr-modal-header .sprr-modal-close { background: none; border: none; font-size: 28px; font-weight: bold; color: #666; cursor: pointer; padding: 0; width: 30px; height: 30px; line-height: 1; }
    .sprr-modal-header .sprr-modal-close:hover { color: #000; }
    .sprr-modal-footer { padding: 15px 30px; border-top: 1px solid #ddd; text-align: right; }
    .sprr-modal-footer .button { margin-left: 10px; min-width: 120px; padding: 6px 20px; }
    .sprr-modal-footer .sprr-modal-close { font-size: inherit; font-weight: 600; width: auto; height: auto; line-height: normal; color: inherit; padding: 6px 20px; }
    .sprr-modal-body { padding: 20px 30px; max-height: 60vh; overflow-y: auto; }
    </style>
</div>

<style>
.sprr-builder-container {
    display: grid;
    grid-template-columns: 350px 1fr;
    min-height: 600px;
}

.sprr-builder-sidebar {
    padding: 20px;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    max-height: 800px;
    background: #fafafa;
}

.sprr-sidebar-section {
    margin-bottom: 20px;
}

.sprr-builder-sidebar h3,
.sprr-builder-sidebar h4 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
    text-transform: uppercase;
    color: #666;
}

.sprr-builder-sidebar h4 {
    font-size: 13px;
}

.sprr-form-group {
    margin-bottom: 15px;
}

.sprr-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    font-size: 13px;
    color: #333;
}

.sprr-component-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sprr-component {
    padding: 12px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: move;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.sprr-component:hover {
    background: #fff;
    border-color: #2271b1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sprr-component .dashicons {
    font-size: 20px;
    color: #666;
}

.sprr-shortcodes {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.sprr-shortcodes code {
    padding: 4px 8px;
    background: #f0f0f0;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
}

.sprr-shortcodes code:hover {
    background: #e0e0e0;
}

.sprr-insert-shortcode {
    background: #f0f0f1;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    color: #2271b1;
    font-weight: 500;
}

.sprr-insert-shortcode:hover {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.sprr-insert-shortcode:active {
    transform: translateY(1px);
}

.sprr-builder-canvas {
    background: #fff;
    display: flex;
    flex-direction: column;
}

.sprr-canvas-header {
    background: #f9f9f9;
    border-bottom: 2px solid #ddd;
}

.sprr-canvas-area {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
    min-height: 500px;
    background: #fff;
}

.sprr-empty-canvas {
    text-align: center;
    padding: 100px 20px;
    color: #999;
}

.sprr-canvas-area.drag-over {
    background: #e8f4f8;
    border: 2px dashed #2271b1;
}

.sprr-canvas-block {
    background: #fff;
    padding: 20px;
    margin-bottom: 15px;
    border: 2px solid transparent;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s;
    cursor: pointer;
}

.sprr-canvas-block:hover {
    border-color: #2271b1;
}

.sprr-canvas-block.selected {
    border-color: #0073aa;
    box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
}

.sprr-block-controls {
    position: absolute;
    top: -12px;
    right: 10px;
    display: none;
    gap: 5px;
}

.sprr-canvas-block:hover .sprr-block-controls,
.sprr-canvas-block.selected .sprr-block-controls {
    display: flex;
}

.sprr-block-control {
    background: #2271b1;
    color: #fff;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.sprr-block-control:hover {
    background: #135e96;
}

.sprr-block-control.delete {
    background: #dc3232;
}

.sprr-block-control.delete:hover {
    background: #a00;
}

.sprr-form-group {
    margin-bottom: 15px;
}

.sprr-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    font-size: 13px;
}

.sprr-form-group input[type="text"],
.sprr-form-group input[type="number"],
.sprr-form-group input[type="url"],
.sprr-form-group textarea,
.sprr-form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.sprr-form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.sprr-color-picker-group {
    display: grid;
    grid-template-columns: 1fr 60px;
    gap: 10px;
    align-items: center;
}

.sprr-color-picker-group input[type="color"] {
    width: 100%;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

/* Editor Tabs */
.sprr-editor-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.sprr-editor-tab {
    padding: 8px 16px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-bottom: none;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.sprr-editor-tab:first-child {
    border-radius: 3px 0 0 0;
}

.sprr-editor-tab:last-child {
    border-radius: 0 3px 0 0;
}

.sprr-editor-tab:hover {
    background: #fff;
}

.sprr-editor-tab.active {
    background: #fff;
    border-bottom: 2px solid #fff;
    margin-bottom: -1px;
    font-weight: 600;
    color: #2271b1;
}

.sprr-editor-content {
    position: relative;
}

.sprr-editor-panel {
    display: none;
}

.sprr-editor-panel.active {
    display: block;
}

.sprr-editor-panel textarea {
    width: 100%;
    min-height: 250px;
    resize: vertical;
}

/* Dragging states */
.sprr-canvas-block.block-moved {
    animation: blockMove 0.3s ease;
}

@keyframes blockMove {
    0%, 100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(5px);
        background: #f0f8ff;
    }
}

.sprr-block-control.move-up,
.sprr-block-control.move-down {
    background: #2271b1;
}

.sprr-block-control.move-up:hover,
.sprr-block-control.move-down:hover {
    background: #135e96;
}

/* Full Editor Modal */
.sprr-full-tab.active {
    background: #2271b1 !important;
    color: #fff !important;
    border-color: #2271b1 !important;
}

.sprr-full-panel {
    display: none;
}

.sprr-full-panel.active {
    display: block;
}

/* Helper: hide sections */
.sprr-hidden {
    display: none !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    let selectedBlock = null;
    let draggedType = null;
    let blockIdCounter = 0;
    
    // Toggle sidebar mode: 'edit' hides Components, shows Edit panel; 'browse' shows Components, hides Edit panel
    function toggleSidebarEditingMode(mode) {
        const $components = $('.sprr-components-section');
        const $edit = $('.sprr-edit-section');
        if (mode === 'edit') {
            $components.addClass('sprr-hidden');
            $edit.removeClass('sprr-hidden');
            $('.sprr-builder-sidebar').scrollTop(0);
        } else {
            $components.removeClass('sprr-hidden');
            $edit.addClass('sprr-hidden');
            // Reset properties panel content and cleanup editors
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                tinymce.get('block-visual-editor').remove();
            }
            $('#sprr-properties-panel').html(`
                <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
                    <span class="dashicons dashicons-admin-settings" style="font-size: 32px; opacity: 0.3;"></span>
                    <p>Click a component to edit</p>
                </div>
            `);
        }
    }
    
    // Component templates
    const componentTemplates = {
        header: function() {
            return `
                <div style="text-align: center; padding: 40px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h1 style="color: #fff; margin: 0; font-size: 32px;">Your Header Here</h1>
                </div>
            `;
        },
        text: function() {
            return `
                <p style="font-size: 16px; color: #333; line-height: 1.6; margin: 0;">
                    Add your text content here. You can use shortcodes like {first_name} to personalize your email.
                </p>
            `;
        },
        button: function() {
            return `
                <div style="text-align: center; margin: 20px 0;">
                    <a href="#" style="background: #2271b1; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Click Here</a>
                </div>
            `;
        },
        image: function() {
            return `
                <div style="text-align: center; margin: 20px 0;">
                    <img src="https://via.placeholder.com/600x300" alt="Image" style="max-width: 100%; height: auto; border-radius: 4px;">
                </div>
            `;
        },
        divider: function() {
            return `
                <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            `;
        },
        spacer: function() {
            return `
                <div style="height: 40px;"></div>
            `;
        },
        columns: function() {
            return `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
                        <p style="margin: 0;">Column 1 content</p>
                    </div>
                    <div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
                        <p style="margin: 0;">Column 2 content</p>
                    </div>
                </div>
            `;
        },
        list: function() {
            return `
                <ul style="font-size: 16px; color: #333; line-height: 1.8; padding-left: 20px;">
                    <li>List item 1</li>
                    <li>List item 2</li>
                    <li>List item 3</li>
                </ul>
            `;
        }
    };
    
    // Drag start
    $('.sprr-component').on('dragstart', function(e) {
        draggedType = $(this).data('type');
        e.originalEvent.dataTransfer.effectAllowed = 'copy';
    });
    
    // Canvas drag over
    $('#sprr-canvas-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('#sprr-canvas-area').on('dragleave', function() {
        $(this).removeClass('drag-over');
    });
    
    // Canvas drop
    $('#sprr-canvas-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        if (draggedType && componentTemplates[draggedType]) {
            $('.sprr-empty-canvas').remove();
            
            const blockId = 'block_' + (++blockIdCounter);
            const content = componentTemplates[draggedType]();
            
            const blockHtml = `
                <div class="sprr-canvas-block" data-block-id="${blockId}" data-type="${draggedType}">
                    <div class="sprr-block-controls">
                        <button type="button" class="sprr-block-control move-up" title="Move Up">
                            <span class="dashicons dashicons-arrow-up-alt2" style="font-size: 14px;"></span>
                        </button>
                        <button type="button" class="sprr-block-control move-down" title="Move Down">
                            <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px;"></span>
                        </button>
                        <button type="button" class="sprr-block-control delete" title="Delete">
                            <span class="dashicons dashicons-no" style="font-size: 14px;"></span>
                        </button>
                    </div>
                    ${content}
                </div>
            `;
            
            $(this).append(blockHtml);
            draggedType = null;
        }
    });
    
    // Block selection
    $(document).on('click', '.sprr-canvas-block', function(e) {
        if (!$(e.target).closest('.sprr-block-control').length) {
            // Remove previous TinyMCE instance if exists
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                tinymce.get('block-visual-editor').remove();
            }

            $('.sprr-canvas-block').removeClass('selected');
            $(this).addClass('selected');
            selectedBlock = $(this);
            showProperties($(this));
            // Switch to editing mode on selection
            toggleSidebarEditingMode('edit');
        }
    });
    
    // Move block up
    $(document).on('click', '.sprr-block-control.move-up', function(e) {
        e.stopPropagation();
        const block = $(this).closest('.sprr-canvas-block');
        const prev = block.prev('.sprr-canvas-block');
        
        if (prev.length) {
            block.insertBefore(prev);
            block.addClass('block-moved');
            setTimeout(() => block.removeClass('block-moved'), 300);
        }
    });
    
    // Move block down
    $(document).on('click', '.sprr-block-control.move-down', function(e) {
        e.stopPropagation();
        const block = $(this).closest('.sprr-canvas-block');
        const next = block.next('.sprr-canvas-block');
        
        if (next.length) {
            block.insertAfter(next);
            block.addClass('block-moved');
            setTimeout(() => block.removeClass('block-moved'), 300);
        }
    });
    
    // Delete block
    $(document).on('click', '.sprr-block-control.delete', function(e) {
        e.stopPropagation();
        if (confirm('Delete this block?')) {
            $(this).closest('.sprr-canvas-block').remove();
            $('#sprr-properties-panel').html(`
                <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
                    <span class="dashicons dashicons-admin-settings" style="font-size: 32px; opacity: 0.3;"></span>
                    <p>Click a component to edit</p>
                </div>
            `);
            
            if ($('.sprr-canvas-block').length === 0) {
                $('#sprr-canvas-area').html(`
                    <div class="sprr-empty-canvas">
                        <span class="dashicons dashicons-welcome-add-page" style="font-size: 64px; opacity: 0.2;"></span>
                        <p>Drag components here to start building your email</p>
                    </div>
                `);
            }
        }
    });
    
    // Helper to convert RGB to HEX
    function rgbToHex(rgb) {
        if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return '#ffffff';
        const res = rgb.match(/\d+/g);
        if (!res) return rgb;
        return "#" + ((1 << 24) + (parseInt(res[0]) << 16) + (parseInt(res[1]) << 8) + parseInt(res[2])).toString(16).slice(1);
    }

    // Show properties panel
    function showProperties(block) {
        const type = block.data('type');
        const blockId = block.data('block-id');
        let propertiesHtml = '<div style="padding: 10px;">';
        
        propertiesHtml += '<h4 style="margin-top: 0; text-transform: capitalize; border-bottom: 2px solid #2271b1; padding-bottom: 10px; margin-bottom: 20px;">' + type + ' Settings</h4>';

        // Get main element for styling logic
        const wrapper = block.children().not('.sprr-block-controls').first();
        
        // Find the "most relevant" element to read styles from
        // e.g., for headers we want the H1 color, for buttons we want the A tag background
        let styleTarget = wrapper;
        if (type === 'header') styleTarget = wrapper.find('h1, h2, h3').first();
        if (type === 'button') styleTarget = wrapper.find('a').first();
        if (styleTarget.length === 0) styleTarget = wrapper;

        // Background target is usually the wrapper
        let bgTarget = wrapper;
        if (type === 'button') bgTarget = wrapper.find('a').first();

        const styles = {
            backgroundColor: rgbToHex(bgTarget.css('background-color')),
            color: rgbToHex(styleTarget.css('color')),
            fontSize: parseInt(styleTarget.css('font-size')) || 16,
            paddingTop: parseInt(wrapper.css('padding-top')) || 0,
            paddingBottom: parseInt(wrapper.css('padding-bottom')) || 0,
            paddingLeft: parseInt(wrapper.css('padding-left')) || 0,
            paddingRight: parseInt(wrapper.css('padding-right')) || 0,
            marginTop: parseInt(wrapper.css('margin-top')) || 0,
            marginBottom: parseInt(wrapper.css('margin-bottom')) || 0,
            marginLeft: parseInt(wrapper.css('margin-left')) || 0,
            marginRight: parseInt(wrapper.css('margin-right')) || 0
        };

        // Special check for gradients (since color pickers can't show them)
        const rawBg = bgTarget.get(0).style.background || bgTarget.get(0).style.backgroundColor;
        if (rawBg && rawBg.indexOf('gradient') !== -1) {
            styles.backgroundColor = '#667eea'; // Default to start of common gradient if it's a gradient
        }

        // Styling Section
        propertiesHtml += `
            <div class="sprr-style-section" style="border: 1px solid #ddd; padding: 12px; margin-bottom: 20px; border-radius: 4px; background: #f9f9f9;">
                <h5 style="margin-top: 0; margin-bottom: 15px; font-size: 14px;">🎨 Styling Controls</h5>
                
                <div class="sprr-form-group">
                    <label>Background Color</label>
                    <div class="sprr-color-picker-group">
                        <input type="text" class="style-input-sync" data-prop="background-color" data-target="bg-text" value="${styles.backgroundColor}" style="font-size: 11px;">
                        <input type="color" class="style-input-sync" data-prop="background-color" data-target="bg-picker" value="${styles.backgroundColor}">
                    </div>
                </div>

                <div class="sprr-form-group">
                    <label>Text Color</label>
                    <div class="sprr-color-picker-group">
                        <input type="text" class="style-input-sync" data-prop="color" data-target="color-text" value="${styles.color}" style="font-size: 11px;">
                        <input type="color" class="style-input-sync" data-prop="color" data-target="color-picker" value="${styles.color}">
                    </div>
                </div>

                <div class="sprr-form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label>Margin Top</label>
                        <input type="number" class="style-input-sync" data-prop="margin-top" value="${styles.marginTop}">
                    </div>
                    <div>
                        <label>Margin Bottom</label>
                        <input type="number" class="style-input-sync" data-prop="margin-bottom" value="${styles.marginBottom}">
                    </div>
                </div>

                <div class="sprr-form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label>Margin Left</label>
                        <input type="number" class="style-input-sync" data-prop="margin-left" value="${styles.marginLeft}">
                    </div>
                    <div>
                        <label>Margin Right</label>
                        <input type="number" class="style-input-sync" data-prop="margin-right" value="${styles.marginRight}">
                    </div>
                </div>

                <div class="sprr-form-group">
                    <label>Horizontal Margin (L/R)</label>
                    <input type="number" class="style-input-sync" data-prop="margin-x" value="${Math.round((styles.marginLeft + styles.marginRight) / 2)}">
                </div>

                <div class="sprr-form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label>Padding Top</label>
                        <input type="number" class="style-input-sync" data-prop="padding-top" value="${styles.paddingTop}">
                    </div>
                    <div>
                        <label>Padding Bottom</label>
                        <input type="number" class="style-input-sync" data-prop="padding-bottom" value="${styles.paddingBottom}">
                    </div>
                </div>

                <div class="sprr-form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label>Padding Left</label>
                        <input type="number" class="style-input-sync" data-prop="padding-left" value="${styles.paddingLeft}">
                    </div>
                    <div>
                        <label>Padding Right</label>
                        <input type="number" class="style-input-sync" data-prop="padding-right" value="${styles.paddingRight}">
                    </div>
                </div>

                <div class="sprr-form-group">
                    <label>Horizontal Padding (L/R)</label>
                    <input type="number" class="style-input-sync" data-prop="padding-x" value="${Math.round((styles.paddingLeft + styles.paddingRight) / 2)}">
                </div>

                <div class="sprr-form-group">
                    <label>Font Size (px)</label>
                    <input type="number" class="style-input-sync" data-prop="font-size" value="${styles.fontSize}">
                </div>
            </div>
        `;

        // Image-specific controls: Add Media button and URL field
        if (type === 'image') {
            const imgEl = wrapper.find('img').first();
            const currentImgUrl = imgEl.length ? (imgEl.attr('src') || '') : '';
            const currentMaxWidth = imgEl.length ? (parseInt(imgEl.css('max-width')) || '') : '';
            const currentMaxHeight = imgEl.length ? (parseInt(imgEl.css('max-height')) || '') : '';
            propertiesHtml += `
                <div class="sprr-form-group" style="border: 1px solid #ddd; padding: 12px; margin-bottom: 20px; border-radius: 4px; background: #fff;">
                    <label style="font-weight: 600;">Image Source</label>
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center;">
                        <input type="text" id="sprr-image-url" value="${currentImgUrl}" placeholder="https://example.com/image.jpg" />
                        <button type="button" class="button sprr-add-media-btn" style="white-space: nowrap;">Add Media</button>
                    </div>
                    <p class="description" style="font-size: 11px; color: #666; margin-top: 8px;">Use Add Media to pick from your library or paste a direct URL.</p>
                </div>
                <div class="sprr-form-group" style="border: 1px solid #ddd; padding: 12px; margin-bottom: 20px; border-radius: 4px; background: #fff;">
                    <label style="font-weight: 600;">Image Size</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label>Max Width (px)</label>
                            <input type="number" class="style-input-sync" data-prop="max-width" value="${currentMaxWidth}" min="0" />
                        </div>
                        <div>
                            <label>Max Height (px)</label>
                            <input type="number" class="style-input-sync" data-prop="max-height" value="${currentMaxHeight}" min="0" />
                        </div>
                    </div>
                    <p class="description" style="font-size: 11px; color: #666; margin-top: 8px;">Leave blank to let the image size naturally. Setting max dimensions helps constrain large images.</p>
                </div>
            `;
        }
        
        // Common property: Edit HTML
        const currentHtml = block.html().replace(/<div class="sprr-block-controls">.*?<\/div>/s, '').trim();
        
        propertiesHtml += `
            <div class="sprr-content-editor-section" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 10px;">
                <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-weight: 600; font-size: 13px;">
                    <span>Shortcodes</span>
                    <span style="font-size: 10px; opacity: 0.6; font-weight: normal;">(Click to insert)</span>
                </label>
                <div class="sprr-shortcode-pills" style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 15px; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <span class="sprr-insert-shortcode" data-code="{first_name}">First Name</span>
                    <span class="sprr-insert-shortcode" data-code="{last_name}">Last Name</span>
                    <span class="sprr-insert-shortcode" data-code="{email}">Email</span>
                    <span class="sprr-insert-shortcode" data-code="{next_payment_date}">Renewal Date</span>
                    <span class="sprr-insert-shortcode" data-code="{subscription_link}">Sub Link</span>
                    <span class="sprr-insert-shortcode" data-code="{cancel_subscription}">Cancel Button</span>
                </div>

                <div class="sprr-editor-tabs">
                    <button type="button" class="sprr-editor-tab active" data-tab="visual">Visual</button>
                    <button type="button" class="sprr-editor-tab" data-tab="html">HTML</button>
                </div>
                <div class="sprr-editor-content">
                    <div class="sprr-editor-panel active" data-panel="visual">
                        <textarea id="block-visual-editor" class="wp-editor-area"></textarea>
                    </div>
                    <div class="sprr-editor-panel" data-panel="html">
                        <textarea id="block-html-editor" rows="10" style="font-family: monospace; font-size: 12px; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; white-space: pre; overflow-x: auto;"></textarea>
                    </div>
                </div>
                <button type="button" id="update-block-html" class="button button-small button-primary" style="margin-top: 10px; width: 100%; height: 32px;">Update Text Content</button>
            </div>
        `;
        
        propertiesHtml += '</div>';
        $('#sprr-properties-panel').html(propertiesHtml);

        // Set initial values 
        $('#block-html-editor').val(formatHTML(currentHtml));
        
        // Initialize TinyMCE for visual editor
        setTimeout(function() {
            if (typeof tinymce !== 'undefined') {
                if (tinymce.get('block-visual-editor')) {
                    tinymce.get('block-visual-editor').remove();
                }

                tinymce.init({
                    selector: '#block-visual-editor',
                    menubar: false,
                    height: 250,
                    branding: false,
                    plugins: 'link lists textcolor',
                    toolbar: 'undo redo | bold italic | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link | removeformat',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; font-size: 14px; padding: 10px; }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(currentHtml);
                            editor.focus();
                        });
                        editor.on('change keyup', function() {
                            editor.save();
                        });
                    }
                });
            } else {
                // Fallback if TinyMCE is not loaded
                $('#block-visual-editor').val(currentHtml);
                console.warn('TinyMCE not loaded, using raw textarea fallback.');
            }
        }, 50);
    }
    
    // Editor tab switching
    $(document).on('click', '.sprr-editor-tab', function() {
        const tab = $(this).data('tab');
        
        // Tab switching logic (no alert needed)
        
        // Sync content before switching
        if ($('.sprr-editor-tab[data-tab="visual"]').hasClass('active')) {
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                const visualContent = tinymce.get('block-visual-editor').getContent();
                $('#block-html-editor').val(formatHTML(visualContent));
            }
        } else {
            const htmlContent = $('#block-html-editor').val();
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                tinymce.get('block-visual-editor').setContent(htmlContent);
            }
        }
        
        $('.sprr-editor-tab').removeClass('active');
        $('.sprr-editor-panel').removeClass('active');
        $(this).addClass('active');
        $('.sprr-editor-panel[data-panel="' + tab + '"]').addClass('active');
    });

    // Shortcode insertion logic
    $(document).on('click', '.sprr-insert-shortcode', function() {
        const code = $(this).data('code');
        const isVisualActive = $('.sprr-editor-tab[data-tab="visual"]').hasClass('active');

        if (isVisualActive) {
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                tinymce.get('block-visual-editor').execCommand('mceInsertContent', false, code);
            }
        } else {
            const $htmlEditor = $('#block-html-editor');
            const cursorPos = $htmlEditor.prop('selectionStart');
            const text = $htmlEditor.val();
            const textBefore = text.substring(0, cursorPos);
            const textAfter = text.substring(cursorPos);
            
            $htmlEditor.val(textBefore + code + textAfter);
            $htmlEditor.focus();
            
            // Set cursor after the inserted code
            const newPos = cursorPos + code.length;
            $htmlEditor[0].setSelectionRange(newPos, newPos);
        }
    });

    // Style real-time sync
    $(document).on('input change', '.style-input-sync', function() {
        if (!selectedBlock) return;
        
        const prop = $(this).data('prop');
        let value = $(this).val();
        const type = selectedBlock.data('type');
        const wrapper = selectedBlock.children().not('.sprr-block-controls').first();

        // Sync text and picker if they are linked
        const targetAttr = $(this).data('target');
        if (targetAttr) {
            const group = $(this).closest('.sprr-color-picker-group');
            if (targetAttr.includes('text')) {
                group.find('input[type="color"]').val(value);
            } else {
                group.find('input[type="text"]').val(value);
            }
        }

        if (prop === 'font-size' || prop === 'padding-top' || prop === 'padding-bottom' || prop === 'padding-left' || prop === 'padding-right' || prop === 'margin-top' || prop === 'margin-bottom' || prop === 'margin-left' || prop === 'margin-right' || prop === 'margin-x' || prop === 'padding-x' || prop === 'max-width' || prop === 'max-height') {
            value += 'px';
        }

        // Apply styles to smart targets
        if (prop === 'background-color') {
            let bgTarget = wrapper;
            if (type === 'button') bgTarget = wrapper.find('a').first();
            
            // Remove any existing gradient if changing to solid color
            if (value.startsWith('#')) {
                bgTarget.css('background', 'none'); 
            }
            bgTarget.css(prop, value);
        } else if (prop === 'color' || prop === 'font-size') {
            let styleTarget = wrapper;
            if (type === 'header') styleTarget = wrapper.find('h1, h2, h3').first();
            if (type === 'button') styleTarget = wrapper.find('a').first();
            
            styleTarget.css(prop, value);
        } else if (prop === 'max-width' || prop === 'max-height') {
            const imgTarget = selectedBlock.find('img').first();
            if (imgTarget.length) {
                // Blank value removes the constraint
                if ($(this).val() === '' || parseInt($(this).val()) === 0) {
                    imgTarget.css(prop, '');
                } else {
                    imgTarget.css(prop, value);
                }
            }
        } else if (prop === 'margin-x') {
            // Apply horizontal margin to both sides
            wrapper.css('margin-left', value);
            wrapper.css('margin-right', value);
        } else if (prop === 'padding-x') {
            // Apply horizontal padding to both sides
            wrapper.css('padding-left', value);
            wrapper.css('padding-right', value);
        } else {
            // Padding always goes to wrapper
            wrapper.css(prop, value);
        }
    });

    // Image URL manual change sync
    $(document).on('input change', '#sprr-image-url', function() {
        if (!selectedBlock) return;
        const url = $(this).val();
        const img = selectedBlock.find('img').first();
        if (img.length) {
            img.attr('src', url);
        }
    });

    // Add Media button opens WP media frame and sets image
    $(document).on('click', '.sprr-add-media-btn', function() {
        // Ensure a block is selected
        if (!selectedBlock) return;
        
        const frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            const img = selectedBlock.find('img').first();
            if (img.length) {
                img.attr('src', attachment.url);
                if (attachment.alt) {
                    img.attr('alt', attachment.alt);
                }
            }
            $('#sprr-image-url').val(attachment.url);
        });
        
        frame.open();
    });
    
    // Update block HTML
    $(document).on('click', '#update-block-html', function() {
        if (selectedBlock) {
            let newHtml;
            
            if ($('.sprr-editor-tab[data-tab="visual"]').hasClass('active')) {
                // Get content from visual editor
                if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                    newHtml = tinymce.get('block-visual-editor').getContent();
                } else {
                    newHtml = $('#block-visual-editor').val();
                }
            } else {
                // Get content from HTML editor
                newHtml = $('#block-html-editor').val();
            }
            
            const controls = selectedBlock.find('.sprr-block-controls').prop('outerHTML');
            selectedBlock.html(controls + newHtml);
            
            // Keep TinyMCE instance so the Visual editor remains active
            // If needed, you can refresh content manually:
            if (typeof tinymce !== 'undefined' && tinymce.get('block-visual-editor')) {
                tinymce.get('block-visual-editor').setContent(newHtml);
            }
            // Also sync the HTML textarea
            $('#block-html-editor').val(formatHTML(newHtml));
        }
    });
    
    // Clear canvas
    $('#sprr-clear-canvas').on('click', function() {
        $('#sprr-canvas-area').html(`
            <div class="sprr-empty-canvas">
                <span class="dashicons dashicons-welcome-add-page" style="font-size: 64px; opacity: 0.2;"></span>
                <p>Drag components here to start building your email</p>
            </div>
        `);
        $('#sprr-properties-panel').html(`
            <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
                <span class="dashicons dashicons-admin-settings" style="font-size: 32px; opacity: 0.3;"></span>
                <p>Click a component to edit</p>
            </div>
        `);
        toggleSidebarEditingMode('browse');
    });
    
    // Preview
    $('#sprr-preview-btn').on('click', function() {
        const content = getCanvasHTML();
        const subject = $('#template_subject').val() || 'Email Preview';
        
        const previewWindow = window.open('', 'Email Preview', 'width=800,height=600');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${subject}</title>
                <style>body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }</style>
            </head>
            <body>
                <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 0;">
                    ${content}
                </div>
            </body>
            </html>
        `);
    });
    
    // Save template
    $('#sprr-save-template').on('click', function() {
        const name = $('#template_name').val();
        const subject = $('#template_subject').val();
        const content = getCanvasHTML();
        
        if (!name) {
            alert('Please enter a template name');
            return;
        }
        
        if (!subject) {
            alert('Please enter an email subject');
            return;
        }
        
        if (!content || content.indexOf('sprr-empty-canvas') !== -1) {
            alert('Please add some content to your template');
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'sprr_save_email_template',
                nonce: '<?php echo wp_create_nonce('sprr_save_template'); ?>',
                template_id: '<?php echo esc_js($edit_id); ?>',
                template_name: name,
                template_subject: subject,
                template_content: content
            },
            success: function(response) {
                if (response.success) {
                    alert('Template saved successfully!');
                    window.location.href = '?page=sp-renewal-reminders-templates&template_tab=custom';
                } else {
                    var msg = response.data || '';
                    if (typeof msg === 'string' && msg.indexOf('Free version allows only 1 custom template') !== -1) {
                        $('#sprr-limit-modal').fadeIn(200);
                    } else {
                        alert('Error: ' + msg);
                    }
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> Save Template');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> Save Template');
            }
        });
    });
    
    // Get canvas HTML
    function getCanvasHTML() {
        const canvas = $('#sprr-canvas-area').clone();
        canvas.find('.sprr-block-controls').remove();
        canvas.find('.sprr-canvas-block').each(function() {
            const content = $(this).html();
            $(this).replaceWith(content);
        });
        return canvas.html();
    }
    


    // Edit Full HTML Button
    $('#sprr-edit-html-btn').on('click', function() {
        const currentContent = $('#sprr-canvas-area').html();
        
        // Create modal
        const modal = $('<div id="sprr-full-editor-modal" style="display: none; position: fixed; z-index: 100000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);"></div>');
        
        const modalContent = $(`
            <div style="position: relative; background-color: #fff; margin: 5% auto; padding: 0; width: 90%; max-width: 1200px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-height: 85vh; display: flex; flex-direction: column;">
                <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">
                        <span class="dashicons dashicons-editor-code" style="margin-right: 5px;"></span>
                        Full Template Editor
                    </h3>
                    <div class="sprr-full-editor-tabs" style="display: flex; gap: 10px;">
                        <button type="button" class="button sprr-full-tab <?php echo !sprr_is_premium_active() ? 'active' : ''; ?>" data-tab="visual">Visual</button>
                        <button type="button" class="button sprr-full-tab <?php echo sprr_is_premium_active() ? 'active' : ''; ?>" data-tab="html">HTML</button>
                    </div>
                    <button type="button" class="sprr-close-modal" style="background: none; border: none; font-size: 28px; cursor: pointer; line-height: 1;">&times;</button>
                </div>
                <div id="sprr-full-editor-loader" style="position: absolute; inset: 0; background: rgba(255,255,255,0.85); display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <div style="display:flex; align-items:center; gap:10px; color:#555; font-size:14px;">
                        <span class="dashicons dashicons-update" style="font-size:22px; animation: sprr-spin 1s linear infinite;"></span>
                        Loading editor...
                    </div>
                </div>
                <div style="padding: 20px; overflow-y: auto; flex: 1;">
                    <div class="sprr-full-panel visual-panel <?php echo !sprr_is_premium_active() ? 'active' : ''; ?>" style="display: <?php echo !sprr_is_premium_active() ? 'block' : 'none'; ?>;">
                        <textarea id="sprr-full-visual-editor" style="width: 100%; min-height: 400px;"></textarea>
                    </div>
                    <div class="sprr-full-panel html-panel <?php echo sprr_is_premium_active() ? 'active' : ''; ?>" style="display: <?php echo sprr_is_premium_active() ? 'block' : 'none'; ?>;">
                        <textarea id="sprr-full-html-code" style="width: 100%; min-height: 400px; font-family: 'Courier New', monospace; font-size: 13px; border: 1px solid #ddd; padding: 15px; line-height: 1.6;"></textarea>
                    </div>
                </div>
                <div style="padding: 20px; border-top: 1px solid #ddd; text-align: right; background: #f9f9f9;">
                    <button type="button" class="sprr-close-modal button" style="margin-right: 10px;">Cancel</button>
                    <button type="button" id="sprr-update-full-content" class="button button-primary">Update Content</button>
                </div>
            </div>
        `);
        
        modal.append(modalContent);
        $('body').append(modal);
        modal.fadeIn(200);

        const initialContent = formatHTML(currentContent);
        $('#sprr-full-html-code').val(initialContent);

        // Initialize TinyMCE for full visual editor
        setTimeout(function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#sprr-full-visual-editor',
                    menubar: true,
                    height: 400,
                    plugins: 'link lists textcolor image',
                    toolbar: 'undo redo | formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image | removeformat',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; padding: 20px; }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(currentContent);
                            $('#sprr-full-editor-loader').hide();
                        });
                    }
                });
            } else {
                // TinyMCE not available; hide loader and rely on HTML panel
                $('#sprr-full-editor-loader').hide();
            }
        }, 100);

        // Tab switching for full editor
        $('.sprr-full-tab').on('click', function() {
            const tab = $(this).data('tab');
            $('.sprr-full-tab').removeClass('active');
            $(this).addClass('active');

            if (tab === 'visual') {
                const htmlContent = $('#sprr-full-html-code').val();
                if (typeof tinymce !== 'undefined' && tinymce.get('sprr-full-visual-editor')) {
                    tinymce.get('sprr-full-visual-editor').setContent(htmlContent);
                }
                $('.html-panel').hide();
                $('.visual-panel').show();
            } else {
                if (typeof tinymce !== 'undefined' && tinymce.get('sprr-full-visual-editor')) {
                    const visualContent = tinymce.get('sprr-full-visual-editor').getContent();
                    $('#sprr-full-html-code').val(formatHTML(visualContent));
                }
                $('.visual-panel').hide();
                $('.html-panel').show();
            }
        });

        // Update content button
        $('#sprr-update-full-content').on('click', function() {
            let finalContent;
            if ($('.sprr-full-tab[data-tab="visual"]').hasClass('active')) {
                if (typeof tinymce !== 'undefined' && tinymce.get('sprr-full-visual-editor')) {
                    finalContent = tinymce.get('sprr-full-visual-editor').getContent();
                } else {
                    finalContent = $('#sprr-canvas-area').html(); // Fallback if visual failed
                }
            } else {
                finalContent = $('#sprr-full-html-code').val();
            }

            $('#sprr-canvas-area').html(finalContent);
            
            if (typeof tinymce !== 'undefined' && tinymce.get('sprr-full-visual-editor')) {
                tinymce.get('sprr-full-visual-editor').remove();
            }
            
            modal.fadeOut(200, function() {
                $(this).remove();
            });
        });

        // Close modal
        $('.sprr-close-modal').on('click', function() {
            if (typeof tinymce !== 'undefined' && tinymce.get('sprr-full-visual-editor')) {
                tinymce.get('sprr-full-visual-editor').remove();
            }
            modal.fadeOut(200, function() {
                $(this).remove();
            });
        });
    });

    // Function to format HTML
    function formatHTML(html) {
        if (!html) return '';
        let formatted = html;
        formatted = formatted.replace(/</g, '\n<').replace(/>/g, '>\n').replace(/\n\s*\n/g, '\n');
        const lines = formatted.trim().split('\n');
        let indentLevel = 0;
        const indentSize = 2;
        const formattedLines = lines.map(line => {
            const trimmed = line.trim();
            if (!trimmed) return '';
            if (trimmed.match(/^<\/\w+/)) indentLevel = Math.max(0, indentLevel - 1);
            const indented = ' '.repeat(indentLevel * indentSize) + trimmed;
            if (trimmed.match(/^<\w+[^>]*>$/) && !trimmed.match(/\/>$/)) indentLevel++;
            return indented;
        });
        return formattedLines.join('\n');
    }
    
    // Click anywhere in the canvas outside a block should revert to browse mode
    $('#sprr-canvas-area').on('click', function(e) {
        const clickedInsideBlock = $(e.target).closest('.sprr-canvas-block').length > 0;
        if (!clickedInsideBlock) {
            selectedBlock = null;
            $('.sprr-canvas-block').removeClass('selected');
            toggleSidebarEditingMode('browse');
        }
    });

    // Limit modal close handlers
    $(document).on('click', '.sprr-modal-close', function() {
        $('#sprr-limit-modal').fadeOut(200);
    });
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('sprr-modal')) {
            $('#sprr-limit-modal').fadeOut(200);
        }
    });
});
</script>
