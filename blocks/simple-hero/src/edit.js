import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './style.scss';
import './editor.scss';

registerBlockType('global360blocks/test-hero', {
	edit: () => {
		const blockProps = useBlockProps({
			className: 'simple-hero-block',
		});

		return (
			<div {...blockProps}>
				<div className="simple-hero-content">
					<h1>Page Title (Dynamic)</h1>
					<p>This will display the actual page title on the frontend</p>
				</div>
			</div>
		);
	},
	save: () => null, // Dynamic block - rendered on server
});
