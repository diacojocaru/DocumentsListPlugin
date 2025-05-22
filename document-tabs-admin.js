jQuery(document).ready(function($) {

    function createSubcategoryHtml(subIndex = 0) {
        return `
        <div class="subcategory-item" data-index="${subIndex}" style="margin-left:20px; border:1px dashed #ddd; padding:10px; margin-top:10px; cursor:move;">
            <input type="text" class="subcategory-name" placeholder="Nume subcategorie" value="" />
            <button class="select-documents button">Selectează Documente</button>
            <button class="delete-subcategory button" style="margin-left:10px;">Șterge Subcategorie</button>
            <input type="hidden" class="document-ids" value="" />
            <div class="document-preview"></div>
        </div>`;
    }

    function initSortable() {
        $('#categories-container').sortable({
            handle: '.category-name',
            placeholder: 'sortable-placeholder',
            items: '.category-item'
        });

        $('.subcategories-container').each(function() {
            $(this).sortable({
                handle: '.subcategory-name',
                placeholder: 'sortable-placeholder',
                items: '.subcategory-item'
            });
        });
    }

    $('#add-category').on('click', function(e) {
        e.preventDefault();
        const index = $('.category-item').length;
        const html = `
        <div class="category-item" data-index="${index}" style="border:1px solid #ccc; padding:10px; margin-bottom:10px; cursor:move;">
            <input type="text" class="category-name" placeholder="Nume categorie (an)" value="" />
            <div class="subcategories-container">
                ${createSubcategoryHtml()}
            </div>
            <button class="add-subcategory button" style="margin-top:10px;">Adaugă Subcategorie</button>
            <button class="delete-category button" style="margin-left:10px;">Șterge Categorie</button>
            <hr>
        </div>`;
        $('#categories-container').append(html);
        initSortable();
    });

    $('#categories-container')
        .on('click', '.delete-category', function(e) {
            e.preventDefault();
            $(this).closest('.category-item').remove();
        })
        .on('click', '.add-subcategory', function(e) {
            e.preventDefault();
            const $cat = $(this).closest('.category-item');
            const subIndex = $cat.find('.subcategory-item').length;
            $cat.find('.subcategories-container').append(createSubcategoryHtml(subIndex));
            initSortable();
        })
        .on('click', '.delete-subcategory', function(e) {
            e.preventDefault();
            $(this).closest('.subcategory-item').remove();
        })
        .on('click', '.select-documents', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $docIds = $btn.siblings('.document-ids');
            const $preview = $btn.siblings('.document-preview');
            let existingIds = $docIds.val() ? $docIds.val().split(',') : [];

            const frame = wp.media({
                title: 'Selectează documente',
                button: { text: 'Selectează' },
                multiple: true,
                library: {
                    type: ['image', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'audio/mpeg', 'video/mp4']
                }
            });

            frame.on('select', function() {
                const attachments = frame.state().get('selection').toJSON();

                attachments.forEach(att => {
                    const idStr = att.id.toString();
                    if (!existingIds.includes(idStr)) {
                        existingIds.push(idStr);
                    }
                });

                $docIds.val(existingIds.join(','));
                $preview.empty();
                existingIds.forEach(id => {
                    const attachment = wp.media.attachment(id);
                    attachment.fetch().then(() => {
                        $preview.append(`<div>${attachment.get('title')}</div>`);
                    });
                });
            });

            frame.open();
        });

    $('#document-tabs-form').on('submit', function() {
        const data = [];
        $('.category-item').each(function() {
            const $cat = $(this);
            const catName = $cat.find('.category-name').val().trim();
            if (!catName) return;

            const catObj = { name: catName, subcategories: [] };

            $cat.find('.subcategory-item').each(function() {
                const $sub = $(this);
                const subName = $sub.find('.subcategory-name').val().trim();
                const docIds = $sub.find('.document-ids').val().trim();
                if (subName && docIds) {
                    catObj.subcategories.push({
                        name: subName,
                        documents: docIds.split(',')
                    });
                }
            });

            if (catObj.subcategories.length > 0) {
                data.push(catObj);
            }
        });

        $('#document_tabs_data').val(JSON.stringify(data));
    });

    initSortable();
});
