import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, Spinner } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import metadata from './block.json';

const debounce = (func, delay) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => func(...args), delay);
    };
};

registerBlockType(metadata.name, {
    attributes: {
        username: {
            type: 'string',
            default: '',
        },
    },
    supports: {
        html: false,
        reusable: true,
        align: true,
    },
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const [tempUsername, setTempUsername] = useState(attributes.username);
        const [previewContent, setPreviewContent] = useState('');
        const [isLoading, setIsLoading] = useState(false);
        const updateUsernameDebounced = useRef(
            debounce((username) => {
                setAttributes({ username });
            }, 500) // 500ms delay
        ).current;

        // Update the preview when `attributes.username` changes
        useEffect(() => {
            if (attributes.username) {
                setIsLoading(true);

                // Fetch rendered shortcode output
                wp.apiFetch({
                    path: `/nhr/v1/render-shortcode`,
                    method: 'POST',
                    data: {
                        shortcode: `[nhrcc_core_contributions username="${attributes.username}"]`,
                    },
                })
                    .then((response) => {
                        setPreviewContent(response.rendered || '');
                    })
                    .catch(() => {
                        setPreviewContent('Failed to load preview.');
                    })
                    .finally(() => {
                        setIsLoading(false);
                    });
            } else {
                setPreviewContent('');
            }
        }, [attributes.username]);

        return (
            <div {...blockProps}>
                {/* Inspector Controls */}
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="WordPress.org Username"
                            value={tempUsername}
                            onChange={(username) => {
                                setTempUsername(username);
                                updateUsernameDebounced(username);
                            }}
                        />
                    </PanelBody>
                </InspectorControls>

                {/* Block Preview */}
                {isLoading ? (
                    <Spinner />
                ) : attributes.username ? (
                    <div className="nhr-core-contributions-preview">
                        <p dangerouslySetInnerHTML={{ __html: previewContent }} />
                    </div>
                ) : (
                    <p>Please set a WordPress.org username to preview the contributions.</p>
                )}
            </div>
        );
    },
    save: () => {
        return null; // Dynamic block rendering handled in PHP.
    },
});
