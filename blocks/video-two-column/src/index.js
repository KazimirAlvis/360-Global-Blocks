import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { TextControl, Button } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
	const { videoUrl, heading, bodyText, videoTitle } = attributes;

	const onChangeVideoUrl = (url) => {
		setAttributes({
			videoUrl: url,
		});
	};

	const onRemoveVideo = () => {
		setAttributes({
			videoUrl: '',
		});
	};

	// Function to convert YouTube URL to embed URL
	const getYouTubeEmbedUrl = (url) => {
		if (!url) return '';

		// Handle different YouTube URL formats
		let videoId = '';

		if (url.includes('youtube.com/watch?v=')) {
			videoId = url.split('v=')[1]?.split('&')[0];
		} else if (url.includes('youtu.be/')) {
			videoId = url.split('youtu.be/')[1]?.split('?')[0];
		} else if (url.includes('youtube.com/embed/')) {
			return url; // Already an embed URL
		}

		return videoId ? `https://www.youtube.com/embed/${videoId}` : url;
	};

	// Check if URL is a YouTube URL
	const isYouTubeUrl = (url) => {
		return url.includes('youtube.com') || url.includes('youtu.be');
	};

	const renderVideo = () => {
		if (!videoUrl) {
			return (
				<div className="video-placeholder">
					<span>Video will appear here</span>
				</div>
			);
		}

		if (isYouTubeUrl(videoUrl)) {
			const embedUrl = getYouTubeEmbedUrl(videoUrl);
			return (
				<div className="video-wrapper">
					<iframe
						src={embedUrl}
						frameBorder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowFullScreen
						className="youtube-video"
					/>
				</div>
			);
		}

		// For direct video files
		return (
			<div className="video-wrapper">
				<video
					controls
					className="column-video"
					src={videoUrl}
				>
					Your browser does not support the video tag.
				</video>
			</div>
		);
	};

	const blockProps = useBlockProps({
		className: 'video-two-column-block',
	});

	const videoColumnStyles = {
		display: 'flex',
		flexDirection: 'column',
		alignItems: 'stretch',
		justifyContent: 'flex-start',
		gap: '16px',
	};

	const videoControlsStyles = {
		width: '100%',
		position: 'relative',
	};

	return (
		<div {...blockProps}>
			<div className="video-two-column-container">
				<div
					className="video-two-column-video"
					style={videoColumnStyles}
				>
					<RichText
						tagName="h2"
						className="video-two-column-video-title"
						value={videoTitle}
						onChange={(value) => setAttributes({ videoTitle: value })}
						placeholder={__('Add video title...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic', 'core/link']}
					/>
					{renderVideo()}
					<div
						className="video-controls"
						style={videoControlsStyles}
					>
						<TextControl
							label={__('Video URL', 'global360blocks')}
							value={videoUrl}
							onChange={onChangeVideoUrl}
							placeholder={__('Paste YouTube URL or direct video link...', 'global360blocks')}
							help={__('Supports YouTube links and direct video file URLs', 'global360blocks')}
						/>
						{videoUrl && (
							<Button
								className="button"
								onClick={onRemoveVideo}
							>
								{__('Remove Video', 'global360blocks')}
							</Button>
						)}
					</div>
				</div>

				<div className="video-two-column-content">
					<RichText
						tagName="h2"
						className="video-two-column-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Enter heading...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic']}
					/>

					<RichText
						tagName="p"
						className="video-two-column-body"
						value={bodyText}
						onChange={(value) => setAttributes({ bodyText: value })}
						placeholder={__('Enter body text...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic', 'core/link']}
					/>

					<div className="video-two-column-button-preview">
						<span className="btn btn_global">Take Risk Assessment Now</span>
					</div>
				</div>
			</div>
		</div>
	);
};

const Save = () => {
	return null; // Dynamic block, rendered via PHP
};

registerBlockType('global360blocks/video-two-column', {
	edit: Edit,
	save: Save,
});
