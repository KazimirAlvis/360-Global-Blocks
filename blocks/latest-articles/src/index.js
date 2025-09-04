import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { registerBlockType } from '@wordpress/blocks';
import './style.css';
import './editor.css';

const Edit = ({ attributes, setAttributes }) => {
	const { numberOfPosts, showExcerpt, excerptLength, columns } = attributes;

	// Fetch posts using useSelect
	const posts = useSelect(
		(select) => {
			return select('core').getEntityRecords('postType', 'post', {
				per_page: numberOfPosts,
				_embed: true,
				status: 'publish',
				orderby: 'date',
				order: 'desc',
			});
		},
		[numberOfPosts]
	);

	const blockProps = useBlockProps({
		className: 'latest-articles-block',
	});

	// Function to get featured image
	const getFeaturedImage = (post) => {
		if (post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0]) {
			return post._embedded['wp:featuredmedia'][0].source_url;
		}
		return null;
	};

	// Function to get excerpt
	const getExcerpt = (post) => {
		if (post.excerpt && post.excerpt.rendered) {
			const div = document.createElement('div');
			div.innerHTML = post.excerpt.rendered;
			const text = div.textContent || div.innerText || '';
			return (
				text.split(' ').slice(0, excerptLength).join(' ') +
				(text.split(' ').length > excerptLength ? '...' : '')
			);
		}
		return '';
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Articles Settings', 'global360blocks')}>
					<RangeControl
						label={__('Number of Articles', 'global360blocks')}
						value={numberOfPosts}
						onChange={(value) => setAttributes({ numberOfPosts: value })}
						min={1}
						max={12}
					/>
					<RangeControl
						label={__('Columns', 'global360blocks')}
						value={columns}
						onChange={(value) => setAttributes({ columns: value })}
						min={1}
						max={4}
					/>
					<ToggleControl
						label={__('Show Excerpt', 'global360blocks')}
						checked={showExcerpt}
						onChange={(value) => setAttributes({ showExcerpt: value })}
					/>
					{showExcerpt && (
						<RangeControl
							label={__('Excerpt Length (words)', 'global360blocks')}
							value={excerptLength}
							onChange={(value) => setAttributes({ excerptLength: value })}
							min={5}
							max={50}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="latest-articles-header">
					<h2>Our Latest Articles</h2>
				</div>

				<div
					className="latest-articles-grid"
					style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
				>
					{posts && posts.length > 0 ? (
						posts.map((post) => (
							<article
								key={post.id}
								className="latest-article-item"
							>
								{getFeaturedImage(post) && (
									<div className="article-image">
										<img
											src={getFeaturedImage(post)}
											alt={post.title.rendered}
										/>
									</div>
								)}
								<div className="article-content">
									<h3
										className="article-title"
										dangerouslySetInnerHTML={{ __html: post.title.rendered }}
									/>
									{showExcerpt && <p className="article-excerpt">{getExcerpt(post)}</p>}
									<div className="article-read-more">
										<span className="read-more-link">READ MORE →</span>
									</div>
								</div>
							</article>
						))
					) : (
						<div className="latest-articles-loading">
							<p>
								{posts === null
									? __('Loading articles...', 'global360blocks')
									: __('No articles found.', 'global360blocks')}
							</p>
						</div>
					)}
				</div>
			</div>
		</>
	);
};

const Save = () => {
	return null; // Dynamic block, rendered via PHP
};

registerBlockType('global360blocks/latest-articles', {
	edit: Edit,
	save: Save,
});
