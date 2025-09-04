import { registerBlockType } from '@wordpress/blocks';
import { MediaUpload, MediaUploadCheck, RichText, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './style.css';
import './editor.css';

registerBlockType('global360blocks/full-hero', {
	title: __('Full Page Hero', 'global360blocks'),
	icon: 'cover',
	category: '360-blocks',
	supports: {
		html: false,
		align: ['full'],
		anchor: true,
		className: true,
		reusable: true,
		inserter: true,
		multiple: true,
		lock: false,
	},
	attributes: {
		bgImageUrl: { type: 'string', default: '' },
		bgImageId: { type: 'number', default: 0 },
		heading: { type: 'string', default: '' },
		subheading: { type: 'string', default: '' },
		assessmentId: { type: 'string', default: '' },
	},
	edit: ({ attributes, setAttributes }) => {
		const { bgImageUrl, bgImageId, heading, subheading, assessmentId } = attributes;
		const blockProps = useBlockProps({
			className: 'full-hero-block',
			style: { backgroundImage: bgImageUrl ? `url(${bgImageUrl})` : undefined },
		});

		return (
			<div {...blockProps}>
				<InspectorControls>
					<PanelBody title={__('Hero Image', 'global360blocks')}>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) =>
									setAttributes({
										bgImageUrl: media.url,
										bgImageId: media.id,
									})
								}
								allowedTypes={['image']}
								value={bgImageId}
								render={({ open }) => (
									<Button
										onClick={open}
										isSecondary
									>
										{bgImageUrl
											? __('Change Image', 'global360blocks')
											: __('Select Image', 'global360blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</PanelBody>
				</InspectorControls>

				{!bgImageUrl && (
					<div className="full-hero-placeholder">
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) =>
									setAttributes({
										bgImageUrl: media.url,
										bgImageId: media.id,
									})
								}
								allowedTypes={['image']}
								value={bgImageId}
								render={({ open }) => (
									<Button
										onClick={open}
										isPrimary
										className="full-hero-upload-btn"
									>
										{__('Upload Hero Image', 'global360blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
				)}

				<div className="full-hero-content">
					<RichText
						tagName="h1"
						className="full-hero-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Add heading...', 'global360blocks')}
					/>
					<RichText
						tagName="p"
						className="full-hero-subheading"
						value={subheading}
						onChange={(value) => setAttributes({ subheading: value })}
						placeholder={__('Add sub-heading...', 'global360blocks')}
					/>
					<Button
						className="full-hero-assess-btn btn btn_global"
						disabled
					>
						{__('Take Risk Assessment Now', 'global360blocks')}
					</Button>
				</div>
			</div>
		);
	},
	save: () => null, // Dynamic block, rendered by PHP
});
