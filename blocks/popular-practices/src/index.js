import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Button, __experimentalHeading as Heading } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './style.scss';
import './editor.scss';

registerBlockType('global360blocks/popular-practices', {
	edit: ({ attributes, setAttributes }) => {
		const { title, clinics } = attributes;

		const blockProps = useBlockProps({
			className: 'popular-practices-block',
		});

		// Get all clinic posts and regular pages/posts that might be clinic pages
		const clinicPages = useSelect((select) => {
			// First try to get clinic CPT posts
			const clinics = select('core').getEntityRecords('postType', 'clinic', { per_page: 100 }) || [];

			// Also get pages/posts that might be clinic pages as fallback
			const pages = select('core').getEntityRecords('postType', 'page', { per_page: 100 }) || [];
			const posts = select('core').getEntityRecords('postType', 'post', { per_page: 100 }) || [];

			// Combine all potential clinic pages
			const allPages = [...clinics, ...pages, ...posts];
			const clinicOptions = allPages
				.filter((page) => {
					const title = page.title.rendered.toLowerCase();
					const slug = page.slug.toLowerCase();
					return (
						title.includes('clinic') ||
						title.includes('practice') ||
						title.includes('medical') ||
						slug.includes('clinic') ||
						slug.includes('practice') ||
						page.type === 'clinic'
					); // Prioritize actual clinic CPT
				})
				.map((page) => ({
					label: page.title.rendered + (page.type === 'clinic' ? ' (Clinic)' : ''),
					value: page.id.toString(),
				}));

			return [{ label: 'Random Clinic', value: '' }, ...clinicOptions];
		}, []);

		const updateClinic = (index, field, value) => {
			const newClinics = [...clinics];
			newClinics[index] = {
				...newClinics[index],
				[field]: value,
			};
			setAttributes({ clinics: newClinics });
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Popular Practices Settings', 'global-360-theme')}>
						<TextControl
							label={__('Block Title', 'global-360-theme')}
							value={title}
							onChange={(value) => setAttributes({ title: value })}
						/>

						{clinics.map((clinic, index) => (
							<div
								key={index}
								style={{ marginBottom: '20px', padding: '15px', border: '1px solid #ddd' }}
							>
								<Heading level={4}>Clinic {index + 1}</Heading>

								<SelectControl
									label={__('Select Clinic Page', 'global-360-theme')}
									value={clinic.clinicId}
									options={clinicPages}
									onChange={(value) => updateClinic(index, 'clinicId', value)}
									help={__('Leave blank for random clinic', 'global-360-theme')}
								/>

								<TextControl
									label={__('Custom Clinic Name', 'global-360-theme')}
									value={clinic.customName}
									onChange={(value) => updateClinic(index, 'customName', value)}
									help={__('Override clinic name (optional)', 'global-360-theme')}
								/>

								<div style={{ marginBottom: '10px' }}>
									<label>Custom Logo</label>
									<MediaUploadCheck>
										<MediaUpload
											onSelect={(media) => updateClinic(index, 'customLogo', media.url)}
											allowedTypes={['image']}
											value={clinic.customLogo}
											render={({ open }) => (
												<div>
													{clinic.customLogo && (
														<img
															src={clinic.customLogo}
															alt="Clinic Logo"
															style={{
																maxWidth: '100px',
																height: 'auto',
																marginBottom: '10px',
															}}
														/>
													)}
													<Button
														onClick={open}
														variant="secondary"
													>
														{clinic.customLogo ? 'Change Logo' : 'Select Logo'}
													</Button>
													{clinic.customLogo && (
														<Button
															onClick={() => updateClinic(index, 'customLogo', '')}
															variant="link"
															isDestructive
														>
															Remove
														</Button>
													)}
												</div>
											)}
										/>
									</MediaUploadCheck>
								</div>

								<TextControl
									label={__('Custom URL', 'global-360-theme')}
									value={clinic.customUrl}
									onChange={(value) => updateClinic(index, 'customUrl', value)}
									help={__('Override clinic URL (optional)', 'global-360-theme')}
								/>
							</div>
						))}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="popular-practices-content">
						<RichText
							tagName="h2"
							className="popular-practices-title"
							value={title}
							onChange={(value) => setAttributes({ title: value })}
							placeholder={__('Enter title...', 'global-360-theme')}
						/>

						<div className="practices-grid">
							{clinics.map((clinic, index) => (
								<div
									key={index}
									className="practice-card"
								>
									<div className="practice-logo">
										{clinic.customLogo ? (
											<img
												src={clinic.customLogo}
												alt="Clinic Logo"
											/>
										) : (
											<div className="logo-placeholder">
												{clinic.clinicId ? 'Selected Clinic Logo' : 'Random Clinic Logo'}
											</div>
										)}
									</div>
									<h3 className="practice-name">
										{clinic.customName ||
											(clinic.clinicId ? 'Selected Clinic Name' : 'Random Clinic Name')}
									</h3>
								</div>
							))}
						</div>
					</div>
				</div>
			</>
		);
	},
	save: () => null, // Dynamic block - rendered on server
});
