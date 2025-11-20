import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('global360blocks/latest-articles', {
	edit: Edit,
	save: () => null,
});
