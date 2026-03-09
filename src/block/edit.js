import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	return (
		<div {...useBlockProps()}>
			<p>WP Agent AI Block (Editor)</p>
		</div>
	);
}