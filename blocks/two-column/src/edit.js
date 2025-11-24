import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { useBlockProps, MediaUpload, MediaUploadCheck, RichText, InnerBlocks } from '@wordpress/block-editor';
import '@wordpress/format-library';
import { Button } from '@wordpress/components';
import { registerBlockType, rawHandler, createBlock } from '@wordpress/blocks';
import { useSelect, useDispatch } from '@wordpress/data';
import './style.css';
import './editor.css';

const BODY_TEMPLATE = [['core/paragraph', { placeholder: __('Add body content…', 'global360blocks') }]];

const BODY_ALLOWED_BLOCKS = ['core/paragraph', 'core/list', 'core/heading', 'core/quote'];

const Edit = ({ attributes, setAttributes, clientId }) => {
	const { imageUrl, imageId, heading, bodyText } = attributes;

	const innerBlocks = useSelect((select) => select('core/block-editor').getBlocks(clientId), [clientId]);
	const hasInnerBlocks = innerBlocks.length > 0;

	const { replaceInnerBlocks } = useDispatch('core/block-editor');

	useEffect(() => {
		if (!hasInnerBlocks && bodyText) {
			const parsedBlocks = rawHandler({ HTML: bodyText });
			let nextBlocks = parsedBlocks.length
				? parsedBlocks
				: [createBlock('core/paragraph', { content: bodyText })];

			if (
				parsedBlocks.length > 1 &&
				parsedBlocks.every(
					(block) => block?.name === 'core/paragraph' && typeof block?.attributes?.content === 'string'
				)
			) {
				const combinedContent = parsedBlocks
					.map((block) => block.attributes.content.trim())
					.filter(Boolean)
					.join('<br />');

				if (combinedContent) {
					nextBlocks = [createBlock('core/paragraph', { content: combinedContent })];
				}
			}
			replaceInnerBlocks(clientId, nextBlocks, false);
			setAttributes({ bodyText: '' });
		}
	}, [hasInnerBlocks, bodyText, replaceInnerBlocks, clientId, setAttributes]);

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
							<span>{__('Image will appear here', 'global360blocks')}</span>
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
					<div className="two-column-content-inner">
						<RichText
							identifier="heading"
							tagName="h2"
							className="two-column-heading"
							value={heading}
							onChange={(value) => setAttributes({ heading: value })}
							placeholder={__('Enter heading...', 'global360blocks')}
							allowedFormats={['core/bold', 'core/italic']}
						/>

						<div className="two-column-body-field">
							<span className="two-column-field-label">{__('Body content', 'global360blocks')}</span>
							<div className="two-column-body">
								<InnerBlocks
									allowedBlocks={BODY_ALLOWED_BLOCKS}
									template={BODY_TEMPLATE}
									templateLock={false}
								/>
							</div>
						</div>

						<div className="two-column-button-preview">
							<span className="btn btn_global">{__('Take Risk Assessment Now', 'global360blocks')}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

const Save = () => <InnerBlocks.Content />;

registerBlockType('global360blocks/two-column', {
	edit: Edit,
	save: Save,
});
