/**
 * The settings screen under Tools → Core Contributions.
 */
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Card,
	CardBody,
	Disabled,
	Notice,
	SelectControl,
	Spinner,
	TabPanel,
	TextControl,
} from '@wordpress/components';
import { dateI18n, getSettings } from '@wordpress/date';
import domReady from '@wordpress/dom-ready';
import { createRoot, useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import './style.css';

const PATH = '/nhrcc-core-contributions/v1';
const HOUR = 3600;
const DURATIONS = [
	{ value: HOUR, label: __( '1 hour', 'nhrrob-core-contributions' ) },
	{ value: 6 * HOUR, label: __( '6 hours', 'nhrrob-core-contributions' ) },
	{ value: 12 * HOUR, label: __( '12 hours', 'nhrrob-core-contributions' ) },
	{ value: 24 * HOUR, label: __( '1 day', 'nhrrob-core-contributions' ) },
	{ value: 168 * HOUR, label: __( '1 week', 'nhrrob-core-contributions' ) },
];
const TABS = [
	{ name: 'general', title: __( 'General', 'nhrrob-core-contributions' ) },
	{ name: 'data', title: __( 'Data', 'nhrrob-core-contributions' ) },
	{ name: 'preview', title: __( 'Preview', 'nhrrob-core-contributions' ) },
];
const PREVIEWS = [
	'nhrcc-core-contributions/contributor-card',
	'nhrcc-core-contributions/contribution-activity',
	'nhrcc-core-contributions/core-contributions-block',
];

function Settings() {
	const [ saved, setSaved ] = useState( null );
	const [ form, setForm ] = useState( null );
	const [ token, setToken ] = useState( '' );
	const [ busy, setBusy ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const receive = ( settings ) => {
		setSaved( settings );
		setForm( settings );
		setToken( '' );
	};

	const request = ( options, message ) => {
		setBusy( true );
		setNotice( null );
		return apiFetch( options )
			.then( ( settings ) => {
				receive( settings );
				setNotice( { status: 'success', text: message } );
			} )
			.catch( ( error ) =>
				setNotice( {
					status: 'error',
					text:
						error?.message ||
						__(
							'Something went wrong. Please try again.',
							'nhrrob-core-contributions'
						),
				} )
			)
			.finally( () => setBusy( false ) );
	};

	useEffect( () => {
		apiFetch( { path: `${ PATH }/settings` } )
			.then( receive )
			.catch( () =>
				setNotice( {
					status: 'error',
					text: __(
						'The settings could not be loaded.',
						'nhrrob-core-contributions'
					),
				} )
			);
	}, [] );

	if ( ! form ) {
		return notice ? (
			<Notice status="error" isDismissible={ false }>
				{ notice.text }
			</Notice>
		) : (
			<Spinner />
		);
	}

	const save = ( extra = {} ) =>
		request(
			{
				path: `${ PATH }/settings`,
				method: 'POST',
				data: { ...form, ...extra },
			},
			__( 'Settings saved successfully.', 'nhrrob-core-contributions' )
		);

	const saveButton = (
		<Button
			variant="primary"
			isBusy={ busy }
			disabled={ busy }
			onClick={ () => save( token ? { githubToken: token } : {} ) }
		>
			{ __( 'Save settings', 'nhrrob-core-contributions' ) }
		</Button>
	);

	return (
		<div className="nhrcc-settings">
			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.text }
				</Notice>
			) }
			<TabPanel tabs={ TABS }>
				{ ( tab ) => (
					<Card>
						<CardBody>
							{ tab.name === 'general' && (
								<>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __(
											'Default WordPress.org username',
											'nhrrob-core-contributions'
										) }
										help={ __(
											'Used by every block that has no username of its own. It is the last part of your profile address, e.g. profiles.wordpress.org/nhrrob.',
											'nhrrob-core-contributions'
										) }
										value={ form.username }
										onChange={ ( username ) =>
											setForm( { ...form, username } )
										}
									/>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										type="number"
										label={ __(
											'Contributions per page',
											'nhrrob-core-contributions'
										) }
										help={ __(
											'How many contributions the list block shows per page (1 to 100).',
											'nhrrob-core-contributions'
										) }
										min={ 1 }
										max={ 100 }
										value={ form.postsPerPage }
										onChange={ ( value ) =>
											setForm( {
												...form,
												postsPerPage:
													parseInt( value, 10 ) || 10,
											} )
										}
									/>
									{ saveButton }
								</>
							) }
							{ tab.name === 'data' && (
								<>
									<SelectControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										label={ __(
											'Keep fetched data for',
											'nhrrob-core-contributions'
										) }
										help={ __(
											'How long contribution lists and plugin and theme lists are reused before they are fetched again.',
											'nhrrob-core-contributions'
										) }
										options={ DURATIONS }
										value={ form.cacheDuration }
										onChange={ ( value ) =>
											setForm( {
												...form,
												cacheDuration: Number( value ),
											} )
										}
									/>
									<TextControl
										__nextHasNoMarginBottom
										__next40pxDefaultSize
										type="password"
										autoComplete="off"
										label={ __(
											'GitHub token (optional)',
											'nhrrob-core-contributions'
										) }
										help={
											saved.hasToken
												? __(
														'A token is saved. Enter a new one to replace it.',
														'nhrrob-core-contributions'
												  )
												: __(
														'Contributions are read from GitHub, which allows only a few requests a minute without a token. A token with no scopes at all is enough.',
														'nhrrob-core-contributions'
												  )
										}
										value={ token }
										onChange={ setToken }
									/>
									<div className="nhrcc-settings__actions">
										{ saveButton }
										{ saved.hasToken && (
											<Button
												variant="secondary"
												isDestructive
												disabled={ busy }
												onClick={ () =>
													save( { githubToken: '' } )
												}
											>
												{ __(
													'Remove token',
													'nhrrob-core-contributions'
												) }
											</Button>
										) }
										<Button
											variant="secondary"
											disabled={ busy }
											onClick={ () =>
												request(
													{
														path: `${ PATH }/refresh`,
														method: 'POST',
													},
													__(
														'Stored data cleared successfully. It is fetched again on the next view.',
														'nhrrob-core-contributions'
													)
												)
											}
										>
											{ __(
												'Refresh all data',
												'nhrrob-core-contributions'
											) }
										</Button>
									</div>
									<p className="description">
										{ saved.synced
											? sprintf(
													/* translators: %s: date and time. */
													__(
														'Contribution history for the default username was last gathered on %s.',
														'nhrrob-core-contributions'
													),
													dateI18n(
														`${
															getSettings()
																.formats.date
														} ${
															getSettings()
																.formats.time
														}`,
														saved.synced * 1000
													)
											  )
											: __(
													'Contribution history (per-year totals, top components, credited releases) is gathered in the background the first time a block needs it.',
													'nhrrob-core-contributions'
											  ) }
									</p>
								</>
							) }
							{ tab.name === 'preview' &&
								( saved.username ? (
									<Disabled className="nhrcc-settings__preview">
										{ PREVIEWS.map( ( block ) => (
											<ServerSideRender
												key={ block }
												block={ block }
												attributes={ {
													username: saved.username,
												} }
											/>
										) ) }
									</Disabled>
								) : (
									<p>
										{ __(
											'Save a default username on the General tab to see a preview.',
											'nhrrob-core-contributions'
										) }
									</p>
								) ) }
						</CardBody>
					</Card>
				) }
			</TabPanel>
		</div>
	);
}

domReady( () => {
	const root = document.getElementById( 'nhrcc-admin-settings' );
	if ( root ) {
		createRoot( root ).render( <Settings /> );
	}
} );
