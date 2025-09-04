import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ColorPicker,
	ToggleControl,
	TextControl,
	__experimentalBoxControl as BoxControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import './editor.scss';

// Popular medical icons from Health Icons library
const MEDICAL_ICONS = {
	// Basic Medical
	stethoscope: {
		name: 'Stethoscope',
		category: 'Equipment',
		outline:
			'<path d="M8.5 1a2 2 0 1 0 0 4h.5v.5a6.5 6.5 0 0 0 13 0V5h.5a2 2 0 1 0 0-4h-14zM10 5V3h12v2a5 5 0 0 1-10 0z"/>',
		filled: '<path d="M8.5 1a2 2 0 1 0 0 4h.5v.5a6.5 6.5 0 0 0 13 0V5h.5a2 2 0 1 0 0-4h-14zM10 5V3h12v2a5 5 0 0 1-10 0z"/>',
	},
	heart: {
		name: 'Heart',
		category: 'Body',
		outline:
			'<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
		filled: '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
	},
	thermometer: {
		name: 'Thermometer',
		category: 'Equipment',
		outline:
			'<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0zM12 4a1 1 0 0 1 1 1v9.17a3 3 0 1 1-2 0V5a1 1 0 0 1 1-1z"/>',
		filled: '<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0zM12 19a2 2 0 1 1-2-2 2 2 0 0 1 2 2z"/>',
	},
	pill: {
		name: 'Pill',
		category: 'Medicine',
		outline:
			'<path d="M8.5 1a7.5 7.5 0 0 0 0 15h7a7.5 7.5 0 0 0 0-15h-7zm0 2h7a5.5 5.5 0 0 1 0 11h-7a5.5 5.5 0 0 1 0-11z"/>',
		filled: '<path d="M8.5 1a7.5 7.5 0 0 0 0 15h3.75V1H8.5zm7 0v15a7.5 7.5 0 0 0 0-15z"/>',
	},
	syringe: {
		name: 'Syringe',
		category: 'Equipment',
		outline: '<path d="M2.5 7.5L7 12l1.5-1.5 7.5 7.5 3-3-7.5-7.5L12 7l4.5-4.5-3-3L9 4l.5 1.5L7 8l1.5 1.5z"/>',
		filled: '<path d="M2.5 7.5L7 12l1.5-1.5 7.5 7.5 3-3-7.5-7.5L12 7l4.5-4.5-3-3L9 4l.5 1.5L7 8l1.5 1.5z"/>',
	},
	'first-aid': {
		name: 'First Aid Cross',
		category: 'Emergency',
		outline: '<path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm6 2h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/>',
		filled: '<path d="M3 3h18v18H3V3zm8 4h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/>',
	},
	ambulance: {
		name: 'Ambulance',
		category: 'Emergency',
		outline:
			'<path d="M2 8h4l2-2h8l2 2h4v8h-2v2h-2v-2H6v2H4v-2H2V8zm2 2v4h2v-2h2v2h8v-2h2v2h2v-4h-3l-1-1H7l-1 1H3z"/>',
		filled: '<path d="M2 8h4l2-2h8l2 2h4v8h-2v2h-2v-2H6v2H4v-2H2V8zm9 1h2v2h2v2h-2v2h-2v-2H9v-2h2V9z"/>',
	},
	tooth: {
		name: 'Tooth',
		category: 'Dental',
		outline:
			'<path d="M12 2a6 6 0 0 0-6 6c0 1.5.5 3 1 4.5L8 16c.5 2 1.5 4 2 6h4c.5-2 1.5-4 2-6l1-3.5c.5-1.5 1-3 1-4.5a6 6 0 0 0-6-6z"/>',
		filled: '<path d="M12 2a6 6 0 0 0-6 6c0 1.5.5 3 1 4.5L8 16c.5 2 1.5 4 2 6h4c.5-2 1.5-4 2-6l1-3.5c.5-1.5 1-3 1-4.5a6 6 0 0 0-6-6z"/>',
	},
	bandage: {
		name: 'Bandage',
		category: 'Treatment',
		outline:
			'<path d="M3 8h2l2-2 8 8-2 2h-2l-8-8zm1 1l7 7h1l1-1L6 8H4zm8-6l3 3-1 1-3-3 1-1zm4 4l3 3-1 1-3-3 1-1z"/>',
		filled: '<path d="M3 8h2l2-2 8 8-2 2h-2l-8-8zm8-6l3 3-1 1-3-3 1-1zm4 4l3 3-1 1-3-3 1-1z"/>',
	},
	'x-ray': {
		name: 'X-Ray',
		category: 'Imaging',
		outline: '<path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm2 2h8v8H8V8zm2 2v4h4v-4h-4z"/>',
		filled: '<path d="M4 4h16v16H4V4zm4 4h8v8H8V8zm2 2v4h4v-4h-4z"/>',
	},
};

const ICON_CATEGORIES = [
	{ value: 'all', label: 'All Icons' },
	{ value: 'Equipment', label: 'Medical Equipment' },
	{ value: 'Body', label: 'Body Parts' },
	{ value: 'Medicine', label: 'Medicine & Drugs' },
	{ value: 'Emergency', label: 'Emergency' },
	{ value: 'Dental', label: 'Dental' },
	{ value: 'Treatment', label: 'Treatment' },
	{ value: 'Imaging', label: 'Medical Imaging' },
];

registerBlockType('global360blocks/medical-icons', {
	edit: (props) => {
		const { attributes, setAttributes } = props;
		const { selectedIcon, iconStyle, iconSize, iconColor, showLabel, iconLabel, alignment } = attributes;

		const [selectedCategory, setSelectedCategory] = useState('all');

		const blockProps = useBlockProps({
			className: `medical-icons-block alignment-${alignment}`,
		});

		const filteredIcons =
			selectedCategory === 'all'
				? MEDICAL_ICONS
				: Object.fromEntries(
						Object.entries(MEDICAL_ICONS).filter(([key, icon]) => icon.category === selectedCategory)
				  );

		const renderIcon = (iconKey, size = iconSize) => {
			const icon = MEDICAL_ICONS[iconKey];
			if (!icon) return null;

			const pathData = iconStyle === 'filled' ? icon.filled : icon.outline;

			return (
				<svg
					width={size}
					height={size}
					viewBox="0 0 24 24"
					fill={iconStyle === 'filled' ? iconColor : 'none'}
					stroke={iconStyle === 'outline' ? iconColor : 'none'}
					strokeWidth="2"
					strokeLinecap="round"
					strokeLinejoin="round"
				>
					<g dangerouslySetInnerHTML={{ __html: pathData }} />
				</svg>
			);
		};

		return (
			<>
				<InspectorControls>
					<PanelBody
						title="Icon Settings"
						initialOpen={true}
					>
						<SelectControl
							label="Category"
							value={selectedCategory}
							options={ICON_CATEGORIES}
							onChange={(value) => setSelectedCategory(value)}
							help="Filter icons by category"
						/>

						<div className="medical-icons-grid">
							{Object.entries(filteredIcons).map(([key, icon]) => (
								<button
									key={key}
									className={`icon-option ${selectedIcon === key ? 'selected' : ''}`}
									onClick={() =>
										setAttributes({
											selectedIcon: key,
											iconLabel: icon.name,
										})
									}
									title={icon.name}
								>
									{renderIcon(key, 32)}
								</button>
							))}
						</div>

						<SelectControl
							label="Icon Style"
							value={iconStyle}
							options={[
								{ label: 'Outline', value: 'outline' },
								{ label: 'Filled', value: 'filled' },
							]}
							onChange={(value) => setAttributes({ iconStyle: value })}
						/>

						<RangeControl
							label="Icon Size"
							value={iconSize}
							onChange={(value) => setAttributes({ iconSize: value })}
							min={16}
							max={200}
							step={4}
						/>

						<div className="color-picker-wrapper">
							<label>Icon Color</label>
							<ColorPicker
								color={iconColor}
								onChange={(value) => setAttributes({ iconColor: value })}
								enableAlpha
							/>
						</div>

						<SelectControl
							label="Alignment"
							value={alignment}
							options={[
								{ label: 'Left', value: 'left' },
								{ label: 'Center', value: 'center' },
								{ label: 'Right', value: 'right' },
							]}
							onChange={(value) => setAttributes({ alignment: value })}
						/>
					</PanelBody>

					<PanelBody
						title="Label Settings"
						initialOpen={false}
					>
						<ToggleControl
							label="Show Label"
							checked={showLabel}
							onChange={(value) => setAttributes({ showLabel: value })}
						/>

						{showLabel && (
							<TextControl
								label="Label Text"
								value={iconLabel}
								onChange={(value) => setAttributes({ iconLabel: value })}
							/>
						)}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="medical-icon-display">
						{renderIcon(selectedIcon)}
						{showLabel && <span className="icon-label">{iconLabel}</span>}
					</div>
				</div>
			</>
		);
	},

	save: () => {
		// Rendered by PHP
		return null;
	},
});
