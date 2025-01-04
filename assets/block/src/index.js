import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

const { TextControl } = wp.components;
// const Edit = () => <p { ...useBlockProps() }>Hello World - Block Editor</p>;
// const save = () => <p { ...useBlockProps.save() }>Hello World - Frontend</p>;

registerBlockType( metadata.name, {
	attributes: {
        username: {
            type: 'string',
            default: '',
        },
    },
    edit: ({ attributes, setAttributes }) => {
        return (
            <div className="nhr-core-contributions">
                <TextControl
                    label="WordPress.org Username"
                    value={attributes.username}
                    onChange={(username) => setAttributes({ username })}
                />
            </div>
        );
    },
    save: () => {
        return null; // Dynamic block rendering handled in PHP.
    },
} );
