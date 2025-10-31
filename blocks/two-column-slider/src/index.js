import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	PanelColorSettings,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, Button, Card, CardBody } from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import './editor.scss';
import './style.scss';

registerBlockType('global360blocks/two-column-slider', {
	edit: (props) => {
		const { attributes, setAttributes } = props;
		const { slides, autoplay, autoplaySpeed, showDots, showArrows } = attributes;
		const [currentSlide, setCurrentSlide] = useState(0);

		const blockProps = useBlockProps({
			className: 'two-column-slider-block',
		});

		const updateSlide = (slideIndex, field, value) => {
			const newSlides = [...slides];
			newSlides[slideIndex] = { ...newSlides[slideIndex], [field]: value };
			setAttributes({ slides: newSlides });
		};

		const addSlide = () => {
			const newSlides = [
				...slides,
				{
					heading: 'New Slide',
					text: 'Add your content here.',
					imageId: null,
					imageUrl: '',
					contentBackground: '',
				},
			];
			setAttributes({ slides: newSlides });
		};

		const removeSlide = (slideIndex) => {
			const newSlides = slides.filter((_, index) => index !== slideIndex);
			setAttributes({ slides: newSlides });
			if (currentSlide >= newSlides.length && newSlides.length > 0) {
				setCurrentSlide(newSlides.length - 1);
			}
		};

		const nextSlide = () => {
			setCurrentSlide((currentSlide + 1) % slides.length);
		};

		const prevSlide = () => {
			setCurrentSlide(currentSlide === 0 ? slides.length - 1 : currentSlide - 1);
		};

		const goToSlide = (index) => {
			setCurrentSlide(index);
		};

		const onSelectImage = (media, slideIndex) => {
			updateSlide(slideIndex, 'imageId', media.id);
			updateSlide(slideIndex, 'imageUrl', media.url);
		};

		const onRemoveImage = (slideIndex) => {
			updateSlide(slideIndex, 'imageId', null);
			updateSlide(slideIndex, 'imageUrl', '');
		};

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody
						title="Slider Settings"
						initialOpen={true}
					>
						<ToggleControl
							label="Autoplay"
							checked={autoplay}
							onChange={(value) => setAttributes({ autoplay: value })}
						/>
						{autoplay && (
							<RangeControl
								label="Autoplay Speed (milliseconds)"
								value={autoplaySpeed}
								onChange={(value) => setAttributes({ autoplaySpeed: value })}
								min={1000}
								max={10000}
								step={500}
							/>
						)}
						<ToggleControl
							label="Show Navigation Dots"
							checked={showDots}
							onChange={(value) => setAttributes({ showDots: value })}
						/>
						<ToggleControl
							label="Show Navigation Arrows"
							checked={showArrows}
							onChange={(value) => setAttributes({ showArrows: value })}
						/>
					</PanelBody>

					<PanelBody
						title="Slides"
						initialOpen={true}
					>
						{slides.map((slide, index) => (
							<Card
								key={index}
								className="slide-settings"
							>
								<CardBody>
									<h4>Slide {index + 1}</h4>

									<Button
										variant={currentSlide === index ? 'primary' : 'secondary'}
										onClick={() => goToSlide(index)}
										style={{ marginBottom: '10px' }}
									>
										Edit Slide {index + 1}
									</Button>

									<PanelColorSettings
										title="Content background"
										initialOpen={false}
										colorSettings={[
											{
												label: 'Background color',
												value: slide.contentBackground || '',
												onChange: (value) =>
													updateSlide(index, 'contentBackground', value || ''),
											},
										]}
									/>

									{slides.length > 1 && (
										<Button
											isDestructive
											variant="secondary"
											onClick={() => removeSlide(index)}
											className="remove-slide-btn"
										>
											Remove Slide
										</Button>
									)}
								</CardBody>
							</Card>
						))}

						{slides.length < 10 && (
							<Button
								variant="primary"
								onClick={addSlide}
								className="add-slide-btn"
							>
								Add Slide
							</Button>
						)}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="two-column-slider-container">
						<div className="slider-wrapper">
							{showArrows && (
								<button
									className="slider-nav prev"
									onClick={prevSlide}
									aria-label="Previous slide"
								>
									<span className="screen-reader-text">Previous slide</span>
								</button>
							)}

							<div
								className="slide-container"
								data-current-slide={currentSlide}
								data-autoplay={autoplay}
							>
								<div
									className="slide-track"
									style={{ transform: `translateX(-${currentSlide * 100}%)` }}
								>
									{slides.map((slide, index) => (
										<div
											key={index}
											className={`slide ${index === currentSlide ? 'active' : ''} ${
												slide.imageUrl ? 'has-image' : 'no-image'
											}`}
										>
											<div
												className="slide-content"
												style={{ backgroundColor: slide.contentBackground || undefined }}
											>
												<span className="slide-index">{index + 1}</span>
												<RichText
													tagName="h2"
													className="slide-heading"
													value={slide.heading}
													onChange={(value) => updateSlide(index, 'heading', value)}
													placeholder="Enter slide heading..."
												/>
												<RichText
													tagName="p"
													className="slide-text"
													value={slide.text}
													onChange={(value) => updateSlide(index, 'text', value)}
													placeholder="Enter slide text..."
												/>
											</div>

											<div className="slide-image">
												<MediaUploadCheck>
													<MediaUpload
														onSelect={(media) => onSelectImage(media, index)}
														allowedTypes={['image']}
														value={slide.imageId}
														render={({ open }) => (
															<div className="image-upload-container">
																{slide.imageUrl ? (
																	<div className="image-preview">
																		<img
																			src={slide.imageUrl}
																			alt={slide.heading || 'Slide image'}
																		/>
																		<div className="image-actions">
																			<Button
																				variant="secondary"
																				onClick={open}
																			>
																				Change Image
																			</Button>
																			<Button
																				variant="secondary"
																				isDestructive
																				onClick={() => onRemoveImage(index)}
																			>
																				Remove
																			</Button>
																		</div>
																	</div>
																) : (
																	<Button
																		variant="primary"
																		onClick={open}
																		className="upload-button"
																	>
																		Add Image
																	</Button>
																)}
															</div>
														)}
													/>
												</MediaUploadCheck>
											</div>
										</div>
									))}
								</div>
							</div>

							{showArrows && (
								<button
									className="slider-nav next"
									onClick={nextSlide}
									aria-label="Next slide"
								>
									<span className="screen-reader-text">Next slide</span>
								</button>
							)}
						</div>

						{showDots && (
							<div className="slider-dots">
								{slides.map((_, index) => (
									<button
										key={index}
										className={`dot ${index === currentSlide ? 'active' : ''}`}
										onClick={() => goToSlide(index)}
										aria-label={`Go to slide ${index + 1}`}
									/>
								))}
							</div>
						)}
					</div>
				</div>
			</Fragment>
		);
	},

	save: () => {
		// Rendered by PHP
		return null;
	},
});
