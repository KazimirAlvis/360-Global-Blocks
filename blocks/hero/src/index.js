import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import './style.css';
import './editor.css';

registerBlockType('global360blocks/hero', {
	title: __('360 Hero', 'global-360-blocks'),
	icon: 'cover-image',
	category: 'text',
	edit: () => (
		<div className="sm_hero">
			<h1>Find A Doctor</h1>
		</div>
	),
	save: () => null,
});
