import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.scss';

registerBlockType('global360blocks/popular-practices', {
	edit: Edit,
	save: () => null,
});
