import { registerBlockType } from '@wordpress/blocks';
import { 
	useBlockProps, 
	InspectorControls, 
	RichText,
	BlockControls,
	AlignmentToolbar
} from '@wordpress/block-editor';
import { 
	PanelBody, 
	SelectControl, 
	ToggleControl
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './editor.scss';
import './style.scss';

const RichTextEdit = (props) => {
	const { attributes, setAttributes } = props;
	const { content, textAlign, maxWidth, showDropCap } = attributes;

	// Debug logging
	console.log('Rich Text Edit Props:', props);
	console.log('Current content:', content);
	console.log('All attributes:', attributes);

	const blockProps = useBlockProps({
		className: `rich-text-block text-align-${textAlign} ${showDropCap ? 'has-drop-cap' : ''}`,
		style: {
			maxWidth: maxWidth !== 'none' ? maxWidth : undefined,
			margin: maxWidth !== 'none' ? '0 auto' : undefined,
		}
	});

	const maxWidthOptions = [
		{ label: 'None (Full Width)', value: 'none' },
		{ label: 'Small (600px)', value: '600px' },
		{ label: 'Medium (800px)', value: '800px' },
		{ label: 'Large (1000px)', value: '1000px' },
		{ label: 'Extra Large (1200px)', value: '1200px' }
	];

	const handleContentChange = (newContent) => {
		console.log('Content changing from:', content);
		console.log('Content changing to:', newContent);
		setAttributes({ content: newContent });
	};

	return (
		<Fragment>
			<BlockControls>
				<AlignmentToolbar
					value={textAlign}
					onChange={(newAlign) => setAttributes({ textAlign: newAlign || 'left' })}
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody title="Text Settings" initialOpen={true}>
					<SelectControl
						label="Content Width"
						value={maxWidth}
						options={maxWidthOptions}
						onChange={(value) => setAttributes({ maxWidth: value })}
						help="Control the maximum width of the text content"
					/>
					
					<ToggleControl
						label="Drop Cap"
						checked={showDropCap}
						onChange={(value) => setAttributes({ showDropCap: value })}
						help="Show a large first letter at the beginning of the text"
					/>
				</PanelBody>

				<PanelBody title="Formatting Help" initialOpen={false}>
					<div style={{ fontSize: '13px', lineHeight: '1.4' }}>
						<p><strong>Keyboard Shortcuts:</strong></p>
						<ul style={{ marginLeft: '15px' }}>
							<li><strong># + space</strong> = H1 heading</li>
							<li><strong>## + space</strong> = H2 heading</li>
							<li><strong>### + space</strong> = H3 heading</li>
							<li><strong>#### + space</strong> = H4 heading</li>
							<li><strong>* + space</strong> = Bullet list</li>
							<li><strong>- + space</strong> = Bullet list</li>
							<li><strong>1. + space</strong> = Numbered list</li>
						</ul>
						<p><strong>Text Formatting:</strong></p>
						<ul style={{ marginLeft: '15px' }}>
							<li>Select text for <strong>bold</strong>, <em>italic</em>, links</li>
							<li><strong>**text**</strong> = bold</li>
							<li><strong>*text*</strong> = italic</li>
						</ul>
						<p><strong>Tips:</strong></p>
						<ul style={{ marginLeft: '15px' }}>
							<li>Press Enter for new paragraph</li>
							<li>Press Shift+Enter for line break</li>
							<li>Use the formatting toolbar when text is selected</li>
						</ul>
					</div>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<RichText
					tagName="div"
					className="rich-text-content"
					value={content}
					onChange={handleContentChange}
					placeholder="Start typing... Use # for headings, * for lists, or select text for formatting options."
					multiline="p"
					__unstableEmbedURLOnPaste={true}
					__unstableAllowPrefixTransformations={true}
					allowedFormats={[
						'core/bold',
						'core/italic',
						'core/underline',
						'core/strikethrough',
						'core/link',
						'core/text-color',
						'core/subscript',
						'core/superscript',
						'core/code'
					]}
				/>
				
				{/* Debug display */}
				<div style={{ 
					marginTop: '10px', 
					padding: '10px', 
					background: '#f0f0f0', 
					fontSize: '12px',
					border: '1px solid #ddd'
				}}>
					<strong>Debug:</strong> Content length: {content ? content.length : 0}<br/>
					<strong>Content:</strong> {content || 'No content'}
				</div>
			</div>
		</Fragment>
	);
};

registerBlockType('global360blocks/rich-text', {
	edit: RichTextEdit,
	save: () => null,
});
