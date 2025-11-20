import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { imageUrl, heading } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Image Settings', 'global360blocks')}>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => setAttributes({ imageUrl: media.url })}
							allowedTypes={['image']}
							value={imageUrl}
							render={({ open }) => (
								<Button
									onClick={open}
									isSecondary
								>
									{imageUrl
										? __('Change Image', 'global360blocks')
										: __('Select Image', 'global360blocks')}
								</Button>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div
					className="cta-container"
					style={{ backgroundImage: `url(${imageUrl})` }}
				>
					<div className="cta-content">
						<h2 className="cta-heading">{heading || __('Add heading...', 'global360blocks')}</h2>
						<div className="cta-button">
							<Button
								isPrimary
								disabled
							>
								{__('Take Risk Assessment Now', 'global360blocks')}
							</Button>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
