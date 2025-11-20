import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, ToggleControl, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { symptom, ai_content, show_disclaimer } = attributes;
	const [isLoading, setIsLoading] = useState(false);
	const blockProps = useBlockProps();

	const generateContent = async () => {
		if (!symptom.trim()) {
			alert('Please enter a symptom first.');
			return;
		}

		setIsLoading(true);

		try {
			const response = await fetch('/wp-json/global360blocks/v1/generate-symptoms-content', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpApiSettings.nonce,
				},
				body: JSON.stringify({ symptom: symptom.trim() }),
			});

			if (response.ok) {
				const data = await response.json();
				setAttributes({ ai_content: data.content });
			} else {
				alert('Error generating content. Please try again.');
			}
		} catch (error) {
			console.error('Error:', error);
			alert('Error generating content. Please try again.');
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Symptoms AI Settings', 'global360blocks')}>
					<TextControl
						label={__('Symptom', 'global360blocks')}
						value={symptom}
						onChange={(value) => setAttributes({ symptom: value })}
						placeholder={__('e.g., knee pain, headache, fever', 'global360blocks')}
					/>
					<Button
						isPrimary
						onClick={generateContent}
						disabled={isLoading || !symptom.trim()}
					>
						{isLoading ? (
							<>
								<Spinner /> {__('Generating...', 'global360blocks')}
							</>
						) : (
							__('Generate AI Content', 'global360blocks')
						)}
					</Button>
					<ToggleControl
						label={__('Show Medical Disclaimer', 'global360blocks')}
						checked={show_disclaimer}
						onChange={(value) => setAttributes({ show_disclaimer: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="symptoms-ai-editor">
					<h3>{__('Symptoms AI Block', 'global360blocks')}</h3>

					{!symptom && (
						<p className="placeholder-text">
							{__('Enter a symptom in the sidebar settings to generate AI content.', 'global360blocks')}
						</p>
					)}

					{symptom && !ai_content && (
						<div className="symptom-preview">
							<p>
								<strong>{__('Symptom:', 'global360blocks')}</strong> {symptom}
							</p>
							<p className="generate-prompt">
								{__(
									'Click "Generate AI Content" in the sidebar to create medical information.',
									'global360blocks'
								)}
							</p>
						</div>
					)}

					{symptom && ai_content && (
						<div className="symptoms-ai-preview">
							<div className="symptom-title">
								<h4>
									{__('Information About:', 'global360blocks')} {symptom}
								</h4>
							</div>
							<div
								className="ai-content-preview"
								dangerouslySetInnerHTML={{ __html: ai_content }}
							/>
							{show_disclaimer && (
								<div className="medical-disclaimer-preview">
									<p>
										<strong>⚠️ {__('Medical Disclaimer:', 'global360blocks')}</strong>{' '}
										{__(
											'This information is for educational purposes only and should not replace professional medical advice. Always consult with a qualified healthcare provider for proper diagnosis and treatment.',
											'global360blocks'
										)}
									</p>
								</div>
							)}
						</div>
					)}
				</div>
			</div>
		</>
	);
}
