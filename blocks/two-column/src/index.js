import { __ } from '@wordpress/i18n';
import { useBlockProps, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
	const { imageUrl, imageId, heading, bodyText } = attributes;

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
		className: 'two-column-block',
	});

	return (
		<div {...blockProps}>
			<div className="two-column-container">
				<div className="two-column-image">
					{imageUrl ? (
						<img
							src={imageUrl}
							alt=""
							className="column-image"
						/>
					) : (
						<div className="image-placeholder">
							<span>Image will appear here</span>
						</div>
					)}
					<div className="image-controls">
						<MediaUploadCheck>
							<MediaUpload
								onSelect={onSelectImage}
								allowedTypes={['image']}
								value={imageId}
								render={({ open }) => (
									<div className="upload-controls">
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

				<div className="two-column-content">
					<RichText
						tagName="h2"
						className="two-column-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Enter heading...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic']}
					/>

					<RichText
						tagName="p"
						className="two-column-body"
						value={bodyText}
						onChange={(value) => setAttributes({ bodyText: value })}
						placeholder={__('Enter body text...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic', 'core/link']}
					/>

					<div className="two-column-button-preview">
						<span className="btn btn_global">Take Assessment</span>
					</div>
				</div>
			</div>
		</div>
	);
};

const Save = () => {
	return null; // Dynamic block, rendered via PHP
};

registerBlockType('global360blocks/two-column', {
	edit: Edit,
	save: Save,
});
