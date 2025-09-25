(function(blocks, editor, components, i18n) {
    var el = wp.element.createElement;
    var __ = i18n.__;
    var MediaUpload = editor.MediaUpload;
    var BlockControls = editor.BlockControls;
    var Button = components.Button;
    var Toolbar = components.Toolbar;
    
    blocks.registerBlockType('simple-gallery/gallery', {
        title: __('Simple Gallery'),
        icon: 'format-gallery',
        category: 'common',
        attributes: {
            images: {
                type: 'array',
                default: [],
            },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            var images = attributes.images;
            
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
            
            // Editor view for the block
            return [
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
                        { className: 'simple-gallery-preview mosaic-gallery' },
                        images.map(function(img, index) {
                            return el(
                                'div',
                                {
                                    key: index,
                                    className: 'sm-gallery-item'
                                },
                                el(
                                    'img',
                                    {
                                        src: img.url,
                                        alt: img.alt || '',
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
