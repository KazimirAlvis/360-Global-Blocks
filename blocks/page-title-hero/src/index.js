import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Import styles
import './editor.scss';
import './style.scss';

registerBlockType('global360blocks/page-title-hero', {
	edit: ({ attributes, setAttributes }) => {
		const { title, subtitle, background_image, background_overlay, text_alignment } = attributes;
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Hero Settings', 'global360blocks')}>
						<TextControl
							label={__('Custom Title', 'global360blocks')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
							placeholder={__('Leave empty to use page title...', 'global360blocks')}
							help={__('If left empty, the block will automatically use the current page title.', 'global360blocks')}
						/>
						<TextControl
							label={__('Subtitle', 'global360blocks')}
							value={subtitle}
							onChange={(value) => setAttributes({ subtitle: value })}
							placeholder={__('Enter subtitle (optional)...', 'global360blocks')}
						/>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) => setAttributes({ background_image: media.url })}
								allowedTypes={['image']}
								value={background_image}
								render={({ open }) => (
									<Button 
										onClick={open}
										variant={background_image ? 'secondary' : 'primary'}
									>
										{background_image ? __('Change Background Image', 'global360blocks') : __('Select Background Image', 'global360blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
						{background_image && (
							<Button 
								onClick={() => setAttributes({ background_image: '' })}
								variant="link"
								isDestructive
							>
								{__('Remove Background Image', 'global360blocks')}
							</Button>
						)}
						<ToggleControl
							label={__('Background Overlay', 'global360blocks')}
							checked={background_overlay}
							onChange={(value) => setAttributes({ background_overlay: value })}
							help={__('Add dark overlay for better text readability', 'global360blocks')}
						/>
						<SelectControl
							label={__('Text Alignment', 'global360blocks')}
							value={text_alignment}
							options={[
								{ label: __('Left', 'global360blocks'), value: 'left' },
								{ label: __('Center', 'global360blocks'), value: 'center' },
								{ label: __('Right', 'global360blocks'), value: 'right' }
							]}
							onChange={(value) => setAttributes({ text_alignment: value })}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="page-title-hero-editor">
						<div 
							className={`sm_hero text-${text_alignment}`}
							style={{
								backgroundImage: background_image ? `url(${background_image})` : 'none',
								backgroundSize: 'cover',
								backgroundPosition: 'center',
								position: 'relative'
							}}
						>
							{background_overlay && background_image && (
								<div className="hero-overlay"></div>
							)}
							<div className="hero-content">
								{title ? (
									<h1 className="hero-title">{title}</h1>
								) : (
									<h1 className="hero-title">[Page Title]</h1>
								)}
								{subtitle && <p className="hero-subtitle">{subtitle}</p>}
							</div>
						</div>
						
						{!title && (
							<div className="placeholder-notice">
								<p>{__('Enter a title in the sidebar settings to preview your page title hero.', 'global360blocks')}</p>
							</div>
						)}
					</div>
				</div>
			</>
		);
	},
	save: () => null,
});
