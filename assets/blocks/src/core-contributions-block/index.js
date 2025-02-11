import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Spinner } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import metadata from './block.json';

function debounce(func, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

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
    edit: EditComponent,
    save: () => null, // Use dynamic rendering on PHP side
});

function EditComponent({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const [previewContent, setPreviewContent] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    
    // Create debounced function with useRef to maintain reference
    const updateUsername = useRef(
        debounce((newUsername) => {
            setAttributes({ username: newUsername });
        }, 500)
    ).current;

    // Separate effect for setting initial values
    useEffect(() => {
        if (!attributes.username) {
            setAttributes({ 
                username: nhrccCoreContributionsCommonObj?.nhrccSettings?.username || '',
                preset: nhrccCoreContributionsCommonObj?.nhrccSettings?.preset || 'default'
            });
        }
    }, []); // Empty dependency array - runs once

    useEffect(() => {
        if (!attributes.username) {
            // setAttributes({ username: nhrccCoreContributionsCommonObj?.nhrccSettings?.username });
            setPreviewContent('');
            return;
        }

        setIsLoading(true);
        
        fetchPreview(attributes)
            .then(setPreviewContent)
            .catch((error) => {
                console.error('Failed to load preview:', error);
                setPreviewContent(`Error: ${error.message}`);
            })
            .finally(() => setIsLoading(false));
    }, [attributes.username, attributes.preset]);

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title="Core Contributions Settings">
                    <TextControl
                        label="WordPress.org Username"
                        value={attributes.username}
                        onChange={updateUsername}
                        help="Enter your WordPress.org username to display contributions"
                    />
                    <SelectControl
                        label="Design Style"
                        value={attributes.preset}
                        options={PRESET_OPTIONS}
                        onChange={(preset) => setAttributes({ preset })}
                    />
                </PanelBody>
            </InspectorControls>
            
            <PreviewContent 
                isLoading={isLoading}
                username={attributes.username}
                content={previewContent}
            />
        </div>
    );
}

// Constants
const PRESET_OPTIONS = [
    { label: 'Default', value: 'default' },
    { label: 'Minimal', value: 'minimal' },
];

// API and Helper Functions
async function fetchPreview({ username, preset }) {
    try {
        const response = await apiFetch({
            path: '/nhrcc-core-contributions/v1/core-contributions/render',
            method: 'POST',
            data: { username, preset },
        });
        return response.content || '';
    } catch (error) {
        console.error('Failed to fetch preview:', error);
        throw new Error('Failed to load preview');
    }
}

// Presentational Components
function PreviewContent({ isLoading, username, content }) {
    if (isLoading) {
        return <Spinner />;
    }
    
    if (!username) {
        return (
            <div className="components-placeholder">
                <p>Please set a WordPress.org username to preview the contributions.</p>
            </div>
        );
    }
    
    return (
        <div 
            className="nhr-core-contributions-preview"
            dangerouslySetInnerHTML={{ __html: content }}
        />
    );
}