import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { RichText, MediaUpload, MediaUploadCheck, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';

import './editor.css';
import './style.css';

registerBlockType('global360blocks/find-doctor', {
	title: __('Find Doctor', 'global360blocks'),
	icon: 'admin-users',
	category: '360-blocks',
	supports: {
		html: false,
		align: ['wide', 'full'],
		anchor: true,
		className: true,
		reusable: true,
		inserter: true,
		multiple: true,
		lock: false,
	},
	attributes: {
		imageUrl: { type: 'string', default: '' },
		imageId: { type: 'number', default: 0 },
		heading: { type: 'string', default: '' },
		bodyText: { type: 'string', default: '' },
	},
	edit: ({ attributes, setAttributes }) => {
		const { imageUrl, imageId, heading, bodyText } = attributes;
		const blockProps = useBlockProps({
			className: 'find-doctor-block',
		});

		const onSelectImage = (media) => {
			setAttributes({
				imageUrl: media.url,
				imageId: media.id,
			});
		};

		const onRemoveImage = () => {
			setAttributes({
				imageUrl: '',
				imageId: 0,
			});
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Find Doctor Image', 'global360blocks')}>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={onSelectImage}
								allowedTypes={['image']}
								value={imageId}
								render={({ open }) => (
									<Button
										className={
											imageId
												? 'editor-post-featured-image__preview'
												: 'editor-post-featured-image__toggle'
										}
										onClick={open}
									>
										{imageId
											? __('Change Image', 'global360blocks')
											: __('Select Image', 'global360blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
						{imageId && (
							<Button
								onClick={onRemoveImage}
								isDestructive
							>
								{__('Remove Image', 'global360blocks')}
							</Button>
						)}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="find-doctor-container">
						<div className="find-doctor-image">
							{imageUrl ? (
								<div className="image-wrapper">
									<img
										src={imageUrl}
										alt={heading || __('Find Doctor Image', 'global360blocks')}
									/>
								</div>
							) : (
								<MediaUploadCheck>
									<MediaUpload
										onSelect={onSelectImage}
										allowedTypes={['image']}
										value={imageId}
										render={({ open }) => (
											<div
												className="find-doctor-placeholder"
												onClick={open}
											>
												<Button className="find-doctor-upload-button">
													{__('Upload Find Doctor Image', 'global360blocks')}
												</Button>
											</div>
										)}
									/>
								</MediaUploadCheck>
							)}
						</div>
						<div className="find-doctor-content">
							<RichText
								tagName="h2"
								className="find-doctor-heading"
								placeholder={__('Enter heading...', 'global360blocks')}
								value={heading}
								onChange={(value) => setAttributes({ heading: value })}
							/>
							<RichText
								tagName="p"
								className="find-doctor-body"
								placeholder={__('Enter body text...', 'global360blocks')}
								value={bodyText}
								onChange={(value) => setAttributes({ bodyText: value })}
							/>
							<div className="find-doctor-button">
								<Button
									className="btn btn_global"
									disabled
								>
									{__('Find a Doctor', 'global360blocks')}
								</Button>
							</div>
						</div>
					</div>
				</div>
			</>
		);
	},
	save: () => null, // Dynamic block
});
