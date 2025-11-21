import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';
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
						<RichText
							tagName="h2"
							className="cta-heading"
							value={heading}
							onChange={(value) => setAttributes({ heading: value })}
							placeholder={__('Add heading...', 'global360blocks')}
						/>
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
