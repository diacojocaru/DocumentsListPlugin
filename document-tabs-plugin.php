<?php
/*
Plugin Name: Document Tabs Plugin
Description: Plugin pentru gestionarea documentelor în taburi personalizate. Fiecare categorie (an) conține subcategorii, iar documentele se adaugă doar la nivelul subcategoriilor.
Version: 1.1
Author: Numele tău
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Previne accesul direct
}

class Document_Tabs_Plugin {
    private $option_name = 'document_tabs_data';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('document_tabs', array($this, 'document_tabs_shortcode'));
    }

    // Adaugă pagina de administrare
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

    // Încarcă scripturile necesare în zona de admin
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

    // Pagina de setări din admin
    public function admin_page() {
        // Procesare formular
        if (isset($_POST['document_tabs_data'])) {
            check_admin_referer('document_tabs_nonce_action', 'document_tabs_nonce_field');
            $data = json_decode(stripslashes($_POST['document_tabs_data']), true);
            update_option($this->option_name, $data);
            echo '<div class="updated"><p>Setări salvate!</p></div>';
        }
        $data = get_option($this->option_name, array());
        ?>
        <div class="wrap">
            <h1>Setări Document Tabs</h1>
            <p>Creează categorii (ani) și pentru fiecare adaugă subcategorii cu documente.</p>
            <form method="post" id="document-tabs-form">
                <?php wp_nonce_field('document_tabs_nonce_action', 'document_tabs_nonce_field'); ?>
                <div id="categories-container">
                    <?php if(!empty($data)) : ?>
                        <?php foreach($data as $index => $cat): ?>
                            <div class="category-item" data-index="<?php echo esc_attr($index); ?>">
                                <input type="text" class="category-name" placeholder="Nume categorie (an)" value="<?php echo esc_attr($cat['name']); ?>" />
                                <button class="delete-category button" style="margin-left:10px;">Șterge Categorie</button>
                                <div class="subcategories-container">
                                    <?php if(isset($cat['subcategories']) && is_array($cat['subcategories'])): ?>
                                        <?php foreach($cat['subcategories'] as $subIndex => $subcat): ?>
                                            <div class="subcategory-item" data-index="<?php echo esc_attr($subIndex); ?>" style="margin-left:20px; border:1px dashed #ddd; padding:10px; margin-top:10px;">
                                                <input type="text" class="subcategory-name" placeholder="Nume subcategorie" value="<?php echo esc_attr($subcat['name']); ?>" />
                                                <button class="select-documents button">Selectează Documente</button>
                                                <button class="delete-subcategory button" style="margin-left:10px;">Șterge Subcategorie</button>
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
                                <button class="add-subcategory button" style="margin-left:10px;">Adaugă Subcategorie</button>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button id="add-category" class="button">Adaugă categorie</button>
                <br><br>
                <!-- Câmpul hidden pentru JSON-ul datelor -->
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
        return '<p>Nu sunt documente disponibile.</p>';
    }
    ob_start();
    ?>
     <style>
    body, .tabs-container, .tabs-container * {
      font-family: 'Arimo', sans-serif;
      color: black;
    }
    ul.tabs {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
      background-color: white;
    }
    ul.tabs li {
      padding: 10px 20px;
      cursor: pointer;
      margin-right: 2px;
      font-size: 18px;
      font-weight: 600;
      position: relative;
      transition: color 0.3s ease;
      border: none;
      background-color: white;
    }
    ul.tabs li.active {
      color: #0972ce;
      background-color: white !important;
    }
    ul.tabs li::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background-color: transparent;
      transition: background-color 0.3s ease;
    }
    ul.tabs li.active::after {
      background-color: #0972ce;
    }
    .tab-content {
      display: none;
      margin-top: 20px;
      background-color: white;
      padding: 20px;
      margin-top: -10px !important;
    }
    .tab-content.active {
      display: block;
    }
    table, th, td {
      border: none !important;
      color: black;
    }
    table {
      width: 100%;
      background: white;
      border-collapse: collapse;
      margin-bottom: 30px; /* un mic spațiu între tabele, dacă sunt mai multe subcategorii */
    }
    a {
      color: #003366;
      font-weight: bold;
      text-decoration: underline;
    }
    a:hover {
      color: #003366;
      text-decoration: underline;
    }
    thead {
      border-bottom: 3px solid green;
      padding-top: 20px;
      padding-bottom: 20px;
      font-size: 20px;
      font-weight: bold;
    }
    th, td {
      padding: 20px !important;
      border: none !important;
      text-align: left !important;
    }
    tr + tr {
      border-top: 1px solid #ccc;
    }
    tbody {
      padding-bottom: 30px;
    }
    </style>

    <div class="tabs-container">
        <!-- Tabs principale (ani) -->
        <ul class="tabs" id="main-tabs">
        <?php foreach($data as $index => $cat): ?>
            <li data-tab="tab-<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                <?php echo esc_html($cat['name']); ?>
            </li>
        <?php endforeach; ?>
        </ul>

        <?php foreach($data as $index => $cat): ?>
        <div id="tab-<?php echo $index; ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>">

            <!-- Tabs secundare (subcategorii) -->
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
                                <tr><td><a href="<?php echo esc_url(add_query_arg('v', time(), $url)); ?>" target="_blank"><?php echo esc_html($title); ?></a>
</td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td><em>Nicio înregistrare în această subcategorie.</em></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><em>Nu există subcategorii pentru această categorie.</em></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
   document.addEventListener("DOMContentLoaded", function () {
    // Tabs principale (categoriile)
    const mainTabs = document.querySelectorAll('.tabs#main-tabs li');
    const mainTabContents = document.querySelectorAll('.tabs-container > .tab-content');

    mainTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');

            // Dezactivează toate taburile principale și conținuturile lor
            mainTabs.forEach(t => t.classList.remove('active'));
            mainTabContents.forEach(c => c.classList.remove('active'));

            // Activează tabul principal selectat și conținutul său
            this.classList.add('active');
            const activeContent = document.getElementById(target);
            activeContent.classList.add('active');

            // Activează primul subtab și conținutul aferent, dacă există
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

    // Tabs subcategorii (sub-tabs)
    document.querySelectorAll('.sub-tabs').forEach(subTabContainer => {
        const subtabs = subTabContainer.querySelectorAll('li');
        subtabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.stopPropagation(); // Previne propagarea clickului spre taburile principale

                const target = this.getAttribute('data-subtab');

                // Dezactivează toate subtabs din containerul curent și conținuturile lor
                subtabs.forEach(t => t.classList.remove('active'));

                const tabContentContainer = subTabContainer.closest('.tab-content');
                tabContentContainer.querySelectorAll('.subtab-content').forEach(c => c.classList.remove('active'));

                // Activează subtabul și conținutul aferent
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
