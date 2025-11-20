import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.scss';

registerBlockType('global360blocks/symptoms-ai', {
	edit: Edit,
	save: () => null,
});
