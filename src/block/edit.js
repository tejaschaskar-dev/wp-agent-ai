import { useBlockProps } from '@wordpress/block-editor';
import { Button, TextareaControl, SelectControl, Spinner, Notice } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { rawHandler } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

const CONTENT_TYPES = [
	{ label: __( 'Paragraph', 'wp-agent-ai' ),       value: 'paragraph' },
	{ label: __( 'Hero Section', 'wp-agent-ai' ),     value: 'hero-section' },
	{ label: __( 'Feature List', 'wp-agent-ai' ),     value: 'feature-list' },
	{ label: __( 'FAQ', 'wp-agent-ai' ),              value: 'faq' },
	{ label: __( 'Testimonials', 'wp-agent-ai' ),     value: 'testimonials' },
	{ label: __( 'Call to Action', 'wp-agent-ai' ),   value: 'call-to-action' },
];

const TONES = [
	{ label: __( 'Professional', 'wp-agent-ai' ), value: 'professional' },
	{ label: __( 'Casual', 'wp-agent-ai' ),       value: 'casual' },
	{ label: __( 'Creative', 'wp-agent-ai' ),     value: 'creative' },
	{ label: __( 'Technical', 'wp-agent-ai' ),    value: 'technical' },
];

const LENGTHS = [
	{ label: __( 'Short (1-2 paragraphs)', 'wp-agent-ai' ),  value: 'short' },
	{ label: __( 'Medium (3-4 paragraphs)', 'wp-agent-ai' ), value: 'medium' },
	{ label: __( 'Long (detailed section)', 'wp-agent-ai' ), value: 'long' },
];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		prompt,
		contentType,
		tone,
		length,
		generatedContent,
		isLoading,
		errorMessage,
	} = attributes;

	const { insertBlocks, removeBlock } = useDispatch( 'core/block-editor' );

	const handleGenerate = async () => {
		if ( ! prompt.trim() ) {
			setAttributes( { errorMessage: __( 'Please enter a description before generating.', 'wp-agent-ai' ) } );
			return;
		}

		setAttributes( { isLoading: true, errorMessage: '', generatedContent: '' } );

		try {
			const response = await apiFetch( {
				path: '/wp-agent-ai/v1/generate',
				method: 'POST',
				data: {
					prompt,
					content_type: contentType,
					tone,
					length,
				},
			} );

			if ( response.success && response.content ) {
				setAttributes( { generatedContent: response.content, isLoading: false } );

				const blocks = rawHandler( { HTML: '<p>' + response.content.replace( /\n\n/g, '</p><p>' ) + '</p>' } );
				insertBlocks( blocks, undefined, undefined, false );
			} else {
				setAttributes( {
					isLoading: false,
					errorMessage: response.message || __( 'Something went wrong. Please try again.', 'wp-agent-ai' ),
				} );
			}
		} catch ( error ) {
			let msg = __( 'Something went wrong. Please try again.', 'wp-agent-ai' );

			if ( error.message ) msg = error.message;
			if ( error.code === 'rest_forbidden' ) msg = __( 'You do not have permission to generate content.', 'wp-agent-ai' );

			setAttributes( { isLoading: false, errorMessage: msg } );
		}
	};

	return (
		<div { ...useBlockProps( { className: 'wp-agent-ai-block' } ) }>
			<div style={ { background: '#f0f0f0', padding: '20px', borderRadius: '8px', border: '1px solid #ddd' } }>

				<h3 style={ { margin: '0 0 15px', color: '#1d2327' } }>
					🤖 { __( 'WP Agent AI — Content Generator', 'wp-agent-ai' ) }
				</h3>

				{ errorMessage && (
					<Notice status="error" isDismissible={ false } style={ { marginBottom: '15px' } }>
						{ errorMessage }
					</Notice>
				) }

				<TextareaControl
					label={ __( 'Describe what you want to generate', 'wp-agent-ai' ) }
					value={ prompt }
					onChange={ ( val ) => setAttributes( { prompt: val } ) }
					placeholder={ __( 'e.g. Write a hero section for a SaaS product that helps restaurants manage online orders', 'wp-agent-ai' ) }
					rows={ 4 }
					disabled={ isLoading }
				/>

				<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '10px', marginBottom: '15px' } }>
					<SelectControl
						label={ __( 'Content Type', 'wp-agent-ai' ) }
						value={ contentType }
						options={ CONTENT_TYPES }
						onChange={ ( val ) => setAttributes( { contentType: val } ) }
						disabled={ isLoading }
					/>
					<SelectControl
						label={ __( 'Tone', 'wp-agent-ai' ) }
						value={ tone }
						options={ TONES }
						onChange={ ( val ) => setAttributes( { tone: val } ) }
						disabled={ isLoading }
					/>
					<SelectControl
						label={ __( 'Length', 'wp-agent-ai' ) }
						value={ length }
						options={ LENGTHS }
						onChange={ ( val ) => setAttributes( { length: val } ) }
						disabled={ isLoading }
					/>
				</div>

				<div style={ { display: 'flex', gap: '10px', alignItems: 'center' } }>
					<Button
						variant="primary"
						onClick={ handleGenerate }
						disabled={ isLoading || ! prompt.trim() }
					>
						{ isLoading ? __( 'Generating...', 'wp-agent-ai' ) : __( 'Generate Content', 'wp-agent-ai' ) }
					</Button>

					{ generatedContent && (
						<Button
							variant="secondary"
							onClick={ handleGenerate }
							disabled={ isLoading }
						>
							{ __( 'Regenerate', 'wp-agent-ai' ) }
						</Button>
					) }

					{ isLoading && <Spinner /> }
				</div>

				{ generatedContent && (
					<div style={ { marginTop: '15px', padding: '10px', background: '#fff', borderRadius: '4px', border: '1px solid #ccc' } }>
						<p style={ { margin: '0 0 5px', fontWeight: 'bold', color: '#1d2327' } }>
							✅ { __( 'Content generated and inserted above!', 'wp-agent-ai' ) }
						</p>
						<p style={ { margin: 0, fontSize: '12px', color: '#666' } }>
							{ __( 'You can edit the inserted blocks directly. Use Regenerate to try again.', 'wp-agent-ai' ) }
						</p>
					</div>
				) }
			</div>
		</div>
	);
}