import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { imageUrl, heading, body, buttonText, buttonURL } = attributes;

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
				<PanelBody title={__('Button Settings', 'global360blocks')}>
					<TextControl
						label={__('Button Text', 'global360blocks')}
						value={buttonText}
						onChange={(val) => setAttributes({ buttonText: val })}
					/>
					<TextControl
						label={__('Button URL', 'global360blocks')}
						value={buttonURL}
						onChange={(val) => setAttributes({ buttonURL: val })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{imageUrl && (
					<img
						src={imageUrl}
						alt=""
					/>
				)}
				<RichText
					tagName="h2"
					value={heading}
					onChange={(val) => setAttributes({ heading: val })}
					placeholder={__('Heading...', 'global360blocks')}
				/>
				<RichText
					tagName="p"
					value={body}
					onChange={(val) => setAttributes({ body: val })}
					placeholder={__('Body text...', 'global360blocks')}
				/>
				<Button
					isPrimary
					href={buttonURL}
				>
					{buttonText || __('Learn More', 'global360blocks')}
				</Button>
			</div>
		</>
	);
}
