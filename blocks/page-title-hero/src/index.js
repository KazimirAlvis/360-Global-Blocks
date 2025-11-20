import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.scss';

registerBlockType('global360blocks/page-title-hero', {
	edit: Edit,
	save: () => null,
});
