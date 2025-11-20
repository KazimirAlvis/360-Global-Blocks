import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import './style.css';

registerBlockType('global360blocks/full-hero', {
	edit: Edit,
	save: () => null, // Dynamic block, rendered by PHP
});
