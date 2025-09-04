import { __ } from '@wordpress/i18n';
import { useBlockProps, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
	const { imageUrl, imageId, heading } = attributes;

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

	const blockProps = useBlockProps({
		className: 'cta-block',
	});

	return (
		<div {...blockProps}>
			<div
				className="cta-container"
				style={{ backgroundImage: imageUrl ? `url(${imageUrl})` : 'none' }}
			>
				<div className="cta-content">
					<RichText
						tagName="h2"
						className="cta-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Enter heading...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic']}
					/>

					<div className="cta-button-preview">
						<span className="btn btn_global">Take Assessment</span>
					</div>
				</div>

				<div className="cta-controls">
					<MediaUploadCheck>
						<MediaUpload
							onSelect={onSelectImage}
							allowedTypes={['image']}
							value={imageId}
							render={({ open }) => (
								<div className="image-controls">
									{!imageUrl && (
										<Button
											className="button button-large"
											onClick={open}
										>
											{__('Upload Image', 'global360blocks')}
										</Button>
									)}
									{imageUrl && (
										<>
											<Button
												className="button"
												onClick={open}
											>
												{__('Replace Image', 'global360blocks')}
											</Button>
											<Button
												className="button"
												onClick={onRemoveImage}
											>
												{__('Remove Image', 'global360blocks')}
											</Button>
										</>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
				</div>
			</div>
		</div>
	);
};

const Save = () => {
	return null; // Dynamic block, rendered via PHP
};

registerBlockType('global360blocks/cta', {
	edit: Edit,
	save: Save,
});
