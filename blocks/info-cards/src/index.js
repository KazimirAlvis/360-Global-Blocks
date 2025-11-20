import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.scss';

registerBlockType('global360blocks/info-cards', {
	edit: Edit,
	save: () => null,
});
