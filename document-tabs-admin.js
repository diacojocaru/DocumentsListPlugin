jQuery(document).ready(function($) {

    function createExternalDocHtml(title = '', url = '') {
        return `
        <div class="external-doc" style="margin-bottom:10px;">
            <input type="text" class="ext-doc-title" placeholder="Nume document" value="${title}" />
            <input type="text" class="ext-doc-url" placeholder="Link document" value="${url}" style="width:60%;" />
            <button class="delete-external-doc button" style="margin-left:10px;">Șterge</button>
        </div>`;
    }

    function createSubcategoryHtml(subIndex = 0) {
        return `
        <div class="subcategory-item" data-index="${subIndex}" style="margin-left:20px; border:1px dashed #ddd; padding:10px; margin-top:10px; cursor:move;">
            <input type="text" class="subcategory-name" placeholder="Nume subcategorie" value="" />
            <button class="select-documents button">Selectează Documente</button>
            <button class="delete-subcategory button" style="margin-left:10px;">Șterge Subcategorie</button>
            <button class="move-sub-up button" style="margin-left:10px;">↑</button>
            <button class="move-sub-down button">↓</button>
            <input type="hidden" class="document-ids" value="" />
            <div class="document-preview"></div>
            <div class="external-docs-container" style="margin-top:10px;"></div>
            <button type="button" class="add-external-doc button">Adaugă document extern</button>

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
            <button class="move-cat-up button" style="margin-left:10px;">↑</button>
            <button class="move-cat-down button">↓</button>
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
        type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image', 'audio', 'video']
    }
});

// ✅ Preselectează documentele deja alese
frame.on('open', function() {
    const selection = frame.state().get('selection');
    existingIds.forEach(id => {
        const attachment = wp.media.attachment(id);
        attachment.fetch();
        selection.add(attachment);
    });
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
        const title = attachment.get('title');
        const docDiv = $(`
            <div class="doc-preview-item" data-id="${id}" style="margin-bottom:5px;">
                ${title}
                <button class="remove-doc button-link" style="margin-left:10px;">✕</button>
            </div>
        `);
        $preview.append(docDiv);

        // 👇 Aici legăm butonul de ștergere
        docDiv.on('click', '.remove-doc', function(e) {
            e.preventDefault();
            const idToRemove = $(this).parent().data('id').toString();
            existingIds = existingIds.filter(i => i !== idToRemove);
            $docIds.val(existingIds.join(','));
            $(this).parent().remove();
        });
    });
});


            });

            frame.open();
        })
        .on('click', '.move-cat-up', function(e) {
            e.preventDefault();
            const $cat = $(this).closest('.category-item');
            const $prev = $cat.prev('.category-item');
            if ($prev.length) $prev.before($cat);
        })
        .on('click', '.move-cat-down', function(e) {
            e.preventDefault();
            const $cat = $(this).closest('.category-item');
            const $next = $cat.next('.category-item');
            if ($next.length) $next.after($cat);
        })
        .on('click', '.move-sub-up', function(e) {
            e.preventDefault();
            const $sub = $(this).closest('.subcategory-item');
            const $prev = $sub.prev('.subcategory-item');
            if ($prev.length) $prev.before($sub);
        })
        .on('click', '.move-sub-down', function(e) {
            e.preventDefault();
            const $sub = $(this).closest('.subcategory-item');
            const $next = $sub.next('.subcategory-item');
            if ($next.length) $next.after($sub);
        })
        .on('click', '.add-external-doc', function(e) {
            e.preventDefault();
            const $container = $(this).siblings('.external-docs-container');
            $container.append(createExternalDocHtml());
        })
        .on('click', '.delete-external-doc', function(e) {
            e.preventDefault();
            $(this).closest('.external-doc').remove();
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
                const externalDocs = [];

                $sub.find('.external-doc').each(function() {
                    const title = $(this).find('.ext-doc-title').val().trim();
                    const url = $(this).find('.ext-doc-url').val().trim();
                    if (title && url) {
                        externalDocs.push({ title, url });
                    }
                });

                if (subName && (docIds || externalDocs.length)) {
                    catObj.subcategories.push({
                        name: subName,
                        documents: docIds ? docIds.split(',') : [],
                        externalDocuments: externalDocs
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
