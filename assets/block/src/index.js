import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Spinner } from '@wordpress/components';
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
        preset: {
            type: 'string',
            default: 'default',
        }
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
            }, 500)
        ).current;

        // Update the preview when attributes change
        useEffect(() => {
            if (attributes.username) {
                setIsLoading(true);
                wp.apiFetch({
                    path: `/nhr/v1/render-shortcode`,
                    method: 'POST',
                    data: {
                        shortcode: `[nhrcc_core_contributions username="${attributes.username}" preset="${attributes.preset}"]`,
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
        }, [attributes.username, attributes.preset]);

        return (
            <div {...blockProps}>
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
                        <SelectControl
                            label="Design Style"
                            value={attributes.preset}
                            options={[
                                { label: 'Default', value: 'default' },
                                { label: 'Modern', value: 'modern' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Compact', value: 'compact' },
                            ]}
                            onChange={(preset) => setAttributes({ preset })}
                        />
                    </PanelBody>
                </InspectorControls>

                {isLoading ? (
                    <Spinner />
                ) : attributes.username ? (
                    <div className="nhr-core-contributions-preview">
                        <div dangerouslySetInnerHTML={{ __html: previewContent }} />
                    </div>
                ) : (
                    <p>Please set a WordPress.org username to preview the contributions.</p>
                )}
            </div>
        );
    },
    save: () => {
        return null;
    },
});