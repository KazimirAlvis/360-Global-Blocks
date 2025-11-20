import { registerBlockType } from '@wordpress/blocks';
import './editor.css';
import './style.css';
import Edit from './edit';
import metadata from '../block.json';

registerBlockType(metadata.name, {
	edit: Edit,
	save: () => null, // Dynamic block, rendered via PHP
});
