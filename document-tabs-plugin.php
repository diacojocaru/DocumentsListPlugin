<?php
/*
Plugin Name: Document Tabs Plugin
Description: Plugin for managing documents in customized tabs. Each category (year) contains subcategories, and documents are added only at the subcategory level.
Version: 1.1
Author: Diana Cojocaru / Developful.ro
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

class Document_Tabs_Plugin {
    private $option_name = 'document_tabs_data';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('document_tabs', array($this, 'document_tabs_shortcode'));
    }

    // Adds the admin page
    public function add_admin_menu() {
        add_menu_page(
            'Document Tabs',
            'Document Tabs',
            'manage_options',
            'document-tabs',
            array($this, 'admin_page'),
            'dashicons-media-document',
            20
        );
    }

    // Loads necessary scripts in the admin area
    public function enqueue_admin_scripts($hook) {
        if ( $hook != 'toplevel_page_document-tabs' ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'document-tabs-admin',
            plugin_dir_url(__FILE__) . 'document-tabs-admin.js',
            array('jquery'),
            '1.1',
            true
        );
    }

    // Admin settings page
    public function admin_page() {
        // Form processing
        if (isset($_POST['document_tabs_data'])) {
            check_admin_referer('document_tabs_nonce_action', 'document_tabs_nonce_field');
            $data = json_decode(stripslashes($_POST['document_tabs_data']), true);
            update_option($this->option_name, $data);
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }
        $data = get_option($this->option_name, array());
        ?>
        <div class="wrap">
            <h1>Document Tabs Settings</h1>
            <p>Create categories (years) and for each, add subcategories with documents.</p>
            <form method="post" id="document-tabs-form">
                <?php wp_nonce_field('document_tabs_nonce_action', 'document_tabs_nonce_field'); ?>
                <div id="categories-container">
                    <?php if(!empty($data)) : ?>
                        <?php foreach($data as $index => $cat): ?>
                            <div class="category-item" data-index="<?php echo esc_attr($index); ?>">
                                <input type="text" class="category-name" placeholder="Category name (year)" value="<?php echo esc_attr($cat['name']); ?>" />
                                <button class="delete-category button" style="margin-left:10px;">Delete Category</button>
                                <div class="subcategories-container">
                                    <?php if(isset($cat['subcategories']) && is_array($cat['subcategories'])): ?>
                                        <?php foreach($cat['subcategories'] as $subIndex => $subcat): ?>
                                            <div class="subcategory-item" data-index="<?php echo esc_attr($subIndex); ?>" style="margin-left:20px; border:1px dashed #ddd; padding:10px; margin-top:10px;">
                                                <input type="text" class="subcategory-name" placeholder="Subcategory name" value="<?php echo esc_attr($subcat['name']); ?>" />
                                                <button class="select-documents button">Select Documents</button>
                                                <button class="delete-subcategory button" style="margin-left:10px;">Delete Subcategory</button>
                                                <input type="hidden" class="document-ids" value="<?php echo esc_attr(implode(',', $subcat['documents'])); ?>" />
                                                <div class="document-preview">
                                                    <?php 
                                                    if(!empty($subcat['documents'])){
                                                        $doc_ids = $subcat['documents'];
                                                        $file_names = array();
                                                        foreach($doc_ids as $doc_id) {
                                                            $file = get_post($doc_id);
                                                            if($file) {
                                                                $file_names[] = $file->post_title;
                                                            }
                                                        }
                                                        echo esc_html(implode(', ', $file_names));
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button class="add-subcategory button" style="margin-left:10px;">Add Subcategory</button>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button id="add-category" class="button">Add Category</button>
                <br><br>
                <!-- Hidden field for the JSON data -->
                <input type="hidden" name="document_tabs_data" id="document_tabs_data" value="" />
                <?php submit_button(); ?>
            </form>
        </div>
        <style>
            .category-item {
                border: 1px solid #ddd;
                padding: 10px;
                margin-bottom: 10px;
            }
            .document-preview {
                margin-top: 5px;
                font-style: italic;
            }
            .subcategory-item {
                margin-left: 20px;
                border: 1px dashed #ddd;
                padding: 10px;
                margin-top: 10px;
            }
        </style>
        <?php
    }

public function document_tabs_shortcode() {
    $data = get_option($this->option_name, array());
    if(empty($data)) {
        return '<p>No documents available.</p>';
    }
    ob_start();
    ?>
     <style>
    /* Styling omitted here for brevity — kept as-is from original */
    </style>

    <div class="tabs-container">
        <!-- Main tabs (years) -->
        <ul class="tabs" id="main-tabs">
        <?php foreach($data as $index => $cat): ?>
            <li data-tab="tab-<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                <?php echo esc_html($cat['name']); ?>
            </li>
        <?php endforeach; ?>
        </ul>

        <?php foreach($data as $index => $cat): ?>
        <div id="tab-<?php echo $index; ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>">

            <!-- Secondary tabs (subcategories) -->
            <?php if (!empty($cat['subcategories'])): ?>
                <ul class="tabs sub-tabs" id="sub-tabs-<?php echo $index; ?>">
                <?php foreach($cat['subcategories'] as $subIndex => $subcat): ?>
                    <li data-subtab="subtab-<?php echo $index.'-'.$subIndex; ?>" class="<?php echo $subIndex === 0 ? 'active' : ''; ?>">
                        <?php echo esc_html($subcat['name']); ?>
                    </li>
                <?php endforeach; ?>
                </ul>

                <?php foreach($cat['subcategories'] as $subIndex => $subcat): ?>
                <div id="subtab-<?php echo $index.'-'.$subIndex; ?>" class="tab-content subtab-content <?php echo $subIndex === 0 ? 'active' : ''; ?>">
                    <table class="documentTable">
                        <tbody>
                        <?php if (!empty($subcat['documents'])): ?>
                            <?php foreach($subcat['documents'] as $doc_id): 
                                $url = wp_get_attachment_url($doc_id);
                                $title = get_the_title($doc_id);
                            ?>
                                <tr><td><a href="<?php echo esc_url(add_query_arg('v', time(), $url)); ?>" target="_blank"><?php echo esc_html($title); ?></a></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td><em>No entries in this subcategory.</em></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><em>There are no subcategories for this category.</em></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
   document.addEventListener("DOMContentLoaded", function () {
    // Main tabs (categories)
    const mainTabs = document.querySelectorAll('.tabs#main-tabs li');
    const mainTabContents = document.querySelectorAll('.tabs-container > .tab-content');

    mainTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');

            // Deactivate all main tabs and their contents
            mainTabs.forEach(t => t.classList.remove('active'));
            mainTabContents.forEach(c => c.classList.remove('active'));

            // Activate the selected main tab and its content
            this.classList.add('active');
            const activeContent = document.getElementById(target);
            activeContent.classList.add('active');

            // Activate the first subtab and its content, if available
            const subTabs = activeContent.querySelectorAll('.sub-tabs li');
            const subTabContents = activeContent.querySelectorAll('.subtab-content');

            if (subTabs.length > 0) {
                subTabs.forEach(st => st.classList.remove('active'));
                subTabContents.forEach(stc => stc.classList.remove('active'));

                subTabs[0].classList.add('active');
                subTabContents[0].classList.add('active');
            }
        });
    });

    // Subcategory tabs (sub-tabs)
    document.querySelectorAll('.sub-tabs').forEach(subTabContainer => {
        const subtabs = subTabContainer.querySelectorAll('li');
        subtabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent click propagation to main tabs

                const target = this.getAttribute('data-subtab');

                // Deactivate all subtabs in the current container and their contents
                subtabs.forEach(t => t.classList.remove('active'));

                const tabContentContainer = subTabContainer.closest('.tab-content');
                tabContentContainer.querySelectorAll('.subtab-content').forEach(c => c.classList.remove('active'));

                // Activate the subtab and its corresponding content
                this.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });
    });
});
    </script>
    <?php
    return ob_get_clean();
}
}
new Document_Tabs_Plugin();
?>
