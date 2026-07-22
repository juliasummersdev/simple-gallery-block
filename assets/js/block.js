(function(blocks, editor, components, i18n) {
    var el = wp.element.createElement;
    var __ = i18n.__;
    var MediaUpload = editor.MediaUpload;
    var BlockControls = editor.BlockControls;
    var InspectorControls = editor.InspectorControls;
    var Button = components.Button;
    var Toolbar = components.Toolbar;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl;

    // Get global columns setting
    var globalColumns = (window.simpleGallerySettings && window.simpleGallerySettings.globalColumns) || 3;

    blocks.registerBlockType('simple-gallery/gallery', {
        title: __('Simple Gallery'),
        icon: 'format-gallery',
        category: 'common',
        attributes: {
            images: {
                type: 'array',
                default: [],
            },
            columns: {
                type: 'number',
                default: globalColumns,
            },
        },

        edit: function(props) {
            var attributes = props.attributes;
            var images = attributes.images;

            // Get columns value
            var columns = attributes.columns || globalColumns;

            function onSelectImages(newImages) {
                props.setAttributes({
                    images: newImages.map(function(image) {
                        return {
                            id: image.id,
                            url: image.url,
                            alt: image.alt,
                        };
                    }),
                });
            }

            function onChangeColumns(newColumns) {
                props.setAttributes({ columns: newColumns });
            }

            // Calculate actual column width for preview
            var columnWidth = (100 / columns) + '%';

            // Editor view for the block
            return [
                // Inspector controls (sidebar)
                el(
                    InspectorControls,
                    { key: 'inspector' },
                    el(
                        PanelBody,
                        {
                            title: __('Gallery Settings'),
                            initialOpen: true,
                        },
                        el(RangeControl, {
                            label: __('Columns'),
                            value: columns,
                            onChange: onChangeColumns,
                            min: 1,
                            max: 12,
                            help: columns === globalColumns
                                ? __('Currently using global default (' + globalColumns + ' columns)')
                                : __('Custom setting for this gallery: ' + columns + ' columns'),
                        })
                    )
                ),

                // Block controls
                el(
                    BlockControls,
                    { key: 'controls' },
                    el(
                        Toolbar,
                        null,
                        el(
                            MediaUpload,
                            {
                                onSelect: onSelectImages,
                                allowedTypes: ['image'],
                                multiple: true,
                                gallery: true,
                                value: images.map(function(img) { return img.id; }),
                                render: function(obj) {
                                    return el(
                                        Button,
                                        {
                                            className: 'components-toolbar__control',
                                            onClick: obj.open,
                                        },
                                        __('Edit Gallery')
                                    );
                                },
                            }
                        )
                    )
                ),
                
                // Block content
                el(
                    'div',
                    { className: props.className },
                    
                    // If there are no images, show the upload button
                    images.length === 0 && el(
                        MediaUpload,
                        {
                            onSelect: onSelectImages,
                            allowedTypes: ['image'],
                            multiple: true,
                            gallery: true,
                            render: function(obj) {
                                return el(
                                    Button,
                                    {
                                        className: 'button button-large',
                                        onClick: obj.open,
                                    },
                                    __('Add Images')
                                );
                            },
                        }
                    ),
                    
                    // If there are images, display the gallery preview
                    images.length > 0 && el(
                        'div',
                        {
                            className: 'simple-gallery-preview mosaic-gallery',
                            ref: function(node) {
                                if (node && typeof Masonry !== 'undefined') {
                                    // Wait for images to load before initializing masonry
                                    setTimeout(function() {
                                        if (node._masonry) {
                                            node._masonry.destroy();
                                        }
                                        node._masonry = new Masonry(node, {
                                            itemSelector: '.sm-gallery-item',
                                            columnWidth: '.sm-gallery-sizer',
                                            percentPosition: true,
                                            gutter: 0
                                        });
                                    }, 100);
                                }
                            }
                        },
                        // Add sizer element for masonry
                        el('div', { className: 'sm-gallery-sizer', style: { width: columnWidth } }),
                        // Map images
                        images.map(function(img, index) {
                            return el(
                                'div',
                                {
                                    key: index,
                                    className: 'sm-gallery-item',
                                    style: { width: columnWidth }
                                },
                                el(
                                    'img',
                                    {
                                        src: img.url,
                                        alt: img.alt || '',
                                        onLoad: function(e) {
                                            // Relayout masonry when images load
                                            var gallery = e.target.closest('.mosaic-gallery');
                                            if (gallery && gallery._masonry) {
                                                gallery._masonry.layout();
                                            }
                                        }
                                    }
                                )
                            );
                        })
                    )
                ),
            ];
        },
        
        save: function() {
            // Dynamic block, render is handled by PHP
            return null;
        },
    });
})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
