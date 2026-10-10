/**
 * Registers every block. All of them are rendered by PHP, so the editor shows
 * the server's output and only adds the sidebar controls listed below.
 */
import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	PanelColorSettings,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import './style.css';
import list from './core-contributions-block/block.json';
import card from './contributor-card/block.json';
import stats from './contribution-stats/block.json';
import activity from './contribution-activity/block.json';
import releases from './release-badges/block.json';
import badges from './team-badges/block.json';
import directory from './directory/block.json';

const TOGGLES = {
	showHeader: __( 'Show contributor header', 'nhrrob-core-contributions' ),
	showDate: __( 'Show dates', 'nhrrob-core-contributions' ),
	showPagination: __( 'Show pagination', 'nhrrob-core-contributions' ),
};

const SELECTS = {
	source: {
		label: __( 'Contributions to', 'nhrrob-core-contributions' ),
		options: [
			{
				value: 'core',
				label: __( 'WordPress core', 'nhrrob-core-contributions' ),
			},
			{
				value: 'gutenberg',
				label: __( 'Gutenberg', 'nhrrob-core-contributions' ),
			},
		],
	},
	type: {
		label: __( 'Show', 'nhrrob-core-contributions' ),
		options: [
			{
				value: 'plugins',
				label: __( 'Plugins', 'nhrrob-core-contributions' ),
			},
			{
				value: 'themes',
				label: __( 'Themes', 'nhrrob-core-contributions' ),
			},
		],
	},
};

/**
 * The username is committed when the field loses focus, so typing does not
 * trigger a remote lookup per keystroke.
 *
 * @param {Object}   props          Props.
 * @param {string}   props.value    Saved username.
 * @param {Function} props.onCommit Called with the new username.
 * @return {Element} Control.
 */
function UsernameControl( { value, onCommit } ) {
	const [ draft, setDraft ] = useState( value );

	return (
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ __(
				'WordPress.org username',
				'nhrrob-core-contributions'
			) }
			help={ __(
				'Leave empty to use the default username from Tools → Core Contributions.',
				'nhrrob-core-contributions'
			) }
			value={ draft }
			onChange={ setDraft }
			onBlur={ () => draft !== value && onCommit( draft.trim() ) }
		/>
	);
}

function Edit( { name, attributes, setAttributes } ) {
	const { username, accentColor, preset, className = '' } = attributes;

	// Blocks saved before 2.0 chose their look with a "preset" attribute.
	useEffect( () => {
		if ( ! preset ) {
			return;
		}
		const style =
			[ 'minimal', 'timeline' ].includes( preset ) &&
			! className.includes( 'is-style-' )
				? `${ className } is-style-${ preset }`.trim()
				: className;
		setAttributes( { preset: '', className: style || undefined } );
	}, [ preset, className, setAttributes ] );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Settings', 'nhrrob-core-contributions' ) }
				>
					<UsernameControl
						value={ username }
						onCommit={ ( value ) =>
							setAttributes( { username: value } )
						}
					/>
					{ Object.keys( SELECTS )
						.filter( ( key ) => key in attributes )
						.map( ( key ) => (
							<SelectControl
								key={ key }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								label={ SELECTS[ key ].label }
								options={ SELECTS[ key ].options }
								value={ attributes[ key ] }
								onChange={ ( value ) =>
									setAttributes( { [ key ]: value } )
								}
							/>
						) ) }
					{ Object.keys( TOGGLES )
						.filter( ( key ) => key in attributes )
						.map( ( key ) => (
							<ToggleControl
								key={ key }
								__nextHasNoMarginBottom
								label={ TOGGLES[ key ] }
								checked={ attributes[ key ] }
								onChange={ ( value ) =>
									setAttributes( { [ key ]: value } )
								}
							/>
						) ) }
				</PanelBody>
				<PanelColorSettings
					title={ __( 'Accent', 'nhrrob-core-contributions' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							label: __(
								'Accent color',
								'nhrrob-core-contributions'
							),
							value: accentColor,
							onChange: ( value ) =>
								setAttributes( { accentColor: value || '' } ),
						},
					] }
				/>
			</InspectorControls>
			{ /* Links in the preview (pagination included) must not navigate away from the editor. */ }
			<Disabled>
				<ServerSideRender
					block={ name }
					attributes={ attributes }
					skipBlockSupportAttributes
				/>
			</Disabled>
		</div>
	);
}

[ list, card, stats, activity, releases, badges, directory ].forEach(
	( metadata ) =>
		registerBlockType( metadata, { edit: Edit, save: () => null } )
);
